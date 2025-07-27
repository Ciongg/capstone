<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstitutionAdminController extends Controller
{
    public function analyticsIndex()
    {
        return view('institution-admin.show-institution-analytics');
    }
    
    public function usersIndex()
    {
        // Use the same view as super admin
        return view('super-admin.users-index');
    }

    public function surveysIndex()
    {
        // Render the Livewire component for institution user surveys
        return view('institution-admin.show-institution-surveys');
    }
}
