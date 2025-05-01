<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;

class IndividualResponses extends Component
{
    public $survey;
    public $current = 0; // index of the current respondent

    public function mount($surveyId)
    {
        $this->survey = Survey::with('responses.answers', 'pages.questions.choices')->findOrFail($surveyId);
    }

    public function render()
    {
        return view('livewire.surveys.form-responses.individual-responses', [
            'survey' => $this->survey,
        ]);
    }
}
