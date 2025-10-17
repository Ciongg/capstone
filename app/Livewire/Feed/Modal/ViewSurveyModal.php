<?php

namespace App\Livewire\Feed\Modal;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Survey;
use App\Services\TestTimeService;

class ViewSurveyModal extends Component
{
    
    public $survey;
    public $surveyId;
    
   public function mount($surveyId)
    {
        $this->surveyId = $surveyId;
        $this->survey = Survey::with(['responses'])->findOrFail($surveyId);

        // Skip if already locked
        if (isset($this->survey->is_demographic_locked) && isset($this->survey->is_institution_locked) && isset($this->survey->is_expired_locked) && isset($this->survey->is_response_limit_locked) && isset($this->survey->is_not_started_locked) && isset($this->survey->is_trust_score_locked)) {
            return;
        }

        $user = Auth::user();
        $userInstitutionId = $user?->institution_id;
        
        $now = TestTimeService::now();

        // Trust Score Lock - Check if user's trust score is too low (<=40)
        if ($user && $user->trust_score <= 40) {
            $this->survey->is_trust_score_locked = true;
        } else {
            $this->survey->is_trust_score_locked = false;
        }

        // Not Started Lock - Check if start date is in the future
        if ($this->survey->start_date && $this->survey->start_date > $now) {
            $this->survey->is_not_started_locked = true;
        } else {
            $this->survey->is_not_started_locked = false;
        }

        // Expiration Lock - Use TestTimeService consistently
        if ($this->survey->end_date && $this->survey->end_date < $now) {
            $this->survey->is_expired_locked = true;
        } else {
            $this->survey->is_expired_locked = false;
        }

        // Response Limit Lock
        if ($this->survey->target_respondents) {
            $currentResponseCount = $this->survey->responses()->count();
            $this->survey->is_response_limit_locked = ($currentResponseCount >= $this->survey->target_respondents);
        } else {
            $this->survey->is_response_limit_locked = false;
        }

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

    public function showDataPrivacyNotice()
    {
        $this->dispatch('show-data-privacy-notice', [
            'redirectUrl' => route('surveys.answer', $this->survey->uuid)
        ]);
    }

    public function render()
    {
        return view('livewire.feed.modal.view-survey-modal');
    }
}
