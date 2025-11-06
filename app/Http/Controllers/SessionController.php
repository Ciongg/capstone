<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Survey;
use App\Models\SecurityLogs;
use Illuminate\Support\Str;
use App\Services\TestTimeService;

class SessionController extends Controller
{
    public function store(){
        $validated = request()->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if(!Auth::attempt($validated)){
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        request()->session()->regenerate();
        
       
        return redirect()->route('feed.index')->with('success', 'Login successful');

    }

    /**
     * Get geolocation data from IP address
     */
    private function getGeoLocation(string $ipAddress): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get("http://ip-api.com/json/{$ipAddress}");
            
            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                return [
                    'country' => $data['country'] ?? '',
                    'region' => $data['regionName'] ?? '',
                    'city' => $data['city'] ?? '',
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GeoLocation API Error', [
                'message' => $e->getMessage(),
                'ip' => $ipAddress
            ]);
        }
        
        return [];
    }

    /**
     * Log admin logout to security logs
     */
    private function logAdminLogout(Request $request, $user): void
    {
        // Only log for institution admins and super admins
        if (!in_array($user->type, ['institution_admin', 'super_admin'])) {
            return;
        }

        try {
            $currentIp = $request->ip();
            $geoData = $this->getGeoLocation($currentIp);
            
            SecurityLogs::create([
                'uuid' => Str::uuid(),
                'created_at' => TestTimeService::now(),
                'email' => $user->email,
                'event_type' => 'logout',
                'outcome' => 'success',
                'user_id' => $user->id,
                'actor_role' => $user->type,
                'ip' => $currentIp,
                'user_agent' => $request->userAgent(),
                'route' => $request->path(),
                'http_method' => $request->method(),
                'http_status' => 200,
                'resource_type' => 'User',
                'resource_id' => $user->id,
                'message' => ucfirst(str_replace('_', ' ', $user->type)) . " logged out: {$user->email}",
                'meta' => [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                ],
                'geo' => $geoData,
            ]);
            
            \Illuminate\Support\Facades\Log::info('Security log created for admin logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $user->type,
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create security log for admin logout', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        // Get user before logout
        $user = Auth::user();
        
        // Log admin logout (only for admins)
        if ($user) {
            $this->logAdminLogout($request, $user);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'You have been logged out.');
    }

    public function create()
    {
        // Check if user was trying to access a survey answer page
        $intendedUrl = session()->get('url.intended');
        
        if ($intendedUrl && str_contains($intendedUrl, '/surveys/answer/')) {
            session()->flash('warning_message', 'You need to login to continue.');
        }
        
        return view('auth.login');
    }


}
