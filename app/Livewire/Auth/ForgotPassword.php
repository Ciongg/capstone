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
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function sendResetEmail()
    {
        $this->validate();

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in database with 10-minute expiry
        EmailVerification::updateOrCreate(
            ['email' => $this->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // Send OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendPasswordResetEmail($this->email, $otpCode);

        if (!$emailSent) {
            $this->addError('email', 'Failed to send reset email. Please try again.');
            return;
        }

        // Move to OTP step
        $this->currentStep = 'otp';
        session()->flash('success', 'Reset code sent to your email!');
    }

    public function verifyOtp()
    {
        $this->validate($this->otpRules());

        // Find the email verification record
        $emailVerification = EmailVerification::where('email', $this->email)
            ->where('otp_code', $this->otp_code)
            ->first();

        if (!$emailVerification || $emailVerification->isExpired()) {
            $this->addError('otp_code', 'Invalid or expired verification code. Please try again.');
            return;
        }

        // OTP is valid, move to password reset step
        $this->currentStep = 'password';
    }

    public function resetPassword()
    {
        $this->validate($this->passwordRules());

        // Find the user
        $user = User::where('email', $this->email)->first();
        
        if (!$user) {
            $this->addError('new_password', 'User not found.');
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
    }

    public function resendOtp()
    {
        if ($this->resendCooldown) {
            return;
        }

        if (empty($this->email)) {
            $this->addError('otp_code', 'No email address found.');
            return;
        }

        // Generate new 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update OTP in database with 10-minute expiry
        EmailVerification::updateOrCreate(
            ['email' => $this->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // Send new OTP email
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendPasswordResetEmail($this->email, $otpCode);

        if ($emailSent) {
            session()->flash('success', 'New reset code sent to your email!');
            $this->startResendCooldown();
        } else {
            $this->addError('otp_code', 'Failed to send reset email. Please try again.');
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