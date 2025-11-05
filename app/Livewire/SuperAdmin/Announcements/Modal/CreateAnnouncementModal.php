<?php

namespace App\Livewire\SuperAdmin\Announcements\Modal;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Institution;
use Livewire\WithFileUploads;
use App\Services\AuditLogService;

class CreateAnnouncementModal extends Component
{
    use WithFileUploads;
    
    public $title;
    public $description;
    public $image;
    public $targetAudience = 'public';
    public $institutionId;
    public $active = true;
    public $start_date;
    public $end_date;
    public $url; // Add URL property
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048', // Not required
        'targetAudience' => 'required|in:public,institution_specific',
        'institutionId' => 'nullable|required_if:targetAudience,institution_specific|exists:institutions,id',
        'active' => 'boolean',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'url' => 'nullable|url', // Add URL validation
    ];
    
    public function save()
    {
        $user = auth()->user();

        // Set institutionId for non-super-admins if institution_specific
        if ($this->targetAudience === 'institution_specific' && !$user->isSuperAdmin()) {
            $this->institutionId = $user->institution_id;
        }

        // Add debugging
        \Log::info('Create Announcement Debug', [
            'targetAudience' => $this->targetAudience,
            'institutionId' => $this->institutionId,
            'user_institution_id' => $user->institution_id,
            'is_super_admin' => $user->isSuperAdmin()
        ]);

        $this->validate();

        // Ensure user has permission for the selected institution
        if ($this->targetAudience === 'institution_specific') {
            if (!$user->isSuperAdmin() && $this->institutionId != $user->institution_id) {
                session()->flash('error', 'You can only create announcements for your own institution.');
                return;
            }
        }

        // Store the uploaded image if present
        $imagePath = $this->image ? $this->image->store('announcements', 'public') : null;

        // Create new announcement
        $createData = [
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

        // Add debugging for create data
        \Log::info('Create Data', $createData);

        $announcement = Announcement::create($createData);

        // Log the created announcement
        \Log::info('Created Announcement', $announcement->toArray());

        // Audit log the announcement creation
        AuditLogService::logCreate(
            resourceType: 'Announcement',
            resourceId: $announcement->id,
            data: [
                'title' => $announcement->title,
                'target_audience' => $announcement->target_audience,
                'institution_id' => $announcement->institution_id,
                'active' => $announcement->active,
                'image_path' => $announcement->image_path, // Track actual image path
                'url' => $announcement->url,
                'start_date' => $announcement->start_date,
                'end_date' => $announcement->end_date,
            ],
            message: "Created announcement: '{$announcement->title}' for " . 
                     ($announcement->target_audience === 'public' ? 'public audience' : 
                      'institution ID: ' . $announcement->institution_id)
        );

        session()->flash('message', 'Announcement created successfully.');
        $this->dispatch('announcementCreated');
        $this->dispatch('close-modal', ['name' => 'create-announcement-modal']);
    }
    
    /**
     * Remove the uploaded image preview
     */
    public function removeImagePreview()
    {
        $this->image = null;
    }
    
    public function render()
    {
        $user = auth()->user();
        $institutions = $user->isSuperAdmin() 
            ? Institution::all() 
            : Institution::where('id', $user->institution_id)->get();
            
        return view('livewire.super-admin.announcements.modal.create-announcement-modal', [
            'institutions' => $institutions
        ]);
    }
}