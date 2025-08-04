<?php

namespace App\Livewire\SuperAdmin\Announcements\Modal;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Institution;
use Livewire\WithFileUploads;

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
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048', // Not required
        'targetAudience' => 'required|in:public,institution_specific',
        'institutionId' => 'nullable|required_if:targetAudience,institution_specific|exists:institutions,id',
        'active' => 'boolean',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ];
    
    public function save()
    {
        $user = auth()->user();

        // Set institutionId for non-super-admins if institution_specific
        if ($this->targetAudience === 'institution_specific' && !$user->hasRole('super_admin')) {
            $this->institutionId = $user->institution_id;
        }

        // Add debugging
        \Log::info('Create Announcement Debug', [
            'targetAudience' => $this->targetAudience,
            'institutionId' => $this->institutionId,
            'user_institution_id' => $user->institution_id,
            'is_super_admin' => $user->hasRole('super_admin')
        ]);

        $this->validate();

        // Ensure user has permission for the selected institution
        if ($this->targetAudience === 'institution_specific') {
            if (!$user->hasRole('super_admin') && $this->institutionId != $user->institution_id) {
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
        ];

        // Add debugging for create data
        \Log::info('Create Data', $createData);

        $announcement = Announcement::create($createData);

        // Log the created announcement
        \Log::info('Created Announcement', $announcement->toArray());

        session()->flash('message', 'Announcement created successfully.');
        $this->dispatch('announcementCreated');
        $this->dispatch('close-modal', ['name' => 'create-announcement-modal']);
    }
    
    public function render()
    {
        $user = auth()->user();
        $institutions = $user->hasRole('super_admin') 
            ? Institution::all() 
            : Institution::where('id', $user->institution_id)->get();
            
        return view('livewire.super-admin.announcements.modal.create-announcement-modal', [
            'institutions' => $institutions
        ]);
    }
}