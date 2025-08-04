<?php

namespace App\Livewire\SuperAdmin\Announcements;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Institution;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class AnnouncementsIndex extends Component
{
    use WithFileUploads, WithPagination;
    
    public $selectedAnnouncement = null;
    public $selectedAnnouncementId = null;
    public $manageModalKey = 0;
    public $showDeleteConfirmation = false;
    
    // Search and filter
    public $search = '';
    public $audienceFilter = 'all';
    
    // For Livewire v3
    public function getListeners()
    {
        return [
            'announcementCreated' => 'render',
            'announcementUpdated' => 'render',
            'announcement-updated' => 'handleAnnouncementUpdated',
            'announcement-deleted' => 'handleAnnouncementDeleted',
        ];
    }
    
    public function openCreateModal()
    {
        $this->selectedAnnouncement = null;
        // The actual modal is opened via Alpine.js in the blade template
    }
    
    public function openEditModal($announcementId)
    {
        $this->selectedAnnouncement = Announcement::find($announcementId);
        // The actual modal is opened via Alpine.js in the blade template
    }
    
    public function confirmDelete($announcementId)
    {
        $this->selectedAnnouncement = Announcement::find($announcementId);
        $this->showDeleteConfirmation = true;
    }
    
    public function deleteAnnouncement($announcementId)
    {
        $announcement = Announcement::find($announcementId);
        if ($announcement) {
            // Delete image from storage if it exists
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $announcement->delete();
            $this->selectedAnnouncement = null;
            // session()->flash('message', 'Announcement deleted successfully.');
            $this->dispatch('announcement-deleted');
        }
    }
    
    public function handleAnnouncementUpdated()
    {
        $this->manageModalKey++;
        $this->dispatch('announcement-updated-success');
    }
    
    public function handleAnnouncementDeleted()
    {
        $this->manageModalKey++;
        $this->dispatch('announcement-deleted-success');
    }
    
    public function render()
    {
        $user = auth()->user();
        $query = Announcement::query();
        
        // Apply search filter
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }
        
        // Apply audience filter
        if ($this->audienceFilter !== 'all') {
            $query->where('target_audience', $this->audienceFilter);
        }
        
        // Filter by user's permissions
        if (!$user->hasRole('super_admin')) {
            $query->where(function($q) use ($user) {
                $q->where('target_audience', 'public')
                  ->orWhere(function($innerQ) use ($user) {
                      $innerQ->where('target_audience', 'institution_specific')
                             ->where('institution_id', $user->institution_id);
                  });
            });
        }
        
        // Order by created_at date instead of order field
        $announcements = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('livewire.super-admin.announcements.announcements-index', [
            'announcements' => $announcements,
            'institutions' => Institution::all(),
        ]);
    }
}


