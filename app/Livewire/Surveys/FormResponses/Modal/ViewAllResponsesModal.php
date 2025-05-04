<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use App\Models\SurveyQuestion; // Make sure to import the model
use Livewire\Component;

class ViewAllResponsesModal extends Component
{
    public SurveyQuestion $question; // Declare the public property and type hint it

    public function render()
    {
        // Correct the relationship path for eager loading
        $this->question->loadMissing('answers.response.user'); 

        return view('livewire.surveys.form-responses.modal.view-all-responses-modal');
    }
}
