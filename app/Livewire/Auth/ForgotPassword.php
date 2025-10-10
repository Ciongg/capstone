<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\EmailVerification;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Carbon\Carbon;

class ForgotPassword extends Component
{
    public string $email = '';
    public string $otp_code = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    
    public string $currentStep = 'email';
    public bool $showSuccess = false;
    public bool $resendCooldown = false;
    public int $resendCooldownSeconds = 0;

    protected function rules(): array
    {
        return [
            'email' => 'required|string|email|exists:users,email',
        ];
    }

    protected function otpRules(): array
    {
        return [
            'otp_code' => 'required|string|size:6',
        ];
    }

    protected function passwordRules(): array
    {
        return [
            'new_password' => 'required|string|min:8|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%@]).*$/',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function sendResetEmail()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidatpionException $e) {
            $errors = $e->validator->errors();
            
            // Check for specific email errors
            if ($errors->has('email')) {
                if (str_contains($errors->first('email'), 'required')) {
                    $this->dispatch('validation-error', [
                        'message' => 'Please enter your email address.'
                    ]);
                    return;
                }
                
                if (str_contains($errors->first('email'), 'valid email')) {
                    $this->dispatch('validation-error', [
                        'message' => 'Please enter a valid email address.'
                    ]);
                    return;
                }
                
                // Modified: More direct check for the exists rule failure
                // Laravel typically uses "The selected email is invalid" for exists rule failures
                $this->dispatch('email-not-found', [
                    'message' => 'This email address is not registered in our system.'
                ]);
                return;
            }
            
            // Handle other validation errors
            $allErrors = $errors->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $allErrors)]);
            return;
        }

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in database with 10-minute expiry (or 1 second for testing)
        EmailVerification::updateOrCreate(
            ['email' => $this->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addSeconds(60), // Change to addMinutes(10) for production
            ]
        );

        // Send OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendPasswordResetEmail($this->email, $otpCode);

        if (!$emailSent) {
            $this->dispatch('email-error', [
                'message' => 'Failed to send reset email. Please try again.'
            ]);
            return;
        }

        // Move to OTP step
        $this->currentStep = 'otp';
        $this->dispatch('otp-sent', [
            'message' => 'Reset code sent to your email!'
        ]);
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
        $emailVerification = EmailVerification::where('email', $this->email)
            ->where('otp_code', $this->otp_code)
            ->first();

        // Check if verification record exists
        if (!$emailVerification) {
            $this->dispatch('otp-error', [
                'message' => 'Invalid verification code. Please try again or request a new code.'
            ]);
            return;
        }

        // Use the model's isExpired() method to check expiration
        if ($emailVerification->isExpired()) {
            $this->dispatch('otp-error', [
                'message' => 'Verification code has expired. Please request a new code.'
            ]);
            return;
        }

        // OTP is valid, move to password reset step
        $this->currentStep = 'password';
    }

    public function resetPassword()
    {
        try {
            $this->validate($this->passwordRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            
            // Check for password length errors
            if ($errors->has('new_password') && str_contains($errors->first('new_password'), 'at least 8')) {
                $this->dispatch('password-length-error', [
                    'message' => 'Password must be at least 8 characters and include a special character and one uppercase letter.'
                ]);
                return;
            }
            
            // Check for password strength errors
            if ($errors->has('new_password') && str_contains($errors->first('new_password'), 'format is invalid')) {
                $this->dispatch('password-strength-error', [
                    'message' => 'Password must contain at least one uppercase letter and one special character.'
                ]);
                return;
            }
            
            // Check for password confirmation mismatch
            if ($errors->has('new_password') && (str_contains($errors->first('new_password'), 'confirmation does not match') || str_contains($errors->first('new_password'), 'confirmed'))) {
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

        // Find the user
        $user = User::where('email', $this->email)->first();
        
        if (!$user) {
            $this->dispatch('validation-error', [
                'message' => 'User not found. Please try again with a different email.'
            ]);
            return;
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        // Delete the email verification record
        EmailVerification::where('email', $this->email)->delete();

        // Reset form
        $this->reset(['otp_code', 'new_password', 'new_password_confirmation']);
        $this->currentStep = 'email';

        $this->showSuccess = true;
        $this->dispatch('password-reset-success', [
            'message' => 'Your password has been reset successfully! You can now login with your new password.'
        ]);
    }

    public function resendOtp()
    {
        if ($this->resendCooldown) {
            return;
        }

        if (empty($this->email)) {
            $this->dispatch('validation-error', [
                'message' => 'No email address found. Please start again.'
            ]);
            return;
        }

        // Generate new 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update OTP in database with expiry time
        EmailVerification::updateOrCreate(
            ['email' => $this->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addSeconds(60), // Change to addMinutes(10) for production
            ]
        );

        // Send new OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendPasswordResetEmail($this->email, $otpCode);

        if ($emailSent) {
            $this->dispatch('otp-sent', [
                'message' => 'New reset code sent to your email!'
            ]);
            $this->startResendCooldown();
        } else {
            $this->dispatch('email-error', [
                'message' => 'Failed to send reset email. Please try again.'
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

    public function render()
    {
        return view('livewire.auth.forgot-password-modal');
    }
}