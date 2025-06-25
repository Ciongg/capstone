<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;

class FormResponses extends Component
{
    public $survey;

    public function mount($surveyId)
    {
        $this->survey = Survey::with('pages.questions.answers')->findOrFail($surveyId);
    }

    public function clearAllAISummaries()
    {
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions as $question) {
                $question->ai_summary = null;
                $question->save();
            }
        }

    }

    public function render()
    {
        return view('livewire.surveys.form-responses.form-responses', [
            'survey' => $this->survey,
        ]);
    }
}
