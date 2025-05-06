<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use App\Livewire\Surveys\FormBuilder\FormBuilder; // Import the FormBuilder class
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TagCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;

class SurveySettingsModal extends Component
{
    use WithFileUploads;

    public $survey;
    public $target_respondents;
    public $start_date;
    public $end_date;
    public $points_allocated;
    public $banner_image; // This will hold the temporary uploaded file object
    public $tagCategories;
    public $selectedSurveyTags = []; // Make sure this is set
    public $title;
    public $description;
    public $type; // Add property for survey type

    // Add the listener property
    protected $listeners = ['surveyTitleUpdated' => 'updateTitleFromEvent'];

    public function mount($survey)
    {
        $this->survey = $survey;
        $this->title = $survey->title;
        $this->description = $survey->description;
        $this->type = $survey->type;
        $this->target_respondents = $survey->target_respondents;
        $this->start_date = $survey->start_date;
        $this->end_date = $survey->end_date;
        
        // Set points based on survey type
        $this->points_allocated = $this->getPointsForType($this->type);

        $this->tagCategories = TagCategory::with('tags')->get();

        $this->selectedSurveyTags = []; // Always initialize as array

        // Load current survey tags
        if ($this->survey) {
            foreach ($this->tagCategories as $category) {
                $tag = $this->survey->tags()->where('tag_category_id', $category->id)->first();
                $this->selectedSurveyTags[$category->id] = $tag ? $tag->id : '';
            }
        }
    }

    // Method to handle the event
    public function updateTitleFromEvent($title)
    {
        $this->title = $title;
    }

    // Get points based on survey type
    public function getPointsForType($type)
    {
        return $type === 'advanced' ? 30 : 10;
    }

    // When type is updated, update points automatically
    public function updatedType($value)
    {
        $this->points_allocated = $this->getPointsForType($value);
    }

    public function saveSurveyInformation()
    {
        // You might want to add validation here
        // $this->validate([...]);

        if ($this->survey) {
            // Handle banner image saving here
            if ($this->banner_image) {
                if ($this->survey->image_path) {
                    Storage::disk('public')->delete($this->survey->image_path);
                }
                $path = $this->banner_image->store('surveys', 'public');
                $this->survey->image_path = $path;
            }

            // Save other fields
            $this->survey->title = $this->title;
            $this->survey->description = $this->description;
            $this->survey->type = $this->type;
            $this->survey->target_respondents = $this->target_respondents;
            $this->survey->start_date = $this->start_date;
            $this->survey->end_date = $this->end_date;
            $this->survey->points_allocated = $this->getPointsForType($this->type);
            $this->survey->save();

            $surveyId = $this->survey->id;
            $this->banner_image = null; 
            $this->survey = $this->survey->fresh(); 

            // Dispatch events
            $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Survey information updated!')->to(FormBuilder::class);
            $this->dispatch('surveySettingsUpdated', surveyId: $surveyId)->to(FormBuilder::class);
            $this->dispatch('surveyTitleUpdated', title: $this->title); // If still needed globally
            $this->dispatch('close-modal', name: 'survey-settings-modal-' . $surveyId);
        }
    }

    public function saveSurveyTags()
    {
        $tagIds = array_filter($this->selectedSurveyTags);

        if ($this->survey) {
            $syncData = [];
            foreach ($tagIds as $categoryId => $tagId) {
                $tag = \App\Models\Tag::find($tagId);
                if ($tag) {
                    $syncData[$tagId] = ['tag_name' => $tag->name];
                }
            }
            $this->survey->tags()->sync($syncData);
            
            // Dispatch events
            $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Survey demographic tags updated!')->to(FormBuilder::class);
        
            $this->dispatch('close-modal', name: 'survey-settings-modal-' . $this->survey->id);
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-settings-modal');
    }
}
