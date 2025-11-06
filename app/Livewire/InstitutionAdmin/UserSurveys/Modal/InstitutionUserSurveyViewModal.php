<?php

namespace App\Livewire\InstitutionAdmin\UserSurveys\Modal;

use App\Models\Survey;
use Livewire\Component;
use App\Services\AuditLogService;

class InstitutionUserSurveyViewModal extends Component
{
    public $survey = null;
    public $surveyId;
    public $lockReason = ''; // Add property to store lock reason
    
    public function mount($surveyId)
    {
        $this->surveyId = $surveyId;
        $this->loadSurvey();
    }
    
    public function loadSurvey()
    {
        if ($this->surveyId) {
            // Include trashed surveys so we can view archived surveys
            $this->survey = Survey::withTrashed()->with(['user', 'topic'])->find($this->surveyId);
        }
    }
    
    public function toggleLockStatus()
    {
        if (!$this->survey) {
            return;
        }
        
        // Capture before state for audit log
        $beforeData = [
            'is_locked' => $this->survey->is_locked,
            'lock_reason' => $this->survey->lock_reason,
        ];
        
        // Update lock status
        $this->survey->is_locked = !$this->survey->is_locked;
        
        // If locking, ensure lock reason is provided
        if ($this->survey->is_locked && !empty($this->lockReason)) {
            $this->survey->lock_reason = $this->lockReason;
        }

        // If unlocking, clear lock reason
        if (!$this->survey->is_locked) {
            $this->survey->lock_reason = null;
            $this->lockReason = '';

            // Capture after state for unlocking
            $afterData = [
                'is_locked' => false,
                'lock_reason' => null,
            ];

            // Audit log the survey unlock
            AuditLogService::logUpdate(
                resourceType: 'Survey',
                resourceId: $this->survey->id,
                before: $beforeData,
                after: $afterData,
                message: "Unlocked survey: '{$this->survey->title}' (UUID: {$this->survey->uuid})" .
                         (!empty($beforeData['lock_reason']) ? " - Previous reason: {$beforeData['lock_reason']}" : "")
            );
        } else {
            // Capture after state for locking
            $afterData = [
                'is_locked' => true,
                'lock_reason' => $this->lockReason,
            ];

            // Audit log the survey lock
            AuditLogService::logUpdate(
                resourceType: 'Survey',
                resourceId: $this->survey->id,
                before: $beforeData,
                after: $afterData,
                message: "Locked survey: '{$this->survey->title}' (UUID: {$this->survey->uuid})" . 
                         (!empty($this->lockReason) ? " - Reason: {$this->lockReason}" : "")
            );
        }

        $this->survey->save();
        
        $status = $this->survey->is_locked ? 'locked' : 'unlocked';
        session()->flash('modal_message', "Survey has been {$status} successfully.");
        
        // Notify the parent component that the status was updated
        $this->dispatch('surveyStatusUpdated');
    }
    
    public function archiveSurvey()
    {
        if (!$this->survey) {
            return;
        }

        // Capture before state for audit log (actual database fields)
        $beforeData = [
            'deleted_at' => null, // Not deleted yet
        ];

        // Capture metadata for the message
        $surveyMeta = [
            'title' => $this->survey->title,
            'uuid' => $this->survey->uuid,
            'status' => $this->survey->status,
            'owner_id' => $this->survey->user_id,
            'response_count' => $this->survey->responses()->count(),
        ];
        
        $this->survey->delete(); // Soft delete
        $this->survey->refresh(); // Refresh the model to get the updated deleted_at timestamp

        // Capture after state for audit log
        $afterData = [
            'deleted_at' => $this->survey->deleted_at,
        ];

        // Audit log the survey archiving
        AuditLogService::logUpdate(
            resourceType: 'Survey',
            resourceId: $this->survey->id,
            before: $beforeData,
            after: $afterData,
            message: "Archived survey: '{$surveyMeta['title']}' (UUID: {$surveyMeta['uuid']}) with {$surveyMeta['response_count']} response(s)"
        );
        
        session()->flash('modal_message', "Survey has been archived successfully.");
        $this->dispatch('surveyStatusUpdated');
    }
    
    public function restoreSurvey()
    {
        if (!$this->survey || !$this->survey->trashed()) {
            return;
        }

        // Capture before state for audit log
        $beforeData = [
            'deleted_at' => $this->survey->deleted_at,
        ];
        
        $this->survey->restore();
        $this->survey->refresh(); // Refresh the model to make sure deleted_at is null

        // Capture after state for audit log
        $afterData = [
            'deleted_at' => null,
        ];

        // Audit log the survey restoration
        AuditLogService::logUpdate(
            resourceType: 'Survey',
            resourceId: $this->survey->id,
            before: $beforeData,
            after: $afterData,
            message: "Restored survey: '{$this->survey->title}' (UUID: {$this->survey->uuid})"
        );
        
        session()->flash('modal_message', "Survey has been restored successfully.");
        $this->dispatch('surveyStatusUpdated');
    }
    
    public function render()
    {
        return view('livewire.institution-admin.user-surveys.modal.institution-user-survey-view-modal');
    }
}
