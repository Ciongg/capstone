<?php

namespace App\Livewire\SuperAdmin\Announcements\Modal;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Institution;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogService;

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
    public $url; // <-- Add this property
    public $imageMarkedForDeletion = false; // Add this property to track deletion status
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'targetAudience' => 'required|in:public,institution_specific',
        'institutionId' => 'nullable|required_if:targetAudience,institution_specific|exists:institutions,id',
        'active' => 'boolean',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'url' => 'nullable|url', // <-- Add validation
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
            $this->url = $announcement->url; // <-- Load url
        }
    }
    
    public function updateAnnouncement()
    {
        $user = auth()->user();

        // Set institutionId for non-super-admins if institution_specific
        if ($this->targetAudience === 'institution_specific' && $user->type !== 'super_admin') {
            $this->institutionId = $user->institution_id;
        }

        // Add debugging
        \Log::info('Update Announcement Debug', [
            'targetAudience' => $this->targetAudience,
            'institutionId' => $this->institutionId,
            'user_institution_id' => $user->institution_id,
            'is_super_admin' => $user->type === 'super_admin'
        ]);

        $this->validate();

        $announcement = Announcement::find($this->announcementId);

        if (!$announcement) {
            session()->flash('error', 'Announcement not found.');
            return;
        }

        // Capture before state for audit log
        $beforeData = [
            'title' => $announcement->title,
            'description' => $announcement->description,
            'target_audience' => $announcement->target_audience,
            'institution_id' => $announcement->institution_id,
            'active' => $announcement->active,
            'image_path' => $announcement->image_path, // Track actual image path
            'url' => $announcement->url,
            'start_date' => $announcement->start_date,
            'end_date' => $announcement->end_date,
        ];

        $imagePath = $this->currentImage;

        // Handle image deletion if marked for deletion
        if ($this->imageMarkedForDeletion && $this->currentImage) {
            Storage::disk('public')->delete($this->currentImage);
            $imagePath = null;
        }
        // Handle image upload if a new image is provided
        else if ($this->image) {
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
            'url' => $this->url,
        ];

        $announcement->update($updateData);

        // Capture after state for audit log
        $afterData = [
            'title' => $this->title,
            'description' => $this->description,
            'target_audience' => $this->targetAudience,
            'institution_id' => $this->institutionId,
            'active' => $this->active,
            'image_path' => $imagePath, // Track actual image path
            'url' => $this->url,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];

        // Audit log the announcement update
        AuditLogService::logUpdate(
            resourceType: 'Announcement',
            resourceId: $announcement->id,
            before: $beforeData,
            after: $afterData,
            message: "Updated announcement: '{$this->title}'"
        );

        // Reset the image marked for deletion flag
        $this->imageMarkedForDeletion = false;

        $this->dispatch('announcement-updated');
        $this->dispatch('close-modal', ['name' => 'manage-announcement-modal']);
    }
    
    public function deleteAnnouncement()
    {
        $announcement = Announcement::find($this->announcementId);
        
        if ($announcement) {
            // Capture data before deletion for audit log
            $announcementData = [
                'title' => $announcement->title,
                'target_audience' => $announcement->target_audience,
                'institution_id' => $announcement->institution_id,
                'active' => $announcement->active,
            ];

            // Delete image from storage if it exists
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }

            // Audit log the announcement deletion
            AuditLogService::logDelete(
                resourceType: 'Announcement',
                resourceId: $announcement->id,
                data: $announcementData,
                message: "Deleted announcement: '{$announcement->title}'"
            );

            $announcement->delete();
            $this->dispatch('announcement-deleted');
            $this->dispatch('close-modal', ['name' => 'manage-announcement-modal']);
        }
    }
    
    /**
     * Remove the uploaded image preview
     */
    public function removeImagePreview()
    {
        $this->image = null;
    }

    /**
     * Mark the current image for deletion without immediately deleting it
     */
    public function markImageForDeletion()
    {
        $this->imageMarkedForDeletion = true;
    }
    
    public function render()
    {
        $user = auth()->user();
        $institutions = $user->type === 'super_admin'
            ? Institution::all() 
            : Institution::where('id', $user->institution_id)->get();
            
        return view('livewire.super-admin.announcements.modal.manage-announcement-modal', [
            'institutions' => $institutions
        ]);
    }
}
