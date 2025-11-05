<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user has 2FA enabled and confirmed
        if ($user->hasTwoFactorEnabled()) {
            // Check if 2FA has been verified in this session
            if (!Session::has('2fa:verified:' . $user->id)) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('message', 'Please complete two-factor authentication.');
            }
        }

        return $next($request);
    }
}
