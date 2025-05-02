<?php

namespace App\Livewire\Feed\Modal;

use Livewire\Component;

class ViewSurveyModal extends Component
{
    public $survey;

    public function mount($survey)
    {
        $this->survey = $survey;
    }

    public function render()
    {
        return view('livewire.feed.modal.view-survey-modal');
    }
}
