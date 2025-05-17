<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstitutionAdminController extends Controller
{
    public function analyticsIndex()
    {
        return view('institution-admin.show-institution-analytics');
    }
}
