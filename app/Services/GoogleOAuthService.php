<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
} 