<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Str;
use App\Services\TestTimeService;
use Illuminate\Support\Facades\Cache;
use App\Services\BrevoService;
use App\Models\SecurityLogs;
use Illuminate\Support\Facades\Session;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    // Cooldown configuration
    private const MAX_FAILED_ATTEMPTS = 3;
    private const COOLDOWN_MINUTES = 1;

    protected function rules(): array
    {
        return [
            'email' => 'required|string|email|max:256',
            'password' => 'required|string|min:8|max:128',
            'remember' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Check user's institution status without changing their role type
     */
    protected function checkInstitutionStatus($user)
    {
        $emailDomain = Str::after($user->email, '@');
        $institution = Institution::where('domain', $emailDomain)->first();
        
        // 1. For researchers (can be upgraded or downgraded)
        if ($user->type === 'respondent' || $user->type === 'researcher') {
            // Check if email is from an educational institution (.edu domain)
            if (Str::endsWith($emailDomain, '.edu') || Str::endsWith($emailDomain, '.edu.ph')) {
                // Upgrade respondent to researcher if institution exists
                if ($user->type === 'respondent' && $institution) {
                    $user->update([
                        'type' => 'researcher', 
                        'institution_id' => $institution->id,
                    ]);
                    return 'upgraded';
                }
                
                // Downgrade researcher to respondent if institution doesn't exist
                if ($user->type === 'researcher' && !$institution) {
                    $user->update([
                        'type' => 'respondent', 
                        'institution_id' => null,
                    ]);
                    return 'downgraded';
                }
            } else if ($user->type === 'researcher') {
                // Non-edu email shouldn't be researcher
                $user->update([
                    'type' => 'respondent', 
                    'institution_id' => null,
                ]);
                return 'downgraded';
            }
        }
        
        // 2. For institution admins - check if their institution has been restored
        if ($user->type === 'institution_admin') {
            // First check if the domain matches an institution in our system
            // This is now the primary check - focusing on domain rather than checking existing ID first
            if ($institution) {
                // If we found a matching institution for this domain
                if ($user->institution_id != $institution->id) {
                    $user->update([
                        'institution_id' => $institution->id
                    ]);
                    return 'institution-restored';
                }
            } else {
                // No institution exists for this email domain
                if ($user->institution_id !== null) {
                    $user->update([
                        'institution_id' => null
                    ]);
                    return 'institution-lost';
                }
            }
        }
        
        return false; // No change
    }

    // Change keys to be IP-based instead of email-based
    private function attemptsKey(): string
    {
        $ip = request()->ip();
        return 'login:attempts:' . sha1($ip);
    }

    private function lockoutKey(): string
    {
        $ip = request()->ip();
        return 'login:lockout:' . sha1($ip);
    }

    private function getLockoutRemainingSeconds(): int
    {
        $untilTs = (int) Cache::get($this->lockoutKey(), 0);
        $nowTs = TestTimeService::now()->timestamp;
        return max(0, $untilTs - $nowTs);
    }

    private function isLockedOut(): bool
    {
        return $this->getLockoutRemainingSeconds() > 0;
    }

    private function startLockout(): void
    {
        $untilTs = TestTimeService::now()->addMinutes(self::COOLDOWN_MINUTES)->timestamp;
        Cache::put($this->lockoutKey(), $untilTs, TestTimeService::now()->addMinutes(self::COOLDOWN_MINUTES));
        Cache::forget($this->attemptsKey());
        
        // Log the lockout event to security logs
        $this->logLockoutEvent();
        
        // Send lockout warning email to the user (if user exists)
        $this->sendLockoutWarningEmail();
    }

    /**
     * Log lockout event to security logs
     */
    private function logLockoutEvent(): void
    {
        try {
            $currentIp = request()->ip();
            $geoData = $this->getGeoLocation($currentIp);
            
            // Find user by email to get user_id and role
            $user = User::where('email', $this->email)->first();
            
            // Only create security log if the email belongs to an actual user account
            if (!$user) {
                \Illuminate\Support\Facades\Log::info('Lockout triggered for non-existent email', [
                    'email' => $this->email,
                    'ip' => $currentIp,
                    'note' => 'No security log created - email not in system'
                ]);
                return;
            }
            
            SecurityLogs::create([
                'uuid' => Str::uuid(),
                'created_at' => TestTimeService::now(),
                'email' => $this->email,
                'event_type' => 'rate_limit_triggered',
                'outcome' => 'failure',
                'user_id' => $user->id,
                'actor_role' => $user->type,
                'ip' => $currentIp,
                'user_agent' => request()->userAgent(),
                'route' => request()->path(),
                'http_method' => request()->method(),
                'http_status' => 429,
                'resource_type' => 'User',
                'resource_id' => $user->id,
                'message' => "Account locked out after " . self::MAX_FAILED_ATTEMPTS . " failed login attempts for email: {$this->email}",
                'meta' => [
                    'max_attempts' => self::MAX_FAILED_ATTEMPTS,
                    'cooldown_minutes' => self::COOLDOWN_MINUTES,
                    'email' => $this->email,
                    'lockout_until' => TestTimeService::now()->addMinutes(self::COOLDOWN_MINUTES)->toDateTimeString(),
                ],
                'geo' => $geoData,
            ]);
            
            \Illuminate\Support\Facades\Log::info('Security log created for lockout', [
                'email' => $this->email,
                'ip' => $currentIp,
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create security log for lockout', [
                'email' => $this->email,
                'ip' => request()->ip(),
                'error' => $e->getMessage()
            ]);
        }
    }

    private function incrementFailedAttempt(): bool
    {
        $key = $this->attemptsKey();
        $count = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $count, TestTimeService::now()->addMinutes(self::COOLDOWN_MINUTES));
        if ($count >= self::MAX_FAILED_ATTEMPTS) {
            $this->startLockout();
            return true;
        }
        return false;
    }

    private function clearLoginAttempts(): void
    {
        Cache::forget($this->attemptsKey());
        Cache::forget($this->lockoutKey());
    }

    /**
     * Hash an IP address for secure storage
     */
    private function hashIpAddress(string $ipAddress): string
    {
        return hash('sha256', $ipAddress);
    }

    /**
     * Get geolocation data from IP address
     */
    private function getGeoLocation(string $ipAddress): array
    {
        try {
            // Using ip-api.com free API (no key required, 45 req/min limit)
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get("http://ip-api.com/json/{$ipAddress}");
            
            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                return [
                    'country' => $data['country'] ?? '',
                    'region' => $data['regionName'] ?? '',
                    'city' => $data['city'] ?? '',
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GeoLocation API Error', [
                'message' => $e->getMessage(),
                'ip' => $ipAddress
            ]);
        }
        
        return [];
    }

    /**
     * Check and handle IP address changes
     */
    private function checkIpAddress(User $user): void
    {
        $currentIp = request()->ip();
        $hashedCurrentIp = $this->hashIpAddress($currentIp);
        $previousHashedIp = $user->ip_address;
        
        // If IP has changed and user had a previous IP recorded
        if ($previousHashedIp && $previousHashedIp !== $hashedCurrentIp) {
            // Get geolocation data (use unhashed IP for API call)
            $geoData = $this->getGeoLocation($currentIp);
            
            // Log the IP change to security logs
            $this->logIpChange($user, $currentIp, $geoData);
            
            // Send warning email (with unhashed IP for user visibility)
            try {
                $brevoService = app(BrevoService::class);
                $brevoService->sendNewIpWarning($user->email, $currentIp, $geoData);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send new IP warning email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_hash' => $hashedCurrentIp,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Update user's IP address with hashed value
        $user->ip_address = $hashedCurrentIp;
        $user->save();
    }

    /**
     * Log IP address change to security logs
     */
    private function logIpChange(User $user, string $newIp, array $geoData): void
    {
        try {
            SecurityLogs::create([
                'uuid' => Str::uuid(),
                'created_at' => TestTimeService::now(),
                'email' => $user->email, // Add email field
                'event_type' => 'login_success',
                'outcome' => 'success',
                'user_id' => $user->id,
                'actor_role' => $user->type,
                'ip' => $newIp,
                'user_agent' => request()->userAgent(),
                'route' => request()->path(),
                'http_method' => request()->method(),
                'http_status' => 200,
                'resource_type' => 'User',
                'resource_id' => $user->id,
                'message' => "New IP address detected for user: {$user->email}",
                'meta' => [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'previous_ip_hash' => $user->ip_address,
                    'new_ip' => $newIp,
                    'ip_change_detected' => true,
                    'warning_email_sent' => true,
                ],
                'geo' => $geoData,
            ]);
            
            \Illuminate\Support\Facades\Log::info('Security log created for IP change', [
                'user_id' => $user->id,
                'email' => $user->email,
                'old_ip_hash' => $user->ip_address,
                'new_ip' => $newIp,
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create security log for IP change', [
                'user_id' => $user->id,
                'email' => $user->email,
                'new_ip' => $newIp,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send lockout warning email to the user whose account is being targeted
     */
    private function sendLockoutWarningEmail(): void
    {
        try {
            // Find user by email (the account being targeted)
            $user = User::where('email', $this->email)->first();
            
            if (!$user) {
                // User doesn't exist, no need to send email
                \Illuminate\Support\Facades\Log::info('Lockout email not sent - user does not exist', [
                    'email' => $this->email,
                    'ip' => request()->ip()
                ]);
                return;
            }
            
            // Get current IP and geo data
            $currentIp = request()->ip();
            $geoData = $this->getGeoLocation($currentIp);
            
            // Send lockout warning email
            $brevoService = app(BrevoService::class);
            $brevoService->sendLockoutWarning(
                $user->email,
                $currentIp,
                $geoData,
                self::COOLDOWN_MINUTES
            );
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send lockout warning email', [
                'email' => $this->email,
                'ip' => request()->ip(),
                'error' => $e->getMessage()
            ]);
            // Don't throw exception - lockout should still work even if email fails
        }
    }

    /**
     * Log successful login for admin users to security logs
     */
    private function logAdminLogin(User $user): void
    {
        // Only log for institution admins and super admins
        if (!in_array($user->type, ['institution_admin', 'super_admin'])) {
            return;
        }

        try {
            $currentIp = request()->ip();
            $geoData = $this->getGeoLocation($currentIp);
            
            SecurityLogs::create([
                'uuid' => Str::uuid(),
                'created_at' => TestTimeService::now(),
                'email' => $user->email, // Add email field
                'event_type' => 'login_success',
                'outcome' => 'success',
                'user_id' => $user->id,
                'actor_role' => $user->type,
                'ip' => $currentIp,
                'user_agent' => request()->userAgent(),
                'route' => request()->path(),
                'http_method' => request()->method(),
                'http_status' => 200,
                'resource_type' => 'User',
                'resource_id' => $user->id,
                'message' => ucfirst(str_replace('_', ' ', $user->type)) . " logged in: {$user->email}",
                'meta' => [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'remember_me' => $this->remember,
                ],
                'geo' => $geoData,
            ]);
            
            \Illuminate\Support\Facades\Log::info('Security log created for admin login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $user->type,
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create security log for admin login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function completeLogin($user, bool $remember): bool
    {
        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            // Store user ID and remember preference in session
            Session::put('2fa:user:id', $user->id);
            Session::put('2fa:remember', $remember);
            
            // Don't actually log them in yet
            return true; // Indicates 2FA redirect needed
        }

        // No 2FA, complete login normally
        Auth::login($user, $remember);
        
        // Mark 2FA as verified for this session (even though it's not required)
        Session::put('2fa:verified:' . $user->id, true);
        
        return false; // Indicates normal login
    }

    public function attemptLogin()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            
            // Check for password length error specifically
            if ($errors->has('password') && str_contains($errors->first('password'), 'at least 8')) {
                $this->dispatch('password-length-error', [
                    'message' => 'Password must be at least 8 characters.'
                ]);
                return;
            }
            
            // Handle other validation errors
            $allErrors = $errors->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $allErrors)]);
            return;
        }

        // First check if the user is archived (soft-deleted)
        $archivedUser = User::withTrashed()
            ->where('email', $this->email)
            ->whereNotNull('deleted_at')
            ->first();
        if ($archivedUser) {
            $this->dispatch('archived-account', [
                'message' => 'This account has been archived. Please contact the Formigo support team for assistance.'
            ]);
            return;
        }

        // Block if currently locked out (now IP-based)
        if ($this->isLockedOut()) {
            $minutes = max(1, (int) ceil($this->getLockoutRemainingSeconds() / 60));
            $this->dispatch('login-cooldown', [
                'message' => "Too many failed login attempts. Please try again in {$minutes} minute(s)."
            ]);
            return;
        }

        // Set session lifetime to 1 day if remember is not checked
        if (!$this->remember) {
            config(['session.lifetime' => 1440]); // 1 day in minutes
        }

        // Proceed with normal authentication
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], false)) {
            // Restore session lifetime to default if login fails
            if (!$this->remember) {
                config(['session.lifetime' => config('session.lifetime', 120)]);
            }

            // Increment failed attempts; start cooldown at 3 (now IP-based)
            if ($this->incrementFailedAttempt()) {
                $minutes = max(1, (int) ceil($this->getLockoutRemainingSeconds() / 60));
                $this->dispatch('login-cooldown', [
                    'message' => "Too many failed login attempts. Please try again in {$minutes} minute(s)."
                ]);
                return;
            }

            $this->dispatch('login-error', [
                'message' => 'Invalid email or password. Please check your credentials and try again.'
            ]);
            return;
        }

        // Successful credentials: clear attempts/cooldown
        $this->clearLoginAttempts();

        // Get the authenticated user
        $user = Auth::user();
        
        // Log out immediately (we'll log back in after 2FA or if no 2FA)
        Auth::logout();
        
        // Log admin login to security logs (only for admins)
        $this->logAdminLogin($user);
        
        // Check and handle IP address changes (send warning if needed)
        $this->checkIpAddress($user);
        
        // Update last_active_at timestamp
        $user->forceFill(['last_active_at' => TestTimeService::now()])->save();
        
        // If the user was inactive, reactivate their account
        if (!$user->is_active) {
            $user->is_active = true;
            $user->save();
            Session::put('account_reactivated', true);
        }

        // Check user's institution status
        $statusChange = $this->checkInstitutionStatus($user);
        if ($statusChange) {
            Session::put('institution_status_change', $statusChange);
        }

        // Check if 2FA is required
        $needs2FA = $this->completeLogin($user, $this->remember);
        
        if ($needs2FA) {
            // Redirect to 2FA challenge
            return redirect()->route('two-factor.challenge');
        }

        // No 2FA required, regenerate session and redirect
        session()->regenerate();

        // Show any pending messages
        if (Session::has('account_reactivated')) {
            $this->dispatch('account-status-change', [
                'type' => 'success',
                'title' => 'Account Reactivated',
                'message' => 'Your account has been reactivated!'
            ]);
            Session::forget('account_reactivated');
        }

        if (Session::has('institution_status_change')) {
            $change = Session::get('institution_status_change');
            // ...existing institution status change dispatch code...
            Session::forget('institution_status_change');
        }
        
        return redirect()->route('feed.index');
    }

    public function render()
    {
        // Get warning message from session if user was redirected from survey answer page
        $warningMessage = session('warning_message');
        
        return view('livewire.auth.login', compact('warningMessage'));
    }
}
