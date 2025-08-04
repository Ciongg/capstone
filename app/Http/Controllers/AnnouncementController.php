<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        // This is just a controller to render the view with the Livewire component
        if (auth()->user()->hasRole('super_admin')) {
            return view('super-admin.show-announcements');
        } else {
            return view('institution-admin.show-announcements');
        }
    }

    // This method will be called by the Livewire component to get announcements for a user
    public function getAnnouncementsForUser($userId = null)
    {
        $user = $userId ? \App\Models\User::find($userId) : auth()->user();
        
        if (!$user) {
            return collect();
        }

        return Announcement::where('active', true)
            ->where(function($query) use ($user) {
                // Get public announcements
                $query->where('target_audience', 'public');
                
                // If user has an institution, also get institution-specific announcements
                if ($user->institution_id) {
                    $query->orWhere(function($q) use ($user) {
                        $q->where('target_audience', 'institution_specific')
                          ->where('institution_id', $user->institution_id);
                    });
                }
            })
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
