<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleOAuthService
{
    public function getGoogleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback($request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            Log::info('Google user data', ['googleUser' => $googleUser]);
            // Check if user already exists by email
            $user = User::where('email', $googleUser->getEmail())->first();
            if ($user) {
                Log::info('Google login for existing user', ['email' => $googleUser->getEmail(), 'user_id' => $user->id]);
                return $user; // Return existing user for login
            }
            // Create new user
            DB::beginTransaction();
            $user = User::create([
                'first_name' => $googleUser->user['given_name'] ?? $googleUser->getName(),
                'last_name' => $googleUser->user['family_name'] ?? '',
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(str()->random(32)), // random password
                'email_verified_at' => now(),
                'is_active' => true,
                'type' => 'respondent', // Default user type for Google signups
                // Add any other required fields here
            ]);
            DB::commit();
            Log::info('Google user created', ['user_id' => $user->id]);
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google signup exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    public function getGoogleUser($request)
    {
        try {
            return Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function createUserFromGoogle($googleUserData)
    {
        try {
            DB::beginTransaction();
            
            // Determine user type based on email domain
            $emailDomain = Str::after($googleUserData['email'], '@');
            $institutionId = null;
            $userType = 'respondent'; // Default type
            
            // Check if email is from an educational institution (.edu domain)
            if (Str::endsWith($emailDomain, '.edu') || Str::endsWith($emailDomain, '.edu.ph')) {
                // Check if this institution exists in our database
                $institution = Institution::where('domain', $emailDomain)->first();
                
                if ($institution) {
                    $institutionId = $institution->id;
                    $userType = 'researcher'; // Educational email from recognized institution = researcher
                }
            }
            
            $user = User::create([
                'first_name' => $googleUserData['given_name'] ?? $googleUserData['name'],
                'last_name' => $googleUserData['family_name'] ?? '',
                'email' => $googleUserData['email'],
                'password' => bcrypt(str()->random(32)),
                'email_verified_at' => now(),
                'is_active' => true,
                'type' => $userType,
                'institution_id' => $institutionId,
                'is_accepted_terms' => true,
                'is_accepted_privacy_policy' => true,
            ]);
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google user creation error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}