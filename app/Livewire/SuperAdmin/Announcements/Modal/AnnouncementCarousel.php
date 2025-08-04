<?php

namespace App\Livewire\SuperAdmin\Announcements\Modal;

use Livewire\Component;
use App\Models\Announcement;

class AnnouncementCarousel extends Component
{
    public $announcements = [];
    
    public function mount()
    {
        $this->loadAnnouncements();
    }
    
    private function loadAnnouncements()
    {
        $user = auth()->user();
        
        $this->announcements = Announcement::where('active', true)
            ->where(function($query) use ($user) {
                // Get public announcements
                $query->where('target_audience', 'public');
                
                // If user has an institution, also get institution-specific announcements
                if ($user && $user->institution_id) {
                    $query->orWhere(function($q) use ($user) {
                        $q->where('target_audience', 'institution_specific')
                          ->where('institution_id', $user->institution_id);
                    });
                }
            })
            ->where(function($query) {
                $now = now();
                $query->where(function($q) use ($now) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
                })
                ->where(function($q) use ($now) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function render()
    {
        return view('livewire.super-admin.announcements.modal.announcement-carousel');
    }
}
    
    
