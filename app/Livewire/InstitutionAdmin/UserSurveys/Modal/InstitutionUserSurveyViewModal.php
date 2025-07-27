<?php

namespace App\Livewire\InstitutionAdmin\UserSurveys\Modal;

use App\Models\Survey;
use Livewire\Component;

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
        return view('livewire.institution-admin.user-surveys.modal.institution-user-survey-view-modal');
    }
}
