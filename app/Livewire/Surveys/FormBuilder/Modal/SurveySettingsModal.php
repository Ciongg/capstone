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
    public $isAnnounced; // <-- Add this property

    // Add these properties for institution demographics
    public $institutionTagCategories = [];
    public $selectedInstitutionTags = [];

    public $survey_topic_id;
    public $topics;

    // Add the listener property
    protected $listeners = [
        'surveyTitleUpdated' => 'updateTitleFromEvent',
        'refresh-survey-data' => 'refreshSurveyData'
    ];

    public function mount($survey)
    {
        $this->survey = $survey;
        $this->title = $survey->title;
        $this->description = $survey->description;
        $this->type = $survey->type;
        $this->target_respondents = $survey->target_respondents;
        
        // Format dates properly for datetime-local inputs
        $this->start_date = $survey->start_date ? 
            Carbon::parse($survey->start_date)->format('Y-m-d\TH:i') : 
            null;
        $this->end_date = $survey->end_date ? 
            Carbon::parse($survey->end_date)->format('Y-m-d\TH:i') : 
            null;
            
        // Ensure we're using the actual database value
        $this->isInstitutionOnly = (bool)$survey->is_institution_only;
        $this->isAnnounced = (bool)$survey->is_announced; // <-- Initialize from model
        $this->survey_topic_id = $survey->survey_topic_id;
        $this->topics = SurveyTopic::all();

        // Set points based on survey type AND boost count
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

    // Add this new method to refresh survey data
    public function refreshSurveyData()
    {
        // Refresh the survey from database to get updated boost_count
        $this->survey = $this->survey->fresh();
        
        // Recalculate points_allocated with updated boost_count
        $this->points_allocated = $this->getPointsForType($this->type);
    }

    // Get points based on survey type AND boost count
    public function getPointsForType($type)
    {
        $basePoints = $type === 'advanced' ? 20 : 10;
        $boostPoints = ($this->survey->boost_count ?? 0) * 5;
        return $basePoints + $boostPoints;
    }

    // When type is updated, update points automatically
    public function updatedType($value)
    {
        $this->points_allocated = $this->getPointsForType($value);
    }

    // Add validation for start_date when it's updated
    public function updatedStartDate($value)
    {
        // If end_date exists and is before the new start_date, clear it
        if ($value && $this->end_date && $this->end_date < $value) {
            $this->end_date = null;
        }
    }

    public function saveSurveyInformation()
    {
        try {
            // Add validation rules with more specific time validation
            $this->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:basic,advanced',
                'target_respondents' => 'nullable|integer|min:1',
                'start_date' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            try {
                                $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $value, 'Asia/Manila');
                                $minAllowedTime = Carbon::now('Asia/Manila')->addMinutes(5);
                                
                                if ($startDate->lt($minAllowedTime)) {
                                    $fail('The start date must be at least 5 minutes in the future.');
                                }
                            } catch (\Exception $e) {
                                $fail('Please provide a valid date and time.');
                            }
                        }
                    }
                ],
                'end_date' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) {
                        if ($value && $this->start_date) {
                            try {
                                $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $this->start_date, 'Asia/Manila');
                                $endDate = Carbon::createFromFormat('Y-m-d\TH:i', $value, 'Asia/Manila');
                                
                                if ($endDate->lte($startDate)) {
                                    $fail('The end date must be after the start date.');
                                }
                            } catch (\Exception $e) {
                                $fail('Please provide a valid date and time.');
                            }
                        } elseif ($value && !$this->start_date) {
                            try {
                                // If no start date but has end date, end date must be at least 5 minutes in future
                                $endDate = Carbon::createFromFormat('Y-m-d\TH:i', $value, 'Asia/Manila');
                                $minAllowedTime = Carbon::now('Asia/Manila')->addMinutes(5);
                                
                                if ($endDate->lt($minAllowedTime)) {
                                    $fail('The end date must be at least 5 minutes in the future.');
                                }
                            } catch (\Exception $e) {
                                $fail('Please provide a valid date and time.');
                            }
                        }
                    }
                ],
                'survey_topic_id' => 'nullable|exists:survey_topic,id',
                'banner_image' => 'nullable|image|max:2048', // 2MB max
            ], [
                'start_date.date' => 'Please provide a valid date and time.',
                'end_date.date' => 'Please provide a valid date and time.',
            ]);

            // If validation passes, proceed with saving
            if ($this->survey) {
                // Handle banner image saving here
                if ($this->banner_image) {
                    if ($this->survey->image_path) {
                        Storage::disk('public')->delete($this->survey->image_path);
                    }
                    $path = $this->banner_image->store('surveys', 'public');
                    $this->survey->image_path = $path;
                }

                // Convert datetime-local format to proper datetime for database with timezone
                $startDate = $this->start_date ? Carbon::createFromFormat('Y-m-d\TH:i', $this->start_date, 'Asia/Manila') : null;
                $endDate = $this->end_date ? Carbon::createFromFormat('Y-m-d\TH:i', $this->end_date, 'Asia/Manila') : null;

                // Save other fields
                $this->survey->title = $this->title;
                $this->survey->description = $this->description;
                $this->survey->type = $this->type;
                $this->survey->target_respondents = $this->target_respondents;
                $this->survey->start_date = $startDate;
                $this->survey->end_date = $endDate;
                // Use the calculated points instead of just base points
                $this->survey->points_allocated = $this->getPointsForType($this->type);
                $this->survey->is_institution_only = $this->isInstitutionOnly;
                $this->survey->is_announced = $this->isAnnounced; // <-- Save to model
                $this->survey->survey_topic_id = $this->survey_topic_id;
                $this->survey->save();

                $surveyId = $this->survey->id;
                $this->banner_image = null; 
                $this->survey = $this->survey->fresh(); 

                // Clear demographics based on institution-only status
                if ($this->isInstitutionOnly) {
                    // If now institution-only, clear all general survey tags
                    $this->survey->tags()->sync([]);
                    $this->selectedSurveyTags = [];
                } else {
                    // If now public, clear all institution tags
                    $this->survey->institutionTags()->sync([]);
                    $this->selectedInstitutionTags = [];
                }

                // Dispatch events only on successful save - use save status with custom message
                $this->dispatch('setSaveStatus', status: 'saved', message: 'New settings saved!')->to(FormBuilder::class);
            $this->dispatch('surveySettingsUpdated', surveyId: $surveyId)->to(FormBuilder::class);
                $this->dispatch('surveyTitleUpdated', title: $this->title)->to(FormBuilder::class);
                $this->dispatch('close-modal', name: 'survey-settings-modal-' . $surveyId);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors for debugging
            \Log::error("Validation failed:", [
                'errors' => $e->errors(),
                'start_date' => $this->start_date,
                'end_date' => $this->end_date
            ]);
            
            // Re-throw validation exception so @error directives can display the errors
            // Do NOT close the modal when validation fails
            throw $e;
        } catch (\Exception $e) {
            // Handle other exceptions
            \Log::error("Save survey information error: " . $e->getMessage());
            throw $e;
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
        
        // Dispatch events - use save status with custom message
        $this->dispatch('setSaveStatus', status: 'saved', message: 'New settings saved!')->to(FormBuilder::class);
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
        
        // Dispatch events - use save status with custom message
        $this->dispatch('setSaveStatus', status: 'saved', message: 'New settings saved!')->to(FormBuilder::class);
        $this->dispatch('close-modal', name: 'survey-settings-modal-' . $surveyId);
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-settings-modal');
    }
}



