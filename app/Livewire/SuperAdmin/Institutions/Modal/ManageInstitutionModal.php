<?php

namespace App\Livewire\SuperAdmin\Institutions\Modal;

use Livewire\Component;
use App\Models\Institution;

class ManageInstitutionModal extends Component
{
    public $institutionId;
    public $name = '';
    public $domain = '';
    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:institutions,name,{institutionId}',
        'domain' => 'required|string|max:255|unique:institutions,domain,{institutionId}',
    ];

    public function mount($institutionId)
    {
        $institution = Institution::findOrFail($institutionId);
        $this->institutionId = $institutionId;
        $this->name = $institution->name;
        $this->domain = $institution->domain;
    }

    public function updateInstitution()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:institutions,name,' . $this->institutionId,
            'domain' => 'required|string|max:255|unique:institutions,domain,' . $this->institutionId,
        ]);

        $institution = Institution::findOrFail($this->institutionId);
        $institution->update([
            'name' => $this->name,
            'domain' => $this->domain,
        ]);

        $this->showSuccess = true;
        $this->message = 'Institution updated successfully!';
        $this->dispatch('institution-updated');
        $this->dispatch('refresh-institution-index');
        $this->closeModal();
    }

    public function deleteInstitution()
    {
        $institution = Institution::findOrFail($this->institutionId);

        // Log out all users under this institution
        $userIds = $institution->users()->pluck('id')->toArray();
        if (!empty($userIds)) {
            // Invalidate all sessions for these users
            \DB::table('sessions')->whereIn('user_id', $userIds)->delete();
        }

        $institution->delete();

        $this->showSuccess = true;
        $this->message = 'Institution deleted successfully!';
        $this->dispatch('institution-deleted');
        $this->dispatch('refresh-institution-index');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'manage-institution-modal');
        $this->name = '';
        $this->domain = '';
    }

    public function render()
    {
        return view('livewire.super-admin.institutions.modal.manage-institution-modal');
    }
}
