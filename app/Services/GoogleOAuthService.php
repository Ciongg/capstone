<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\TestTimeService;
use App\Models\SecurityLogs;
use Illuminate\Support\Facades\Session;

class GoogleOAuthService
{
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
            Log::error('GeoLocation API Error', [
                'message' => $e->getMessage(),
                'ip' => $ipAddress
            ]);
        }
        
        return [];
    }

    /**
     * Log IP address change to security logs
     */
    private function logIpChange(User $user, string $newIp, array $geoData, string $previousHashedIp): void
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
                'message' => "New IP address detected for user: {$user->email} (Google OAuth)",
                'meta' => [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'previous_ip_hash' => $previousHashedIp,
                    'new_ip' => $newIp,
                    'ip_change_detected' => true,
                    'warning_email_sent' => true,
                    'auth_method' => 'google_oauth',
                ],
                'geo' => $geoData,
            ]);
            
            Log::info('Security log created for IP change (Google OAuth)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'old_ip_hash' => $previousHashedIp,
                'new_ip' => $newIp,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create security log for IP change (Google OAuth)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'new_ip' => $newIp,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check and handle IP address changes for existing users
     */
    private function checkAndNotifyIpChange(User $user, string $currentIp, string $hashedCurrentIp): void
    {
        $previousHashedIp = $user->ip_address;
        
        // Only send email if IP has changed AND user had a previous IP recorded
        if ($previousHashedIp && $previousHashedIp !== $hashedCurrentIp) {
            try {
                $geoData = $this->getGeoLocation($currentIp);
                
                // Log the IP change to security logs
                $this->logIpChange($user, $currentIp, $geoData, $previousHashedIp);
                
                // Send warning email
                $brevoService = app(BrevoService::class);
                $brevoService->sendNewIpWarning($user->email, $currentIp, $geoData);
                
                Log::info('New IP warning email sent (Google OAuth)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'previous_ip_hash' => $previousHashedIp,
                    'new_ip_hash' => $hashedCurrentIp
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send new IP warning email (Google OAuth)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Log successful admin login to security logs
     */
    private function logAdminLogin(User $user, string $currentIp, array $geoData): void
    {
        // Only log for institution admins and super admins
        if (!in_array($user->type, ['institution_admin', 'super_admin'])) {
            return;
        }

        try {
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
                'message' => ucfirst(str_replace('_', ' ', $user->type)) . " logged in via Google: {$user->email}",
                'meta' => [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'auth_method' => 'google_oauth',
                ],
                'geo' => $geoData,
            ]);
            
            Log::info('Security log created for admin login (Google OAuth)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $user->type,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create security log for admin login (Google OAuth)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getGoogleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback($request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            Log::info('Google user data', ['googleUser' => $googleUser]);
            
            // Get current IP address and hash it
            $currentIp = $request->ip();
            $hashedIp = $this->hashIpAddress($currentIp);
            
            // Check if user already exists by email
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Get geolocation for existing user
                $geoData = $this->getGeoLocation($currentIp);
                
                // Check for IP change BEFORE updating
                $this->checkAndNotifyIpChange($user, $currentIp, $hashedIp);
                
                // Log admin login (only for admins)
                $this->logAdminLogin($user, $currentIp, $geoData);
                
                // Update IP address and last_active_at for existing user
                $user->ip_address = $hashedIp;
                $user->last_active_at = TestTimeService::now();
                $user->save();
                
                Log::info('Google login for existing user', [
                    'email' => $googleUser->getEmail(), 
                    'user_id' => $user->id,
                    'ip_hash' => $hashedIp
                ]);

                // Check if 2FA is enabled
                if ($user->hasTwoFactorEnabled()) {
                    // Store user ID in session for 2FA verification
                    Session::put('2fa:user:id', $user->id);
                    Session::put('2fa:remember', false); // OAuth doesn't use remember
                    
                    // Return user but don't log in yet (controller will redirect to 2FA)
                    return ['user' => $user, 'needs_2fa' => true];
                }

                // No 2FA, mark as verified
                Session::put('2fa:verified:' . $user->id, true);
                
                return ['user' => $user, 'needs_2fa' => false];
            }
            
            // Create new user with hashed IP (no 2FA for new users initially)
            DB::beginTransaction();
            $user = User::create([
                'first_name' => $googleUser->user['given_name'] ?? $googleUser->getName(),
                'last_name' => $googleUser->user['family_name'] ?? '',
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(str()->random(32)),
                'email_verified_at' => now(),
                'is_active' => true,
                'type' => 'respondent',
                'is_accepted_terms' => true,
                'is_accepted_privacy_policy' => true,
                'last_active_at' => TestTimeService::now(),
                'ip_address' => $hashedIp,
            ]);
            
            DB::commit();
            Log::info('Google user created', [
                'user_id' => $user->id,
                'ip_hash' => $hashedIp
            ]);

            // Mark 2FA as verified (new user has no 2FA)
            Session::put('2fa:verified:' . $user->id, true);
            
            return ['user' => $user, 'needs_2fa' => false];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google signup exception', [
                'message' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getGoogleUser($request)
    {
        try {
            return Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function createUserFromGoogle($googleUserData)
    {
        try {
            DB::beginTransaction();
            
            // Determine user type based on email domain
            $emailDomain = Str::after($googleUserData['email'], '@');
            $institutionId = null;
            $userType = 'respondent';
            
            if (Str::endsWith($emailDomain, '.edu') || Str::endsWith($emailDomain, '.edu.ph')) {
                $institution = Institution::where('domain', $emailDomain)->first();
                
                if ($institution) {
                    $institutionId = $institution->id;
                    $userType = 'researcher';
                }
            }
            
            // Get current IP address and hash it
            $currentIp = request()->ip();
            $hashedIp = $this->hashIpAddress($currentIp);
            
            $user = User::create([
                'first_name' => $googleUserData['given_name'] ?? $googleUserData['name'],
                'last_name' => $googleUserData['family_name'] ?? '',
                'email' => $googleUserData['email'],
                'password' => bcrypt(str()->random(32)),
                'email_verified_at' => now(),
                'is_active' => true,
                'type' => $userType,
                'institution_id' => $institutionId,
                'is_accepted_terms' => true,
                'is_accepted_privacy_policy' => true,
                'last_active_at' => TestTimeService::now(),
                'ip_address' => $hashedIp,
            ]);
            
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google user creation error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}