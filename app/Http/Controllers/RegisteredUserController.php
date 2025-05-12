<?php

namespace App\Http\Controllers;

class RegisteredUserController extends Controller
{
 
    public function create()
    {
        return view('auth.register');
    }

}
