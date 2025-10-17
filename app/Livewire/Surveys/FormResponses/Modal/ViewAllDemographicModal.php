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
    public array $surveyTags = [];
    
    public function mount(Survey $survey, $response = null, $user = null)
    {
        $this->survey = $survey;
        $this->response = $response;
        $this->user = $user;
        
        // Load the survey snapshot if it exists
        $this->survey->loadMissing('snapshot');
        
        // Get survey demographic tags from snapshot if available, otherwise from the current survey
        if ($this->survey->snapshot && !empty($this->survey->snapshot->demographic_tags)) {
            // Use the preserved snapshot data
            $this->surveyTags = $this->survey->snapshot->demographic_tags;
        } else {
            // Fallback to current survey tags
            $this->survey->loadMissing('tags');
            $this->surveyTags = $this->survey->tags->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'category_id' => $tag->tag_category_id ?? null,
                    'category_name' => $tag->category ? $tag->category->name : null
                ];
            })->toArray();
        }
        
        // Get demographic tags from response snapshot if available
        if ($this->response && $this->response->snapshot) {
            $this->demographicTags = json_decode($this->response->snapshot->demographic_tags, true) ?? [];
        } 
        // Fallback to user's current tags if no snapshot available
        elseif ($this->user) {
            $this->user->loadMissing('tags.category');
            $this->demographicTags = $this->user->tags->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'category_id' => $tag->tag_category_id ?? null,
                    'category_name' => $tag->category ? $tag->category->name : null
                ];
            })->toArray();
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-responses.modal.view-all-demographic-modal');
    }
}
