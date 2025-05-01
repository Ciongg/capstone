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

    public function deleteAllResponses()
    {
        if ($this->survey) {
            $this->survey->responses()->each(function ($response) {
                $response->answers()->delete();
                $response->delete();
            });
            // Reload survey with fresh relations
            $this->survey = Survey::with('pages.questions.answers')->find($this->survey->id);
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-responses.form-responses', [
            'survey' => $this->survey,
        ]);
    }
}
