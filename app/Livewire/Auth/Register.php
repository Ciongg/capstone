<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class Register extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

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

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function registerUser()
    {
        $this->validate();

        $emailDomain = Str::after($this->email, '@');
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
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'password' => Hash::make($this->password),
            'type' => $userType,
            'institution_id' => $institutionId,
        ]);

        Auth::login($user);

        session()->flash('success', 'Registration successful!');
        
        // Use regular redirect which is most reliable
        return redirect()->route('feed.index');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
