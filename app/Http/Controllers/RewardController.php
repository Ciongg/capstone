<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index(){
        return view('respondent.show-rewards');
    }

      public function vouchersIndex(){
        return view('respondent.show-vouchers');
    }
}
