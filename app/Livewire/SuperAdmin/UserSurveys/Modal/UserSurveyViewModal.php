<?php

namespace App\Livewire\SuperAdmin\UserSurveys\Modal;

use App\Models\Survey;
use App\Models\InboxMessage;
use Livewire\Component;

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
        
        $wasPreviouslyLocked = $this->survey->is_locked;
        
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
        
        $this->survey->delete(); // Soft delete
        $this->survey->refresh(); // Refresh the model to get the updated deleted_at timestamp
        
        session()->flash('modal_message', "Survey has been archived successfully.");
        $this->dispatch('surveyStatusUpdated');
    }
    
    public function restoreSurvey()
    {
        if (!$this->survey || !$this->survey->trashed()) {
            return;
        }
        
        $this->survey->restore();
        $this->survey->refresh(); // Refresh the model to make sure deleted_at is null
        
        session()->flash('modal_message', "Survey has been restored successfully.");
        $this->dispatch('surveyStatusUpdated');
    }
    
    public function render()
    {
        return view('livewire.super-admin.user-surveys.modal.user-survey-view-modal');
    }
}
