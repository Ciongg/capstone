<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleOAuthService;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

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
        $result = $this->googleService->handleGoogleCallback($request);
        
        if (!$result) {
            return redirect()->route('login')
                ->with('error', 'Google authentication failed. Please try again.');
        }

        $user = $result['user'];
        $needs2FA = $result['needs_2fa'] ?? false;

        // Check user's institution status before completing login
        $statusChange = $this->checkInstitutionStatus($user);
        if ($statusChange) {
            Session::put('institution_status_change', $statusChange);
        }

        // If 2FA is required, redirect to 2FA challenge
        if ($needs2FA) {
            return redirect()->route('two-factor.challenge');
        }

        // No 2FA required, log in directly
        Auth::login($user);
        session()->regenerate();

        // Show institution status change messages
        if (Session::has('institution_status_change')) {
            $change = Session::get('institution_status_change');
            
            if ($change === 'upgraded') {
                session()->flash('account-upgrade', 'Your account has been upgraded to Researcher status! Your institution is now recognized in our system.');
            } elseif ($change === 'downgraded') {
                session()->flash('account-downgrade', 'Your account has been changed to Respondent status. This could be because your institution is no longer in our system or your email domain changed.');
            } elseif ($change === 'institution-restored') {
                session()->flash('account-upgrade', 'Your institution has been restored in our system. All features are now available.');
            } elseif ($change === 'institution-changed') {
                session()->flash('account-upgrade', 'Your institution has been updated in our system based on your email domain.');
            } elseif ($change === 'institution-lost') {
                session()->flash('account-downgrade', 'Your institution is no longer recognized in our system. Some features will be limited.');
            }
            
            Session::forget('institution_status_change');
        }
        
        return redirect()->intended(route('feed.index'));
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
            // New users don't have 2FA yet, mark as verified
            Session::put('2fa:verified:' . $user->id, true);
            
            Auth::login($user);
            session()->regenerate();
            
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