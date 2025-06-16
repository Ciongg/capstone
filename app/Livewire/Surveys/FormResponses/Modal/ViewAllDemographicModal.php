<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use App\Models\Survey;
use App\Models\User;
use App\Models\Response;
use Livewire\Component;

class ViewAllDemographicModal extends Component
{
    public Survey $survey;
    public ?Response $response = null;
    public ?User $user = null;
    public array $demographicTags = [];

    public function mount(Survey $survey, $response = null, $user = null)
    {
        $this->survey = $survey;
        $this->response = $response;
        $this->user = $user;
        
        // Get demographic tags from snapshot if available
        if ($this->response && $this->response->snapshot) {
            $this->demographicTags = json_decode($this->response->snapshot->demographic_tags, true) ?? [];
        } 
        // Fallback to user's current tags if no snapshot available
        elseif ($this->user) {
            $this->user->loadMissing('tags');
            $this->demographicTags = $this->user->tags->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'category_id' => $tag->tag_category_id ?? null
                ];
            })->toArray();
        }
    }

    public function render()
    {
        // Eager load tags for survey to avoid N+1 queries
        $this->survey->loadMissing('tags');

        return view('livewire.surveys.form-responses.modal.view-all-demographic-modal');
    }
}
