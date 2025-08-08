<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleOAuthService;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    protected $googleService;

    public function __construct(GoogleOAuthService $googleService)
    {
        $this->googleService = $googleService;
    }

    // Redirect to Google for signup
    public function redirect()
    {
        return $this->googleService->getGoogleRedirect();
    }

    // Handle Google callback for signup
    public function callback(Request $request)
    {
        $googleUser = $this->googleService->getGoogleUser($request);
        if (!$googleUser) {
            return redirect()->route('register')->with('error', 'Google authentication failed.');
        }

        // Get remember value from query parameter (default: false)
        $remember = $request->query('remember', false);

        // Check if user already exists by email
        $user = $this->googleService->findUserByEmail($googleUser->getEmail());
        if ($user) {
            // Set session lifetime for remember me
            if ($remember) {
                config(['session.lifetime' => config('session.remember_me_lifetime', 43200)]); // 30 days
            } else {
                config(['session.lifetime' => config('session.lifetime', 120)]); // default
            }

            Auth::login($user, $remember);
            
            session()->regenerate();

            // Restore session lifetime to default after login
            config(['session.lifetime' => config('session.lifetime', 120)]);

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
            
            return redirect()->route('feed.index')->with('success', 'Login successful!');
        }

        // Show consent page before creating account
        return view('auth.google-consent', [
            'googleUser' => $googleUser,
        ]);
    }

    // Handle consent form submission to create account
    public function consent(Request $request)
    {
        $googleUserData = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'given_name' => 'nullable|string',
            'family_name' => 'nullable|string',
            'avatar' => 'nullable|url',
        ]);
        // Actually create the user
        $user = $this->googleService->createUserFromGoogle($googleUserData);
        if ($user) {
            Auth::login($user);
            return redirect()->route('feed.index')->with('success', 'Registration successful!');
        }
        return redirect()->route('register')->with('error', 'Account creation failed.');
    }

    // Add this method to check institution status
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
}