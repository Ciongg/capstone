<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleOAuthService;
use Illuminate\Support\Facades\Auth;

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
        $user = $this->googleService->handleGoogleCallback($request);
        if ($user) {
            Auth::login($user);
            // Check if user was just created (has recent created_at timestamp)
            $isNewUser = $user->created_at->diffInMinutes(now()) < 1;
            $message = $isNewUser ? 'Registration successful!' : 'Login successful!';
            return redirect()->route('feed.index')->with('success', $message);
        } else {
            return redirect()->route('register')->with('error', 'Google authentication failed.');
        }
    }
} 