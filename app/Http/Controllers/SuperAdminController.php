<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function rewardIndex()
    {
        return view('super-admin.show-reward-redemptions');
    }
}
