<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\TestTimeService;
use Carbon\Carbon;

class UpdateLastActiveAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentTime = Carbon::parse(TestTimeService::now());
            $lastActive = $user->last_active_at; // Use directly, already Carbon

            // Log::info('UpdateLastActiveAt: authenticated', [
            //     'user_id' => $user->id,
            //     'email' => $user->email,
            // ]);

            $diff = $lastActive ? abs($currentTime->diffInSeconds($lastActive)) : null;

            if (!$lastActive || $diff >= 10) {
                $user->forceFill(['last_active_at' => $currentTime])->save();
        
            } 
        }

        return $next($request);
    }
}
