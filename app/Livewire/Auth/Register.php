<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Institution;
use App\Models\EmailVerification;
use App\Services\BrevoService;
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
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:8|confirmed',
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
        $this->validateOnly($propertyName);
    }

    public function registerUser()
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
        $emailSent = $brevoService->sendOtpEmail($this->email, $otpCode);

        if (!$emailSent) {
            session()->flash('error', 'Failed to send verification email. Please try again.');
            return;
        }

        // Store pending email
        $this->pendingEmail = $this->email;
        
        // Dispatch browser event to open modal
        $this->dispatch('open-modal', name: 'otp-verification');
        
        session()->flash('success', 'Verification code sent to your email!');
    }

    public function verifyOtp()
    {
        $this->validate($this->otpRules());

        // Find the email verification record
        $emailVerification = EmailVerification::where('email', $this->pendingEmail)
            ->where('otp_code', $this->otp_code)
            ->first();

        if (!$emailVerification || $emailVerification->isExpired()) {
            $this->addError('otp_code', 'Invalid or expired verification code. Please try again.');
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

        $user = User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->pendingEmail,
            'phone_number' => $this->phone_number,
            'password' => Hash::make($this->password),
            'type' => $userType,
            'institution_id' => $institutionId,
            'is_active' => true, // Set new users to active by default
            'email_verified_at' => now(),
        ]);

        // Delete the email verification record
        $emailVerification->delete();

        Auth::login($user);

        $this->showSuccess = true;
        $this->dispatch('otp-verified-success');
    }

    public function resendOtp()
    {
        if ($this->resendCooldown) {
            return;
        }

        if (empty($this->pendingEmail)) {
            session()->flash('error', 'No pending email verification found.');
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
            session()->flash('success', 'New verification code sent to your email!');
            $this->startResendCooldown();
        } else {
            session()->flash('error', 'Failed to send verification email. Please try again.');
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
