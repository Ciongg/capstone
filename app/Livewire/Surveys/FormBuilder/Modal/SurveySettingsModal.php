<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use Livewire\Component;

class SurveySettingsModal extends Component
{
    public $survey;
    public $target_respondents;
    public $start_date;
    public $end_date;
    public $points_allocated;

    public function mount($survey)
    {
        $this->survey = $survey;
        $this->target_respondents = $survey->target_respondents;
        $this->start_date = $survey->start_date;
        $this->end_date = $survey->end_date;
        $this->points_allocated = $survey->points_allocated;
    }

    public function updated($property)
    {
        $this->survey->{$property} = $this->{$property};
        $this->survey->save();
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-settings-modal');
    }
}
