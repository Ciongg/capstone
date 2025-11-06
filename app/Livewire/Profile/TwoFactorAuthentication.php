<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Models\EmailVerification;
use App\Services\BrevoService;
use Carbon\Carbon;

class TwoFactorAuthentication extends Component
{
    public $user;
    public $isEnabled = false;
    public $showQrCode = false;
    public $qrCodeUrl = '';
    public $secret = '';
    public $verificationCode = '';
    public $recoveryCodes = [];
    public $showRecoveryCodes = false;
    public $disableMethod = 'authenticator'; // 'authenticator' or 'email'
    public $disableStep = 'method';
    public $disableOtpCode = '';
    public $disableAuthCode = '';
    public $disableResendCooldown = false;
    public $disableResendCooldownSeconds = 0;
    public $isGeneratingQrCode = false;

    protected $listeners = [
        'resetDisableFlow' => 'handleModalOpen',
        'open-2fa-settings' => 'handleOpen2FASettings'
    ];

    public function mount()
    {
        $this->user = auth()->user();
        $setting = $this->user->twoFactorSetting;
        $this->isEnabled = $setting && $setting->enabled && $setting->confirmed_at;
    }

    public function handleModalOpen()
    {
        // Refresh the enabled state from database
        $setting = $this->user->fresh()->twoFactorSetting;
        $this->isEnabled = $setting && $setting->enabled && $setting->confirmed_at;
        
        if ($this->isEnabled) {
            // If 2FA is enabled, prepare disable flow
            $this->resetDisableFlow();
        } else {
            // If 2FA is disabled, close this modal and open setup modal
            $this->dispatch('close-modal', name: 'disable-2fa-modal');
            $this->enableTwoFactor();
        }
    }

    public function handleOpen2FASettings()
    {
        // Refresh the enabled state from database
        $setting = $this->user->fresh()->twoFactorSetting;
        $this->isEnabled = $setting && $setting->enabled && $setting->confirmed_at;
        
        if ($this->isEnabled) {
            // If 2FA is enabled, open disable modal
            $this->resetDisableFlow();
            $this->dispatch('open-modal', name: 'disable-2fa-modal');
        } else {
            // If 2FA is disabled, open setup modal immediately
            $this->openSetupModal();
        }
    }

    public function toggleTwoFactor()
    {
        if ($this->isEnabled) {
            $this->startDisableFlow();
        } else {
            $this->enableTwoFactor();
        }
    }

    public function openSetupModal()
    {
        // Set loading state and open modal immediately
        $this->isGeneratingQrCode = true;
        $this->showQrCode = false;
        $this->showRecoveryCodes = false;
        $this->dispatch('open-modal', name: 'setup-2fa-modal');
    }

    public function generateQrCode()
    {
        // Add a small delay to ensure modal is fully rendered
        usleep(100000); // 0.1 second
        
        $google2fa = new Google2FA();
        // Use longer Base32 secret for compatibility
        $this->secret = $google2fa->generateSecretKey(32);

        // Build standards-compliant otpauth URI:
        // otpauth://totp/Issuer:email?secret=SECRET&issuer=Issuer&algorithm=SHA1&digits=6&period=30
        $issuer = 'Formigo';
        $label = $issuer . ':' . $this->user->email;
        $imageUrl = url('images/icons/Formigo.png');
        $this->qrCodeUrl = 'otpauth://totp/' . rawurlencode($label) . '?' . http_build_query([
            'secret' => $this->secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
            'image' => $imageUrl,
        ], '', '&', PHP_QUERY_RFC3986);

        // Generate recovery codes
        $plainRecoveryCodes = $this->generateRecoveryCodes();
        $this->recoveryCodes = $plainRecoveryCodes;
        $hashedRecoveryCodes = array_map(fn ($code) => Hash::make($code), $plainRecoveryCodes);

        // Save to database but not enabled yet (Model may encrypt via accessor)
        $setting = $this->user->getOrCreateTwoFactorSetting();
        $setting->update([
            'secret' => $this->secret,
            'recovery_codes' => $hashedRecoveryCodes,
            'enabled' => false,
            'confirmed_at' => null
        ]);

        // Set flags to show QR code
        $this->showQrCode = true;
        $this->isGeneratingQrCode = false;
    }

    public function enableTwoFactor()
    {
        $this->openSetupModal();
    }

    public function confirmSetup()
    {
        // Manual validation -> SweetAlert
        $code = trim($this->verificationCode ?? '');
        if ($code === '') {
            $this->dispatch('show-error', message: 'Please enter the 6-digit code.');
            return;
        }
        if (!preg_match('/^\d{6}$/', $code)) {
            $this->dispatch('show-error', message: 'Enter a valid 6-digit code.');
            return;
        }

        $google2fa = new Google2FA();
        $setting = $this->user->twoFactorSetting;

        // Prefer decrypted secret if model stored it encrypted; fallback to plain
        $secret = $setting->secret;
        if (!preg_match('/^[A-Z2-7]+=*$/', (string) $secret)) {
            try {
                $secret = Crypt::decryptString($setting->getRawOriginal('secret'));
            } catch (\Throwable $e) {
                $secret = $setting->secret; // best effort
            }
        }

        // Allow more time drift during setup
        $valid = $google2fa->verifyKey($secret, $code, 4);

        if (!$valid) {
            $this->dispatch('show-error', message: 'Invalid verification code. Please try again.');
            return;
        }

        // Enable 2FA
        $setting->update([
            'enabled' => true,
            'confirmed_at' => now()
        ]);

        $this->isEnabled = true;
        $this->showQrCode = false;
        $this->showRecoveryCodes = true;
        $this->verificationCode = '';

        session()->flash('message', '2FA has been enabled successfully!');
        $this->dispatch('show-success', message: '2FA enabled! Please save your recovery codes below to recover access if you lose your authenticator.');
    }

    public function startDisableFlow()
    {
        $this->resetDisableFlow();
        $this->dispatch('open-modal', name: 'disable-2fa-modal');
    }

    public function switchToEmailRecovery()
    {
        $this->disableMethod = 'email';
        $this->disableStep = 'email-intro';
        $this->disableAuthCode = '';
    }

    public function switchBackToAuthenticator()
    {
        $this->disableMethod = 'authenticator';
        $this->disableStep = 'method';
        $this->disableOtpCode = '';
        $this->disableResendCooldown = false;
        $this->disableResendCooldownSeconds = 0;
    }

    public function verifyAuthenticatorForDisable()
    {
        $code = trim($this->disableAuthCode ?? '');
        if (!preg_match('/^\d{6}$/', $code)) {
            $this->dispatch('show-error', message: 'Enter the 6-digit code from your authenticator app.');
            return;
        }

        $google2fa = new Google2FA();
        $setting = $this->user->twoFactorSetting;

        $secret = $setting->secret;
        if (!preg_match('/^[A-Z2-7]+=*$/', (string) $secret)) {
            try {
                $secret = Crypt::decryptString($setting->getRawOriginal('secret'));
            } catch (\Throwable $e) {
                $secret = $setting->secret;
            }
        }

        $valid = $google2fa->verifyKey($secret, $code, 4);

        if (!$valid) {
            $this->dispatch('show-error', message: 'Invalid verification code. Please try again.');
            return;
        }

        $this->completeDisableTwoFactor();
        $this->disableStep = 'success';
        $this->disableAuthCode = '';
        $this->dispatch('show-success', message: 'Two-factor authentication has been disabled.');
    }

    public function sendDisableOtp()
    {
        // Check if there's an existing valid OTP for this email
        $existingVerification = EmailVerification::where('email', $this->user->email)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existingVerification) {
            // Valid OTP exists, just move to OTP step without sending a new code
            $this->disableStep = 'otp';
            $this->disableOtpCode = '';
            $this->dispatch('show-error', message: 'A verification code was already sent to your email. Please check your inbox or use the resend button if needed.');
            return;
        }

        // No valid OTP exists, generate a new one
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::updateOrCreate(
            ['email' => $this->user->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        $mailer = app(BrevoService::class);
        if (!$mailer->sendTwoFactorRecoveryEmail($this->user->email, $otpCode)) {
            $this->dispatch('show-error', message: 'Failed to send recovery code. Please try again.');
            return;
        }

        $this->disableStep = 'otp';
        $this->disableOtpCode = '';
        $this->startDisableResendCooldown();
        $this->dispatch('show-success', message: 'Recovery code sent to your email.');
    }

    public function verifyDisableOtp()
    {
        $code = trim($this->disableOtpCode ?? '');
        if (!preg_match('/^\d{6}$/', $code)) {
            $this->dispatch('show-error', message: 'Enter the 6-digit code sent to your email.');
            return;
        }

        $record = EmailVerification::where('email', $this->user->email)
            ->where('otp_code', $code)
            ->first();

        if (!$record) {
            $this->dispatch('show-error', message: 'Invalid verification code. Please try again.');
            return;
        }

        if ($record->isExpired()) {
            $this->dispatch('show-error', message: 'Verification code has expired. Please request a new code.');
            return;
        }

        $this->completeDisableTwoFactor();
        EmailVerification::where('email', $this->user->email)->delete();

        $this->disableStep = 'success';
        $this->disableOtpCode = '';
        $this->disableResendCooldown = false;
        $this->disableResendCooldownSeconds = 0;

        $this->dispatch('show-success', message: 'Two-factor authentication has been disabled.');
    }

    public function resendDisableOtp()
    {
        if ($this->disableResendCooldown) {
            return;
        }

        // Generate new OTP without checking for existing one
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::updateOrCreate(
            ['email' => $this->user->email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        $mailer = app(BrevoService::class);
        if (!$mailer->sendTwoFactorRecoveryEmail($this->user->email, $otpCode)) {
            $this->dispatch('show-error', message: 'Failed to send recovery code. Please try again.');
            return;
        }

        $this->startDisableResendCooldown();
        $this->dispatch('twofactor-otp-sent', message: 'New verification code sent to your email!');
    }

    private function completeDisableTwoFactor(): void
    {
        $setting = $this->user->twoFactorSetting;
        if ($setting) {
            $setting->update([
                'enabled' => false,
                'secret' => null,
                'recovery_codes' => null,
                'confirmed_at' => null,
            ]);
        }

        $this->isEnabled = false;
        $this->showQrCode = false;
        $this->showRecoveryCodes = false;
        $this->recoveryCodes = [];
    }

    private function resetDisableFlow(): void
    {
        $this->disableMethod = 'authenticator';
        $this->disableStep = 'method';
        $this->disableOtpCode = '';
        $this->disableAuthCode = '';
        $this->disableResendCooldown = false;
        $this->disableResendCooldownSeconds = 0;
    }

    private function startDisableResendCooldown(int $seconds = 300): void
    {
        $this->disableResendCooldown = true;
        $this->disableResendCooldownSeconds = $seconds;
        $this->dispatch('twofactor-start-resend-cooldown');
    }

    public function decrementDisableCooldown(): void
    {
        if (!$this->disableResendCooldown) {
            return;
        }

        $this->disableResendCooldownSeconds--;
        if ($this->disableResendCooldownSeconds <= 0) {
            $this->disableResendCooldown = false;
            $this->disableResendCooldownSeconds = 0;
        }
    }

    public function closeDisableModal(): void
    {
        $this->dispatch('close-modal', name: 'disable-2fa-modal');
        $this->resetDisableFlow();
    }


       private function generateRecoveryCodes($count = 8)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }

    public function render()
    {
        return view('livewire.profile.two-factor-authentication');
    }
}
