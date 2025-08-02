<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Surveys\FormBuilder\Templates\ISO25010Template;
use App\Livewire\Surveys\FormBuilder\Templates\AcademicResearchTemplate;

class SurveyTypeModal extends Component
{
    public $step = 'type'; // 'type', 'method', or 'template'
    public $surveyType; // 'basic' or 'advanced'
    public $creationMethod; // 'scratch' or 'template'
    public $selectedTemplate; // 'iso25010' or 'academic'

    public function selectSurveyType(string $type)
    {
        $this->surveyType = $type;
        $this->step = 'method';
    }

    public function selectCreationMethod(string $method)
    {
        $this->creationMethod = $method;
        
        // If template is selected, go to template selection step
        if ($method === 'template') {
            $this->step = 'template';
        }
    }

    public function selectTemplate(string $template)
    {
        $this->selectedTemplate = $template;
    }

    public function goBack()
    {
        $this->step = 'type';
        // Reset only the current step's selection, not previous steps
        $this->surveyType = null;
        $this->creationMethod = null;
        $this->selectedTemplate = null;
    }

    public function goBackToMethod()
    {
        $this->step = 'method';
        // Reset only the current step's selection, not previous steps
        $this->selectedTemplate = null;
    }

    public function proceedToCreateSurvey()
    {
        if (!$this->surveyType || !$this->creationMethod) {
            return;
        }

        // For template method, ensure a template is selected
        if ($this->creationMethod === 'template' && !$this->selectedTemplate) {
            return;
        }

        // Determine points based on survey type
        $pointsAllocated = ($this->surveyType === 'advanced') ? 20 : 10;

        // Set appropriate title based on template selection
        $surveyTitle = 'Untitled Survey';
        if ($this->creationMethod === 'template') {
            $surveyTitle = match($this->selectedTemplate) {
                'iso25010' => 'ISO 25010 Software Quality Evaluation',
                'academic' => 'Academic Research Survey',
                default => 'Untitled Survey'
            };
        }

        $surveyModel = Survey::create([
            'user_id' => Auth::id(),
            'title' => $surveyTitle,
            'description' => null,
            'status' => 'pending',
            'type' => $this->surveyType,
            'points_allocated' => $pointsAllocated,
        ]);

        if ($this->creationMethod === 'template') {
            // Use template classes to create structured surveys
            match($this->selectedTemplate) {
                'iso25010' => ISO25010Template::createTemplate($surveyModel),
                'academic' => AcademicResearchTemplate::createTemplate($surveyModel),
                default => $this->createDefaultSurvey($surveyModel)
            };
        } else {
            // Create default structure for "from scratch" option
            $this->createDefaultSurvey($surveyModel);
        }

        $this->dispatch('close-modal');
        return redirect()->route('surveys.create', ['survey' => $surveyModel->uuid]);
    }

    private function createDefaultSurvey(Survey $survey)
    {
        // Add a default page to the survey
        $page = SurveyPage::create([
            'survey_id' => $survey->id,
            'page_number' => 1,
        ]);

        // Add a default question to the page
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
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
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-type-modal');
    }
}
