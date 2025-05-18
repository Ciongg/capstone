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

        // Skip if already locked
        if (isset($survey->is_demographic_locked) && isset($survey->is_institution_locked)) {
            return;
        }

        $user = Auth::user();
        $userInstitutionId = $user?->institution_id;

        // 🔒 Institution Lock
        if ($survey->is_institution_only) {
            $creatorInstitutionId = $survey->user->institution_id;
            $survey->is_institution_locked = ($userInstitutionId !== $creatorInstitutionId);
        } else {
            $survey->is_institution_locked = false;
        }

        // 🔒 Demographic Lock (only for advanced surveys)
        if ($survey->type === 'advanced') {
            $userGeneralTags = $user?->tags()->pluck('tags.id')->toArray() ?? [];
            $userInstitutionTags = $user?->institutionTags()->pluck('institution_tags.id')->toArray() ?? [];

            $surveyGeneralTags = $survey->tags->pluck('id')->toArray();
            $surveyInstitutionTags = $survey->institutionTags->pluck('id')->toArray();

            $requiresNoTags = empty($surveyGeneralTags) && empty($surveyInstitutionTags);

            if ($requiresNoTags) {
                $survey->is_demographic_locked = false;
            } else {
                $missingGeneralTags = array_diff($surveyGeneralTags, $userGeneralTags);
                //health, education, finance surveytags
                //health, education, technology usertags
                //return the values from the first array taht are not in the second arry
                // returns finance
                $missingInstitutionTags = array_diff($surveyInstitutionTags, $userInstitutionTags);

                $hasGeneralTagMatch = empty($surveyGeneralTags) || empty($missingGeneralTags);
                $hasInstitutionTagMatch = empty($surveyInstitutionTags) || empty($missingInstitutionTags);

                $survey->is_demographic_locked = !($hasGeneralTagMatch && $hasInstitutionTagMatch);
            }
        } else {
            $survey->is_demographic_locked = false;
        }
    }

    public function render()
    {
        return view('livewire.feed.modal.view-survey-modal');
    }
}
