<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Support\Facades\Auth;

class SurveyTypeModal extends Component
{
    public $step = 'type'; // 'type' or 'method'
    public $surveyType; // 'basic' or 'advanced'
    public $creationMethod; // 'scratch' or 'template'

    public function selectSurveyType(string $type)
    {
        $this->surveyType = $type;
        $this->step = 'method';
    }

    public function selectCreationMethod(string $method)
    {
        $this->creationMethod = $method;
    }

    public function goBack()
    {
        $this->step = 'type';
        $this->creationMethod = null; // Reset creation method when going back
    }

    public function proceedToCreateSurvey()
    {
        if (!$this->surveyType || !$this->creationMethod) {
            // Optionally, add some error handling/feedback here
            return;
        }

        // Determine points based on survey type
        $pointsAllocated = ($this->surveyType === 'advanced') ? 20 : 10;

        $surveyModel = Survey::create([
            'user_id' => Auth::id(),
            'title' => 'Untitled Survey',
            'description' => null,
            'status' => 'pending',
            'type' => $this->surveyType,
            'points_allocated' => $pointsAllocated,
        ]);

        // Add a default page to the survey
        $page = SurveyPage::create([
            'survey_id' => $surveyModel->id,
            'page_number' => 1,
        ]);

        // Add a default question to the page
        $question = SurveyQuestion::create([
            'survey_id' => $surveyModel->id,
            'survey_page_id' => $page->id,
            'question_text' => 'Enter Question Title',
            'question_type' => 'multiple_choice',
            'order' => 1,
            'required' => false,
        ]);

        // Add default choices to the question
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1,
        ]);

        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 2',
            'order' => 2,
        ]);
        
        // TODO: If $this->creationMethod is 'template', load template structure here

        $this->dispatch('close-modal'); // Dispatch event for Alpine modal to close

        // It's important that the redirect happens after the dispatch if the modal needs to visually close first.
        // However, Livewire redirects are full page reloads, so the modal will be gone anyway.
        return redirect()->route('surveys.create', ['survey' => $surveyModel->id]);
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-type-modal');
    }
}
