<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuthentication extends Component
{
    public $user;
    public $isEnabled = false;
    public $showQrCode = false;
    public $qrCodeUrl = '';
    public $secret = '';
    public $verificationCode = '';
    public $recoveryCodes = [];
    public $password = '';
    public $showRecoveryCodes = false;

    public function mount()
    {
        $this->user = auth()->user();
        $setting = $this->user->twoFactorSetting;
        $this->isEnabled = $setting && $setting->enabled && $setting->confirmed_at;
    }

    public function toggleTwoFactor()
    {
        if ($this->isEnabled) {
            // Will be handled by SweetAlert confirmation
            $this->dispatch('confirm-disable-2fa');
        } else {
            $this->enableTwoFactor();
        }
    }

    public function enableTwoFactor()
    {
        $google2fa = new Google2FA();
        // Use longer Base32 secret for compatibility
        $this->secret = $google2fa->generateSecretKey(32);

        // Build standards-compliant otpauth URI:
        // otpauth://totp/Issuer:email?secret=SECRET&issuer=Issuer&algorithm=SHA1&digits=6&period=30
        $issuer = config('app.name', 'Formigo');
        $label = $issuer . ':' . $this->user->email;
        $this->qrCodeUrl = sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($label),
            $this->secret,
            rawurlencode($issuer)
        );

        $this->showQrCode = true;

        // Generate recovery codes
        $this->recoveryCodes = $this->generateRecoveryCodes();

        // Save to database but not enabled yet (Model may encrypt via accessor)
        $setting = $this->user->getOrCreateTwoFactorSetting();
        $setting->update([
            'secret' => $this->secret,
            'recovery_codes' => $this->recoveryCodes,
            'enabled' => false,
            'confirmed_at' => null
        ]);

        $this->dispatch('open-modal', name: 'setup-2fa-modal');
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

    public function disableTwoFactor()
    {
        // Manual validation -> SweetAlert
        if (trim($this->password ?? '') === '') {
            $this->dispatch('show-error', message: 'Password is required.');
            return;
        }

        if (!Hash::check($this->password, $this->user->password)) {
            $this->dispatch('show-error', message: 'Invalid password.');
            return;
        }

        $setting = $this->user->twoFactorSetting;
        if ($setting) {
            $setting->update([
                'enabled' => false,
                'secret' => null,
                'recovery_codes' => null,
                'confirmed_at' => null
            ]);
        }

        $this->isEnabled = false;
        $this->password = '';
        $this->dispatch('close-modal', name: 'disable-2fa-modal');
        $this->dispatch('show-success', message: '2FA has been disabled.');
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
