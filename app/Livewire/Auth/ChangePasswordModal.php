<?php

namespace App\Livewire\Auth;

use App\Models\EmailVerification;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Carbon\Carbon;

class ChangePasswordModal extends Component
{
    public string $current_password = '';
    public string $otp_code = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    
    public string $currentStep = 'verify';
    public bool $showSuccess = false;
    public bool $resendCooldown = false;
    public int $resendCooldownSeconds = 0;

    protected function currentPasswordRules(): array
    {
        return [
            'current_password' => 'required|string',
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
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function sendVerificationCode()
    {
        try {
            $this->validate($this->currentPasswordRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $errors)]);
            return;
        }

        // Verify current password
        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->dispatch('current-password-error', [
                'message' => 'The current password you entered is incorrect. Please try again.'
            ]);
            return;
        }

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in database with 10-minute expiry
        EmailVerification::updateOrCreate(
            ['email' => auth()->user()->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10)
            ]
        );

        // Send OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendPasswordChangeEmail(auth()->user()->email, $otpCode);

        if (!$emailSent) {
            $this->dispatch('email-error', [
                'message' => 'Failed to send verification email. Please try again.'
            ]);
            return;
        }

        // Move to OTP step
        $this->currentStep = 'otp';
        $this->dispatch('otp-sent', [
            'message' => 'Verification code sent to your email!'
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
        $emailVerification = EmailVerification::where('email', auth()->user()->email)
            ->where('otp_code', $this->otp_code)
            ->first();

        // Check if verification record exists
        if (!$emailVerification) {
            $this->dispatch('otp-error', [
                'message' => 'Invalid verification code. Please try again or request a new code.'
            ]);
            return;
        }

        // Check if OTP is expired
        if ($emailVerification->isExpired()) {
            $this->dispatch('otp-error', [
                'message' => 'Verification code has expired. Please request a new code.'
            ]);
            return;
        }

        // OTP is valid, move to password change step
        $this->currentStep = 'password';
    }

    public function changePassword()
    {
        try {
            $this->validate($this->passwordRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            
            if ($errors->has('new_password') && str_contains($errors->first('new_password'), 'at least 8')) {
                $this->dispatch('password-length-error', [
                    'message' => 'Password must be at least 8 characters.'
                ]);
                return;
            }
            
            if ($errors->has('new_password') && (str_contains($errors->first('new_password'), 'confirmation does not match') || str_contains($errors->first('new_password'), 'confirmed'))) {
                $this->dispatch('password-mismatch', [
                    'message' => 'The passwords do not match. Please make sure both password fields are identical.'
                ]);
                return;
            }
            
            $allErrors = $errors->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $allErrors)]);
            return;
        }

        // Check if new password is the same as old password
        if (Hash::check($this->new_password, auth()->user()->password)) {
            $this->dispatch('password-same-as-old', [
                'message' => 'Your new password cannot be the same as your current password. Please choose a different password.'
            ]);
            return;
        }

        // Update the user's password
        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        // Delete the email verification record
        EmailVerification::where('email', auth()->user()->email)->delete();

        // Reset form
        $this->reset(['current_password', 'otp_code', 'new_password', 'new_password_confirmation']);
        $this->currentStep = 'verify';

        $this->showSuccess = true;
        $this->dispatch('password-changed-success', [
            'message' => 'Your password has been changed successfully!'
        ]);
    }

    public function resendOtp()
    {
        if ($this->resendCooldown) {
            return;
        }

        // Generate new 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update OTP in database
        EmailVerification::updateOrCreate(
            ['email' => auth()->user()->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // Send new OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendPasswordChangeEmail(auth()->user()->email, $otpCode);

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
        $this->resendCooldownSeconds = 60;
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

    public function resetForm()
    {
        $this->reset(['current_password', 'otp_code', 'new_password', 'new_password_confirmation']);
        $this->currentStep = 'verify';
        $this->showSuccess = false;
        $this->resendCooldown = false;
        $this->resendCooldownSeconds = 0;
    }

    public function render()
    {
        return view('livewire.auth.change-password-modal');
    }
}
