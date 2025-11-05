<?php

namespace App\Livewire\SuperAdmin\Institutions\Modal;

use Livewire\Component;
use App\Models\Institution;
use App\Services\AuditLogService;

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
        
        // Capture before state for audit log
        $beforeData = [
            'name' => $institution->name,
            'domain' => $institution->domain,
        ];

        // Check if domain is changing
        $isDomainChanging = $institution->domain !== $this->domain;
        $affectedUserIds = [];

        if ($isDomainChanging) {
            // Get all users under this institution before the change
            $affectedUserIds = $institution->users()->pluck('id')->toArray();
        }

        $institution->update([
            'name' => $this->name,
            'domain' => $this->domain,
        ]);

        // If domain changed, log out all affected users
        if ($isDomainChanging && !empty($affectedUserIds)) {
            \DB::table('sessions')->whereIn('user_id', $affectedUserIds)->delete();
        }

        // Capture after state for audit log
        $afterData = [
            'name' => $this->name,
            'domain' => $this->domain,
        ];

        // Build audit log message
        $message = "Updated institution: '{$this->name}'";
        if ($isDomainChanging) {
            $message .= " (domain changed from '{$beforeData['domain']}' to '{$this->domain}' and logged out " . count($affectedUserIds) . " user(s))";
        }

        // Audit log the institution update
        AuditLogService::logUpdate(
            resourceType: 'Institution',
            resourceId: $institution->id,
            before: $beforeData,
            after: $afterData,
            message: $message
        );

        $this->showSuccess = true;
        $this->message = 'Institution updated successfully!';
        $this->dispatch('institution-updated');
        $this->dispatch('refresh-institution-index');
        $this->closeModal();
    }

    public function deleteInstitution()
    {
        $institution = Institution::findOrFail($this->institutionId);

        // Capture data before deletion for audit log
        $institutionData = [
            'name' => $institution->name,
            'domain' => $institution->domain,
            'user_count' => $institution->users()->count(),
        ];

        // Log out all users under this institution
        $userIds = $institution->users()->pluck('id')->toArray();
        if (!empty($userIds)) {
            // Invalidate all sessions for these users
            \DB::table('sessions')->whereIn('user_id', $userIds)->delete();
        }

        // Audit log the institution deletion
        AuditLogService::logDelete(
            resourceType: 'Institution',
            resourceId: $institution->id,
            data: $institutionData,
            message: "Deleted institution: '{$institution->name}' with domain '{$institution->domain}' and logged out {$institutionData['user_count']} user(s)"
        );

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
