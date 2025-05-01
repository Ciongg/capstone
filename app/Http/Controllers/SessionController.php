<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

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

    public function destroy(){
        Auth::logout();

        return redirect('/');
    }

    public function showLogin(){
        return view('login');
    }


}
