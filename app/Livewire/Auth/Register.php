<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Institution;
use App\Models\EmailVerification;
use App\Services\BrevoService;
use App\Services\TestTimeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Carbon\Carbon;

class Register extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;
    
    // OTP verification properties
    public string $otp_code = '';
    public bool $showOtpModal = false;
    public string $pendingEmail = '';
    public bool $showSuccess = false;
    public bool $resendCooldown = false;
    public int $resendCooldownSeconds = 0;
    
    protected function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:11|min:11|unique:users,phone_number',
            'password' => 'required|string|min:8|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%@]).*$/',
            'terms' => 'required|accepted',
        ];
    }

    protected function otpRules(): array
    {
        return [
            'otp_code' => 'required|string|size:6',
        ];
    }

    public function updated($propertyName)
    {
        // For password confirmation, check if passwords match in real-time
        if ($propertyName === 'password_confirmation') {
            if (!empty($this->password) && !empty($this->password_confirmation) && $this->password !== $this->password_confirmation) {
                $this->addError('password_confirmation', 'The passwords do not match.');
                return;
            } else {
                $this->resetErrorBag('password_confirmation');
            }
        }
        
        $this->validateOnly($propertyName);
    }

    public function registerUser()
    {
        // First check if passwords match before validation
        if (!empty($this->password) && !empty($this->password_confirmation) && $this->password !== $this->password_confirmation) {
            $this->dispatch('password-mismatch', [
                'message' => 'The passwords do not match. Please make sure both password fields are identical.'
            ]);
            return;
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            
            // Check for password length errors
            if ($errors->has('password') && str_contains($errors->first('password'), 'at least 8')) {
                $this->dispatch('password-length-error', [
                    'message' => 'Password must be at least 8 characters and include a special character and one uppercase letter.'
                ]);
                return;
            }
            
            // Check for phone number length errors
            if ($errors->has('phone_number') && (str_contains($errors->first('phone_number'), 'at least') || str_contains($errors->first('phone_number'), 'at most') || str_contains($errors->first('phone_number'), 'characters'))) {
                $this->dispatch('phone-length-error', [
                    'message' => 'Phone number must be exactly 11 digits.'
                ]);
                return;
            }
            
            // Check for password strength errors
            if ($errors->has('password') && str_contains($errors->first('password'), 'format is invalid')) {
                $this->dispatch('password-strength-error', [
                    'message' => 'Password must contain at least one uppercase letter and one special character.'
                ]);
                return;
            }
            
            // Check for specific duplicate errors
            if ($errors->has('email') && str_contains($errors->first('email'), 'already been taken')) {
                $this->dispatch('duplicate-email', [
                    'message' => 'This email address is already registered. Please use a different email or try logging in.'
                ]);
                return;
            }
            
            if ($errors->has('phone_number') && str_contains($errors->first('phone_number'), 'already been taken')) {
                $this->dispatch('duplicate-phone', [
                    'message' => 'This phone number is already registered. Please use a different phone number.'
                ]);
                return;
            }
            
            // Check for password confirmation mismatch (backup check)
            if ($errors->has('password') && (str_contains($errors->first('password'), 'confirmation does not match') || str_contains($errors->first('password'), 'confirmed'))) {
                $this->dispatch('password-mismatch', [
                    'message' => 'The passwords do not match. Please make sure both password fields are identical.'
                ]);
                return;
            }
            
            // Handle other validation errors
            $allErrors = $errors->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $allErrors)]);
            return;
        }

        // Check if there's an existing valid OTP for this email
        $existingVerification = EmailVerification::where('email', $this->email)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existingVerification) {
            // Valid OTP exists, just open the modal without sending a new code
            $this->pendingEmail = $this->email;
            $this->dispatch('open-modal', name: 'otp-verification');
            $this->dispatch('existing-otp-found', [
                'message' => 'A verification code was already sent to your email. Please check your inbox or spam folder.'
            ]);
            return;
        }

        // No valid OTP exists, generate a new one
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in database with 10-minute expiry
        EmailVerification::updateOrCreate(
            ['email' => $this->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addSeconds(60), // 10 minutes from now
            ]
        );

        // Send OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendOtpEmail($this->email, $otpCode);

        if (!$emailSent) {
            $this->dispatch('email-error', [
                'message' => 'Failed to send verification email. Please try again.'
            ]);
            return;
        }

        // Store pending email
        $this->pendingEmail = $this->email;
        
        // Dispatch browser event to open modal
        $this->dispatch('open-modal', name: 'otp-verification');
        
        $this->dispatch('otp-sent', [
            'message' => 'Verification code sent to your email!'
        ]);
    }

    /**
     * Hash an IP address for secure storage
     */
    private function hashIpAddress(string $ipAddress): string
    {
        return hash('sha256', $ipAddress);
    }

    public function verifyOtp()
    {
        
        try {
            $this->validate($this->otpRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $errors)]);
            return;
        }

        // Find the email verification record
        $emailVerification = EmailVerification::where('email', $this->pendingEmail)
            ->where('otp_code', $this->otp_code)
            ->first();

        if (!$emailVerification || $emailVerification->isExpired()) {
            $this->dispatch('otp-error', [
                'message' => 'Invalid or expired verification code. Please request a new code.'
            ]);
            return;
        }

        // OTP is valid, create the user
        $emailDomain = Str::after($this->pendingEmail, '@');
        $institutionId = null;
        
        // Determine user type based on email domain
        $userType = 'respondent'; // Default type
        
        // Check if email is from an educational institution (.edu domain)
        if (Str::endsWith($emailDomain, '.edu') || Str::endsWith($emailDomain, '.edu.ph')) {
            // Check if this institution exists in our database
            $institution = Institution::where('domain', $emailDomain)->first();
            
            if ($institution) {
                $institutionId = $institution->id;
                $userType = 'researcher'; // Educational email from recognized institution = researcher
            }
            // If institution not found but is .edu domain, stays as respondent for now
            // Can be upgraded on future logins if institution is added
        }

        try {
            // Get current IP address and hash it
            $currentIp = request()->ip();
            $hashedIp = $this->hashIpAddress($currentIp);

            $user = User::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->pendingEmail,
                'phone_number' => $this->phone_number,
                'password' => Hash::make($this->password),
                'type' => $userType,
                'institution_id' => $institutionId,
                'is_active' => true, 
                'email_verified_at' => now(),
                'is_accepted_terms' => true,
                'is_accepted_privacy_policy' => true,
                'last_active_at' => TestTimeService::now(),
                'ip_address' => $hashedIp, // Store hashed IP address
            ]);

            // Delete the email verification record
            $emailVerification->delete();

            Auth::login($user);

            $this->showSuccess = true;
            $this->dispatch('otp-verified-success');
            
            $this->dispatch('registration-success', [
                'message' => 'Your account has been created successfully! Welcome to Formigo.'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('registration-error', [
                'message' => 'An error occurred while creating your account. Please try again.'
            ]);
        }
    }

    public function resendOtp()
    {
        if ($this->resendCooldown) {
            return;
        }

        if (empty($this->pendingEmail)) {
            $this->dispatch('registration-error', [
                'message' => 'No pending email verification found. Please start registration again.'
            ]);
            return;
        }

        // Generate new 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update OTP in database with 10-minute expiry
        EmailVerification::updateOrCreate(
            ['email' => $this->pendingEmail],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // Send new OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendOtpEmail($this->pendingEmail, $otpCode);

        if ($emailSent) {
            $this->dispatch('otp-sent', [
                'message' => 'New verification code sent to your email!'
            ]);
            $this->startResendCooldown();
        } else {
            $this->dispatch('email-error', [
                'message' => 'Failed to send verification email. Please try again.'
            ]);
        }
    }

    private function startResendCooldown()
    {
        $this->resendCooldown = true;
        $this->resendCooldownSeconds = 60; // 60 seconds cooldown

        // Start the countdown timer
        $this->dispatch('start-resend-cooldown');
    }

    public function decrementCooldown()
    {
        if ($this->resendCooldown && $this->resendCooldownSeconds > 0) {
            $this->resendCooldownSeconds--;
            
            if ($this->resendCooldownSeconds <= 0) {
                $this->resendCooldown = false;
            }
        }
    }

    private function resetForm()
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->terms = false;
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
