<?php

namespace App\Livewire\SuperAdmin\UserSurveys\Modal;

use App\Models\Survey;
use App\Models\InboxMessage;
use Livewire\Component;
use App\Services\AuditLogService;

class UserSurveyViewModal extends Component
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

        // If locking, update the lock reason and always send notification
        if ($this->survey->is_locked) {
            // Update lock reason if provided
            if (!empty($this->lockReason)) {
                $this->survey->lock_reason = $this->lockReason;
            }
            
            // Always send notification to survey owner when locking
            if ($this->survey->user_id) {
                $reasonText = !empty($this->lockReason) 
                    ? "Reason: {$this->lockReason}\n\n" 
                    : "No specific reason was provided.\n\n";
                    
                InboxMessage::create([
                    'recipient_id' => $this->survey->user_id,
                    'subject' => 'Your Survey Has Been Locked',
                    'message' => "Your survey '{$this->survey->title}' has been locked by an administrator.\n\n".
                                 $reasonText.
                                 "If you believe this lock was made in error, you can appeal this decision by submitting a support request through your Profile > Help Request section.\n\n".
                                 "Please include the Survey UUID: {$this->survey->uuid} in your appeal.",
                    'read_at' => null
                ]);
            }

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

        // If unlocking, clear lock reason
        if (!$this->survey->is_locked) {
            $this->survey->lock_reason = null;
            $this->lockReason = '';
            
            // Always send notification when unlocking
            if ($this->survey->user_id) {
                InboxMessage::create([
                    'recipient_id' => $this->survey->user_id,
                    'subject' => 'Your Survey Has Been Unlocked',
                    'message' => "Good news! Your survey '{$this->survey->title}' has been unlocked by an administrator and is now accessible again.",
                    'url' => "/surveys/create/{$this->survey->uuid}",
                    'read_at' => null
                ]);
            }

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
        return view('livewire.super-admin.user-surveys.modal.user-survey-view-modal');
    }
}
