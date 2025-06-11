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
use Carbon\Carbon;

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
        $this->start_date = $survey->start_date ? Carbon::parse($survey->start_date)->format('Y-m-d\TH:i') : null;
        $this->end_date = $survey->end_date ? Carbon::parse($survey->end_date)->format('Y-m-d\TH:i') : null;
        $this->isInstitutionOnly = (bool)$survey->is_institution_only; // Explicitly cast to boolean
        $this->survey_topic_id = $survey->survey_topic_id;
        $this->topics = SurveyTopic::all();

        // Set points based on survey type
        $this->points_allocated = $this->getPointsForType($this->type);

        $this->tagCategories = TagCategory::with('tags')->get();

        // Initialize arrays for tags
        $this->selectedSurveyTags = [];
        $this->selectedInstitutionTags = [];

        // Load current survey tags
        if ($this->survey) {
            foreach ($this->tagCategories as $category) {
                // Get all tags for this category
                $tags = $this->survey->tags()->where('tag_category_id', $category->id)->get();
                if ($tags->count() > 0) {
                    $this->selectedSurveyTags[$category->id] = $tags->pluck('id')->toArray();
                } else {
                    $this->selectedSurveyTags[$category->id] = [];
                }
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
            $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Survey information Updated!')->to(FormBuilder::class);

            $this->dispatch('surveyTitleUpdated', title: $this->title)->to(FormBuilder::class); // If still needed globally
            $this->dispatch('close-modal', name: 'survey-settings-modal-' . $surveyId);
        }
    }

    public function saveSurveyTags()
    {
        $syncData = [];
        
        foreach ($this->selectedSurveyTags as $categoryId => $tagIds) {
            if (!empty($tagIds)) {
                foreach ($tagIds as $tagId) {
                    $tag = \App\Models\Tag::find($tagId);
                    if ($tag) {
                        $syncData[$tagId] = ['tag_name' => $tag->name];
                    }
                }
            }
        }
        
        // Sync the tags
        $this->survey->tags()->sync($syncData);
        $this->survey->institutionTags()->sync([]); // Clear all institution tags
        
        // Reset the selected institution tags array
        $this->selectedInstitutionTags = [];
        
        // Dispatch events
        $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Survey demographic tags updated! Institution demographics have been cleared.');
    
        $this->dispatch('close-modal', name: 'survey-settings-modal-' . $this->survey->id);
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
            foreach ($this->institutionTagCategories as $category) {
                // Get all institution tags for this category
                $tags = $this->survey->institutionTags()
                    ->where('institution_tag_category_id', $category->id)
                    ->get();
                    
                if ($tags->count() > 0) {
                    $this->selectedInstitutionTags[$category->id] = $tags->pluck('id')->toArray();
                } else {
                    $this->selectedInstitutionTags[$category->id] = [];
                }
            }
        }
    }

    public function saveInstitutionTags()
    {
        $syncData = [];
        
        foreach ($this->selectedInstitutionTags as $categoryId => $tagIds) {
            if (!empty($tagIds)) {
                foreach ($tagIds as $tagId) {
                    $tag = InstitutionTag::find($tagId);
                    if ($tag) {
                        $syncData[$tagId] = ['tag_name' => $tag->name];
                    }
                }
            }
        }
        
        // Sync the tags
        $this->survey->institutionTags()->sync($syncData);
        $this->survey->tags()->sync([]); // Clear all general survey tags
        
        // Reset the selected survey tags array
        $this->selectedSurveyTags = [];
        
        $surveyId = $this->survey->id;
        
        // Dispatch events
        $this->dispatch('settingsOperationCompleted', status: 'success', message: 'Institution demographics updated! General survey demographics have been cleared.');
        $this->dispatch('close-modal', name: 'survey-settings-modal-' . $surveyId);
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
