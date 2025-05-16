<?php

namespace App\Livewire\Feed\Modal;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ViewSurveyModal extends Component
{
    public $survey;

    public function mount($survey)
    {
        $this->survey = $survey;
        
        // Handle locked status for modal if not already set
        if (!isset($survey->is_demographic_locked) || !isset($survey->is_institution_locked)) {
            $user = Auth::user();
            $userInstitutionId = $user ? $user->institution_id : null;
            
            // Check for institution lock
            if ($this->survey->is_institution_only) {
                $surveyCreatorInstitutionId = $this->survey->user->institution_id;
                $this->survey->is_institution_locked = ($userInstitutionId !== $surveyCreatorInstitutionId);
            } else {
                $this->survey->is_institution_locked = false;
            }
            
            // For advanced surveys, check demographic locks
            if ($this->survey->type === 'advanced') {
                $userGeneralTags = $user ? $user->tags()->pluck('tags.id')->toArray() : [];
                $userInstitutionTags = $user ? $user->institutionTags()->pluck('institution_tags.id')->toArray() : [];
                
                $surveyTags = $this->survey->tags->pluck('id')->toArray();
                $surveyInstTags = $this->survey->institutionTags->pluck('id')->toArray();
                
                // If advanced survey has no tags, it's not locked
                if (empty($surveyTags) && empty($surveyInstTags)) {
                    $this->survey->is_demographic_locked = false;
                } else {
                    // Check for unmatched tags
                    $unmatchedGeneralTags = array_diff($surveyTags, $userGeneralTags);
                    $unmatchedInstTags = array_diff($surveyInstTags, $userInstitutionTags);
                    
                    $generalTagsMatch = empty($surveyTags) || empty($unmatchedGeneralTags);
                    $institutionTagsMatch = empty($surveyInstTags) || empty($unmatchedInstTags);
                    
                    $this->survey->is_demographic_locked = !($generalTagsMatch && $institutionTagsMatch);
                }
            } else {
                $this->survey->is_demographic_locked = false;
            }
        }
    }

    public function render()
    {
        return view('livewire.feed.modal.view-survey-modal');
    }
}
