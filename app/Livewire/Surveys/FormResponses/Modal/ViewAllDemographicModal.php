<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use App\Models\Survey;
use App\Models\User;
use Livewire\Component;

class ViewAllDemographicModal extends Component
{
    public Survey $survey;
    public User $user;

    public function render()
    {
        // Eager load tags for both survey and user to avoid N+1 queries
        $this->survey->loadMissing('tags');
        $this->user->loadMissing('tags');

        return view('livewire.surveys.form-responses.modal.view-all-demographic-modal');
    }
}
