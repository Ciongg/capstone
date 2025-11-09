<?php

namespace App\Http\Controllers;

use App\Services\GoogleOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        try {
            $googleUser = $this->googleService->getGoogleUser($request);
            
            if (!$googleUser) {
                return redirect()->route('login')->with('error', 'Failed to authenticate with Google.');
            }

            // Check if user exists
            $existingUser = $this->googleService->findUserByEmail($googleUser->getEmail());
            
            if (!$existingUser) {
                // New user - redirect to consent page
                return view('auth.google-consent', [
                    'googleUser' => $googleUser,
                    'isNewUser' => true
                ]);
            }

            // Existing user - proceed with login
            $result = $this->googleService->handleGoogleCallback($request);
            
            if (!$result || !isset($result['user'])) {
                return redirect()->route('login')->with('error', 'Failed to authenticate with Google.');
            }

            // Check if 2FA is required
            if ($result['needs_2fa']) {
                return redirect()->route('two-factor.challenge');
            }

            // No 2FA needed, log in directly
            Auth::login($result['user']);
            
            return redirect()->intended(route('feed.index'));
            
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')->with('error', 'An error occurred during Google authentication.');
        }
    }

    // Handle consent form submission to create account
    public function consent(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'name' => 'required|string',
                'given_name' => 'nullable|string',
                'family_name' => 'nullable|string',
            ]);

            // Check if user already exists
            $existingUser = $this->googleService->findUserByEmail($request->email);
            
            if ($existingUser) {
                // User exists, just log them in
                $result = $this->googleService->handleGoogleCallback($request);
                
                if (!$result || !isset($result['user'])) {
                    return redirect()->route('login')->with('error', 'Failed to authenticate with Google.');
                }

                // Check if 2FA is required
                if ($result['needs_2fa']) {
                    return redirect()->route('two-factor.challenge');
                }

                Auth::login($result['user']);
                return redirect()->intended(route('feed.index'));
            }

            // Create new user
            $userData = [
                'email' => $request->email,
                'name' => $request->name,
                'given_name' => $request->given_name,
                'family_name' => $request->family_name,
            ];

            $user = $this->googleService->createUserFromGoogle($userData);
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Failed to create account.');
            }

            // Log in the new user (new users don't have 2FA)
            Auth::login($user);
            
            return redirect()->route('feed.index')->with('success', 'Account created successfully!');
            
        } catch (\Exception $e) {
            Log::error('Google consent error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')->with('error', 'An error occurred during account creation.');
        }
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