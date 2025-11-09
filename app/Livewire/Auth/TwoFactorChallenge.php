<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Services\TestTimeService;

class TwoFactorChallenge extends Component
{
    public string $code = '';
    public bool $useRecoveryCode = false;
    public string $recoveryCode = '';

    protected function rules(): array
    {
        if ($this->useRecoveryCode) {
            return [
                'recoveryCode' => 'required|string'
            ];
        }
        
        return [
            'code' => 'required|digits:6'
        ];
    }

    public function toggleRecoveryMode()
    {
        $this->useRecoveryCode = !$this->useRecoveryCode;
        $this->code = '';
        $this->recoveryCode = '';
        $this->resetErrorBag();
    }

    public function verify()
    {
        $this->validate();

        // Get user ID from session
        $userId = Session::get('2fa:user:id');
        
        if (!$userId) {
            $this->dispatch('2fa-session-expired');
            return;
        }

        $user = \App\Models\User::find($userId);
        
        if (!$user || !$user->twoFactorSetting) {
            $this->dispatch('2fa-session-expired');
            return;
        }

        if ($this->useRecoveryCode) {
            // Verify recovery code
            if ($this->verifyRecoveryCode($user)) {
                $this->completeLogin($user);
                
                // Dispatch success event
                $this->dispatch('2fa-success');
                
                // Small delay to show success message before redirect
                $this->dispatch('redirect-after-success');
                return;
            }
            
            $this->dispatch('2fa-error-recovery');
            return;
        }

        // Verify TOTP code
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(
            $user->twoFactorSetting->secret,
            $this->code,
            2 // Allow 2 windows of time drift
        );

        if (!$valid) {
            $this->dispatch('2fa-error-code');
            return;
        }

        $this->completeLogin($user);
        
        // Dispatch success event
        $this->dispatch('2fa-success');
        
        // Small delay to show success message before redirect
        $this->dispatch('redirect-after-success');
    }

    private function verifyRecoveryCode($user): bool
    {
        $recoveryCodes = $user->twoFactorSetting->recovery_codes ?? [];
        
        // Iterate through stored hashed recovery codes
        foreach ($recoveryCodes as $index => $hashedCode) {
            // Use Hash::check to compare the plain text input with the hashed stored code
            if (Hash::check($this->recoveryCode, $hashedCode)) {
                // Remove the used recovery code
                unset($recoveryCodes[$index]);
                $user->twoFactorSetting->update([
                    'recovery_codes' => array_values($recoveryCodes)
                ]);
                return true;
            }
        }
        
        return false;
    }

    private function completeLogin($user)
    {
        // Clear 2FA session
        Session::forget('2fa:user:id');
        Session::forget('2fa:remember');

        // Get remember preference from session
        $remember = Session::get('2fa:remember', false);

        // Log the user in
        Auth::login($user, $remember);
        
        // Update last active timestamp
        $user->update(['last_active_at' => TestTimeService::now()]);
        
        // Regenerate session
        Session::regenerate();
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge');
    }
}
