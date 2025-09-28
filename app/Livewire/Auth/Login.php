<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Str;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|string|email|max:256',
            'password' => 'required|string|min:8|max:128',
            'remember' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Check user's institution status without changing their role type
     */
    protected function checkInstitutionStatus($user)
    {
        $emailDomain = Str::after($user->email, '@');
        $institution = Institution::where('domain', $emailDomain)->first();
        
        // 1. For researchers (can be upgraded or downgraded)
        if ($user->type === 'respondent' || $user->type === 'researcher') {
            // Check if email is from an educational institution (.edu domain)
            if (Str::endsWith($emailDomain, '.edu') || Str::endsWith($emailDomain, '.edu.ph')) {
                // Upgrade respondent to researcher if institution exists
                if ($user->type === 'respondent' && $institution) {
                    $user->update([
                        'type' => 'researcher', 
                        'institution_id' => $institution->id,
                    ]);
                    return 'upgraded';
                }
                
                // Downgrade researcher to respondent if institution doesn't exist
                if ($user->type === 'researcher' && !$institution) {
                    $user->update([
                        'type' => 'respondent', 
                        'institution_id' => null,
                    ]);
                    return 'downgraded';
                }
            } else if ($user->type === 'researcher') {
                // Non-edu email shouldn't be researcher
                $user->update([
                    'type' => 'respondent', 
                    'institution_id' => null,
                ]);
                return 'downgraded';
            }
        }
        
        // 2. For institution admins - check if their institution has been restored
        if ($user->type === 'institution_admin') {
            // First check if the domain matches an institution in our system
            // This is now the primary check - focusing on domain rather than checking existing ID first
            if ($institution) {
                // If we found a matching institution for this domain
                if ($user->institution_id != $institution->id) {
                    $user->update([
                        'institution_id' => $institution->id
                    ]);
                    return 'institution-restored';
                }
            } else {
                // No institution exists for this email domain
                if ($user->institution_id !== null) {
                    $user->update([
                        'institution_id' => null
                    ]);
                    return 'institution-lost';
                }
            }
        }
        
        return false; // No change
    }

    public function attemptLogin()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            
            // Check for password length error specifically
            if ($errors->has('password') && str_contains($errors->first('password'), 'at least 8')) {
                $this->dispatch('password-length-error', [
                    'message' => 'Password must be at least 8 characters.'
                ]);
                return;
            }
            
            // Handle other validation errors
            $allErrors = $errors->all();
            $this->dispatch('validation-error', ['message' => implode(' ', $allErrors)]);
            return;
        }

        // First check if the user is archived (soft-deleted)
        $archivedUser = User::withTrashed()
            ->where('email', $this->email)
            ->whereNotNull('deleted_at')
            ->first();
            
        if ($archivedUser) {
            $this->dispatch('archived-account', [
                'message' => 'This account has been archived. Please contact the Formigo support team for assistance.'
            ]);
            return;
        }

        // Set session lifetime to 1 day if remember is not checked
        if (!$this->remember) {
            config(['session.lifetime' => 1440]); // 1 day in minutes
        }

        // Proceed with normal authentication with remember option
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Restore session lifetime to default if login fails
            if (!$this->remember) {
                config(['session.lifetime' => config('session.lifetime', 120)]);
            }
            
            $this->dispatch('login-error', [
                'message' => 'Invalid email or password. Please check your credentials and try again.'
            ]);
            return;
        }

        // Get the authenticated user
        $user = Auth::user();
        
        // If the user was inactive, reactivate their account
        if (!$user->is_active) {
            $user->is_active = true;
            $user->save();
            $this->dispatch('account-status-change', [
                'type' => 'success',
                'title' => 'Account Reactivated',
                'message' => 'Your account has been reactivated!'
            ]);
        }

        session()->regenerate(); //regens session ID

        // Restore session lifetime to default after login
        if (!$this->remember) {
            config(['session.lifetime' => config('session.lifetime', 120)]); //2 hours
        }
        
        // After successful login, check user's institution status
        $statusChange = $this->checkInstitutionStatus($user);
        
        // Dispatch appropriate message based on the status change
        if ($statusChange === 'upgraded') {
            $this->dispatch('account-status-change', [
                'type' => 'success',
                'title' => 'Account Upgraded',
                'message' => 'Your account has been upgraded to Researcher status! Your institution is now recognized in our system.'
            ]);
        } elseif ($statusChange === 'downgraded') {
            $this->dispatch('account-status-change', [
                'type' => 'warning',
                'title' => 'Account Status Changed',
                'message' => 'Your account has been changed to Respondent status. This could be because your institution is no longer in our system or your email domain changed.'
            ]);
        } elseif ($statusChange === 'institution-restored') {
            $this->dispatch('account-status-change', [
                'type' => 'success',
                'title' => 'Institution Restored',
                'message' => 'Your institution has been restored in our system. All features are now available.'
            ]);
        } elseif ($statusChange === 'institution-changed') {
            $this->dispatch('account-status-change', [
                'type' => 'info',
                'title' => 'Institution Updated',
                'message' => 'Your institution has been updated in our system based on your email domain.'
            ]);
        } elseif ($statusChange === 'institution-lost') {
            $this->dispatch('account-status-change', [
                'type' => 'warning',
                'title' => 'Institution Status Changed',
                'message' => 'Your institution is no longer recognized in our system. Some features will be limited.'
            ]);
        }
        
        return redirect()->route('feed.index');
    }

    public function render()
    {
        // Get warning message from session if user was redirected from survey answer page
        $warningMessage = session('warning_message');
        
        return view('livewire.auth.login', compact('warningMessage'));
    }
}
