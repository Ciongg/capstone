<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $this->secret = $google2fa->generateSecretKey();
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->user->email,
            $this->secret
        );
        
        $this->qrCodeUrl = $qrCodeUrl;
        $this->showQrCode = true;
        
        // Generate recovery codes
        $this->recoveryCodes = $this->generateRecoveryCodes();
        
        // Save to database but not enabled yet
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
        $this->validate([
            'verificationCode' => 'required|digits:6'
        ]);

        $google2fa = new Google2FA();
        $setting = $this->user->twoFactorSetting;

        $valid = $google2fa->verifyKey($setting->secret, $this->verificationCode);

        if (!$valid) {
            $this->addError('verificationCode', 'Invalid verification code. Please try again.');
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
        $this->dispatch('close-modal', name: 'setup-2fa-modal');
        $this->dispatch('show-success', message: '2FA enabled successfully! Please save your recovery codes.');
    }

    public function disableTwoFactor()
    {
        $this->validate([
            'password' => 'required'
        ]);

        if (!Hash::check($this->password, $this->user->password)) {
            $this->addError('password', 'Invalid password.');
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
