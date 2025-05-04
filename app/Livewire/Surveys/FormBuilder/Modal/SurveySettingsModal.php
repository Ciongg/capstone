<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

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

    // Add the listener property
    protected $listeners = ['surveyTitleUpdated' => 'updateTitleFromEvent'];

    public function mount($survey)
    {
        $this->survey = $survey;
        $this->title = $survey->title; // Add this
        $this->description = $survey->description; // Add this
        $this->target_respondents = $survey->target_respondents;
        $this->start_date = $survey->start_date;
        $this->end_date = $survey->end_date;
        $this->points_allocated = $survey->points_allocated;

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

    public function updated($property)
    {
        // Remove image saving logic from here
        // Keep this method empty or remove it if nothing else uses it
    }

    public function saveSurveyInformation()
    {
        // You might want to add validation here, including for banner_image
        // $this->validate([
        //     'title' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'banner_image' => 'nullable|image|max:2048', // Example validation
        //     // ... other rules
        // ]);

        if ($this->survey) {
            // Handle banner image saving here
            if ($this->banner_image) {
                // Delete the old image if it exists
                if ($this->survey->image_path) {
                    Storage::disk('public')->delete($this->survey->image_path);
                }
                // Store the new image
                $path = $this->banner_image->store('surveys', 'public');
                $this->survey->image_path = $path;
            }

            // Save other fields
            $this->survey->title = $this->title;
            $this->survey->description = $this->description;
            $this->survey->target_respondents = $this->target_respondents;
            $this->survey->start_date = $this->start_date;
            $this->survey->end_date = $this->end_date;
            // Correct assignment:
            $this->survey->points_allocated = $this->points_allocated; 

            $this->survey->save();

            // Reset the temporary file upload property AFTER saving
            $this->banner_image = null; 
            // Manually trigger a refresh of the survey data if needed, 
            // especially if the preview relies on the saved path
            $this->survey = $this->survey->fresh(); 

            session()->flash('survey_info_saved', 'Survey information updated!');

            // Dispatch event if needed
            $this->dispatch('surveyTitleUpdated', title: $this->title);
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
            session()->flash('survey_tags_saved', 'Survey demographic tags updated!');
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-settings-modal');
    }
}
