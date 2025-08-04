<?php

namespace App\Livewire\SuperAdmin\Announcements\Modal;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Institution;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ManageAnnouncementModal extends Component
{
    use WithFileUploads;
    
    public $announcementId;
    public $title;
    public $description;
    public $image;
    public $currentImage;
    public $targetAudience;
    public $institutionId;
    public $active;
    public $start_date;
    public $end_date;
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'targetAudience' => 'required|in:public,institution_specific',
        'institutionId' => 'nullable|required_if:targetAudience,institution_specific|exists:institutions,id',
        'active' => 'boolean',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ];
    
    public function mount($announcementId)
    {
        $this->announcementId = $announcementId;
        $this->loadAnnouncementData();
    }
    
    private function loadAnnouncementData()
    {
        $announcement = Announcement::find($this->announcementId);
        
        if ($announcement) {
            $this->title = $announcement->title;
            $this->description = $announcement->description;
            $this->currentImage = $announcement->image_path;
            $this->targetAudience = $announcement->target_audience;
            $this->institutionId = $announcement->institution_id;
            $this->active = $announcement->active;
            $this->start_date = $announcement->start_date ? $announcement->start_date->format('Y-m-d\TH:i') : null;
            $this->end_date = $announcement->end_date ? $announcement->end_date->format('Y-m-d\TH:i') : null;
        }
    }
    
    public function updateAnnouncement()
    {
        $user = auth()->user();

        // Set institutionId for non-super-admins if institution_specific
        if ($this->targetAudience === 'institution_specific' && !$user->hasRole('super_admin')) {
            $this->institutionId = $user->institution_id;
        }

        // Add debugging
        \Log::info('Update Announcement Debug', [
            'targetAudience' => $this->targetAudience,
            'institutionId' => $this->institutionId,
            'user_institution_id' => $user->institution_id,
            'is_super_admin' => $user->hasRole('super_admin')
        ]);

        $this->validate();

        $announcement = Announcement::find($this->announcementId);

        if (!$announcement) {
            session()->flash('error', 'Announcement not found.');
            return;
        }

        $imagePath = $this->currentImage;

        // Handle image upload if a new image is provided
        if ($this->image) {
            // Delete old image if exists
            if ($this->currentImage) {
                Storage::disk('public')->delete($this->currentImage);
            }

            $imagePath = $this->image->store('announcements', 'public');
        }

        $updateData = [
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $imagePath,
            'target_audience' => $this->targetAudience,
            'institution_id' => $this->targetAudience === 'institution_specific' ? $this->institutionId : null,
            'active' => $this->active,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];

        // Add debugging for update data
        \Log::info('Update Data', $updateData);

        $announcement->update($updateData);

        $this->dispatch('announcement-updated');
        $this->dispatch('close-modal', ['name' => 'manage-announcement-modal']);
    }
    
    public function deleteAnnouncement()
    {
        $announcement = Announcement::find($this->announcementId);
        
        if ($announcement) {
            // Delete image from storage if it exists
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $announcement->delete();
            $this->dispatch('announcement-deleted');
            $this->dispatch('close-modal', ['name' => 'manage-announcement-modal']);
        }
    }
    
    public function render()
    {
        $user = auth()->user();
        $institutions = $user->hasRole('super_admin') 
            ? Institution::all() 
            : Institution::where('id', $user->institution_id)->get();
            
        return view('livewire.super-admin.announcements.modal.manage-announcement-modal', [
            'institutions' => $institutions
        ]);
    }
}
