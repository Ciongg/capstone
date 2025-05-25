<?php

namespace App\Livewire\Feed\Modal;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Survey;

class ViewSurveyModal extends Component
{
    
    public $survey;
    public $surveyId;
    
   public function mount($surveyId)
    {
        $this->surveyId = $surveyId;
        $this->survey = Survey::findOrFail($surveyId);

        // Skip if already locked
        if (isset($this->survey->is_demographic_locked) && isset($this->survey->is_institution_locked)) {
            return;
        }

        $user = Auth::user();
        $userInstitutionId = $user?->institution_id;

        // Institution Lock
        if ($this->survey->is_institution_only) {
            $creatorInstitutionId = $this->survey->user->institution_id;
            $this->survey->is_institution_locked = ($userInstitutionId !== $creatorInstitutionId);
        } else {
            $this->survey->is_institution_locked = false;
        }

        // Demographic Lock (only for advanced surveys)
        if ($this->survey->type === 'advanced') {
            $userGeneralTags = $user?->tags()->pluck('tags.id')->toArray() ?? [];
            $userInstitutionTags = $user?->institutionTags()->pluck('institution_tags.id')->toArray() ?? [];

            $surveyGeneralTags = $this->survey->tags->pluck('id')->toArray();
            $surveyInstitutionTags = $this->survey->institutionTags->pluck('id')->toArray();

            $requiresNoTags = empty($surveyGeneralTags) && empty($surveyInstitutionTags);

            if ($requiresNoTags) {
                $this->survey->is_demographic_locked = false;
            } else {
                $missingGeneralTags = array_diff($surveyGeneralTags, $userGeneralTags);
                $missingInstitutionTags = array_diff($surveyInstitutionTags, $userInstitutionTags);

                $hasGeneralTagMatch = empty($surveyGeneralTags) || empty($missingGeneralTags);
                $hasInstitutionTagMatch = empty($surveyInstitutionTags) || empty($missingInstitutionTags);

                $this->survey->is_demographic_locked = !($hasGeneralTagMatch && $hasInstitutionTagMatch);
            }
        } else {
            $this->survey->is_demographic_locked = false;
        }
    }


    public function render()
    {
        return view('livewire.feed.modal.view-survey-modal');
    }
}
