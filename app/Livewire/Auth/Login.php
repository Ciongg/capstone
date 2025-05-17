<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
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
        $this->validate();

        // First check if the user is archived (soft-deleted)
        $archivedUser = User::withTrashed()
            ->where('email', $this->email)
            ->whereNotNull('deleted_at')
            ->first();
            
        if ($archivedUser) {
            // Show archived message regardless of password correctness
            throw ValidationException::withMessages([
                'email' => ['This account has been archived. Please contact the Formigo support team for assistance.'],
            ]);
        }

        // Proceed with normal authentication if not archived
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')], 
            ]);
        }

        // Get the authenticated user
        $user = Auth::user();
        
        // If the user was inactive, reactivate their account
        if (!$user->is_active) {
            $user->is_active = true;
            $user->save();
            session()->flash('account-reactivated', 'Your account has been reactivated!');
        }

        session()->regenerate();
        
        // After successful login, check user's institution status
        $statusChange = $this->checkInstitutionStatus($user);
        
        // Flash appropriate message based on the status change
        if ($statusChange === 'upgraded') {
            session()->flash('account-upgrade', 'Your account has been upgraded to Researcher status! Your institution is now recognized in our system.');
        } elseif ($statusChange === 'downgraded') {
            session()->flash('account-downgrade', 'Your account has been changed to Respondent status. This could be because your institution is no longer in our system or your email domain changed.');
        } elseif ($statusChange === 'institution-restored') {
            session()->flash('account-upgrade', 'Your institution has been restored in our system. All features are now available.');
        } elseif ($statusChange === 'institution-changed') {
            session()->flash('account-upgrade', 'Your institution has been updated in our system based on your email domain.');
        } elseif ($statusChange === 'institution-lost') {
            session()->flash('account-downgrade', 'Your institution is no longer recognized in our system. Some features will be limited.');
        }
        
        return redirect()->route('feed.index');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
