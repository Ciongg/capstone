<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use App\Livewire\Surveys\FormBuilder\FormBuilder;
use App\Models\SurveyTopic;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TagCategory;
use App\Models\Tag;
use App\Models\InstitutionTagCategory;
use App\Models\InstitutionTag;
use Illuminate\Support\Facades\Auth;
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
    public $isInstitutionOnly; // Property for institution-only checkbox

    // Add these properties for institution demographics
    public $institutionTagCategories = [];
    public $selectedInstitutionTags = [];

    public $survey_topic_id;
    public $topics;

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
        $this->isInstitutionOnly = (bool)$survey->is_institution_only; // Explicitly cast to boolean
        $this->survey_topic_id = $survey->survey_topic_id;
        $this->topics = SurveyTopic::all();

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

        // Load institution tag categories and tags if they exist
        $this->loadInstitutionTagCategories();
        $this->loadSelectedInstitutionTags();
    }

    // Method to handle the event
    public function updateTitleFromEvent($title)
    {
        $this->title = $title;
    }

    // Get points based on survey type
    public function getPointsForType($type)
    {
        return $type === 'advanced' ? 20 : 10;
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
            $this->survey->is_institution_only = $this->isInstitutionOnly; // Save the institution-only setting
            $this->survey->survey_topic_id = $this->survey_topic_id;
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
                    // Make sure the pivot data includes timestamps by not specifying them
                    // Laravel will automatically set created_at and updated_at if withTimestamps() is used in the relationship
                    $syncData[$tagId] = ['tag_name' => $tag->name];
                }
            }
            
            // Ensure timestamps are respected when syncing
            $this->survey->tags()->sync($syncData);
            $this->survey->institutionTags()->sync([]); // Clear all institution tags
            
            // Reset the selected institution tags array
            $this->selectedInstitutionTags = [];
            
            // Dispatch events
            $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Survey demographic tags updated! Institution demographics have been cleared.');
    
            $this->dispatch('close-modal', name: 'survey-settings-modal-' . $this->survey->id);
        }
    }

    private function loadInstitutionTagCategories()
    {
        // Get the user's institution
        $user = Auth::user();
        if ($user && $user->institution_id) {
            $this->institutionTagCategories = InstitutionTagCategory::where('institution_id', $user->institution_id)
                ->with('tags')
                ->get();
        }
    }

    private function loadSelectedInstitutionTags()
    {
        if ($this->survey && $this->survey->id) {
            // Get institution tags already associated with this survey
            $institutionTags = $this->survey->institutionTags()->get();
            
            // Group by category
            foreach ($institutionTags as $tag) {
                if ($tag->institution_tag_category_id) {
                    $this->selectedInstitutionTags[$tag->institution_tag_category_id] = $tag->id;
                }
            }
        }
    }

    public function saveInstitutionTags()
    {
        $institutionTagIds = array_filter($this->selectedInstitutionTags);

        if ($this->survey) {
            $syncData = [];
            foreach ($institutionTagIds as $categoryId => $tagId) {
                $tag = InstitutionTag::find($tagId);
                if ($tag) {
                    $syncData[$tagId] = ['tag_name' => $tag->name];
                }
            }
            
            // Sync institution tags and clear any general survey tags
            $this->survey->institutionTags()->sync($syncData);
            $this->survey->tags()->sync([]); // Clear all general survey tags
            
            // Reset the selected survey tags array
            $this->selectedSurveyTags = [];
            
            $surveyId = $this->survey->id;
            
            // Dispatch events
            $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Institution demographics updated! General survey demographics have been cleared.');
            $this->dispatch('close-modal', name: 'survey-settings-modal-' . $surveyId);
        }
    }

    // Add this method to handle updating tabs when the institution-only checkbox changes
    public function updatedIsInstitutionOnly($value)
    {
        // Emit event to update Alpine.js components with the new value
        $this->dispatch('updated', ['isInstitutionOnly' => (bool)$value]);
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-settings-modal');
    }
}
