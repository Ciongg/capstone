<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function destroy(Request $request){
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
            session()->flash('warning_message', 'You must be logged in to answer surveys. If you already have an account, please login below. If not, please register a new account.');
        }
        
        return view('auth.login');
    }


}
