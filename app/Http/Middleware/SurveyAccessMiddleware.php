<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the survey from the route
        $survey = $request->route('survey');
        
        // If no survey in route or survey doesn't exist, proceed (will 404 later)
        if (!$survey) {
            return $next($request);
        }
        
        // If user is authenticated, allow access
        if (auth()->check()) {
            return $next($request);
        }
        
        // If user is a guest, check if survey allows guest responses
        if ($survey->is_guest_allowed) {
            // Set a flag in session that this is a guest accessing a survey
            session()->flash('guest_survey_access', true);
            return $next($request);
        }
        
        // Survey requires authentication, redirect to login
        return redirect()->route('login')->with('warning_message', 
            'You must be logged in to answer this survey as the survey owner does not allow guest responses.');
    }
}
