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
use App\Models\User;
use App\Models\InboxMessage;
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
    public $isGuestAllowed; // <-- Add property for guest responses

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

    // Add new properties for collaborator management
    public $collaborators = [];
    public $newCollaboratorUuid;

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
        $this->isAnnounced = (bool)$survey->is_announced;
        $this->isGuestAllowed = (bool)$survey->is_guest_allowed; // Initialize from model
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

        // Load collaborators
        $this->loadCollaborators();
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
            // Create validation rules array
            $validationRules = [
                'title' => 'required|string|max:256',
                'description' => 'nullable|string|max:2046',
                'type' => 'required|in:basic,advanced',
                'target_respondents' => 'nullable|integer|min:10|max:1000', // Changed min from 1 to 10
                'survey_topic_id' => 'nullable|exists:survey_topic,id',
                'banner_image' => 'nullable|image|max:2048', // 2MB max
            ];
            
            // Only validate start_date if survey is NOT published yet
            if ($this->survey->status === 'pending') {
                $validationRules['start_date'] = [
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
                ];
            }
            
            // Always validate end_date, but with different rules based on start_date and status
            $validationRules['end_date'] = [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if (!$value) return; // Skip if no end date
                    
                    try {
                        $endDate = Carbon::createFromFormat('Y-m-d\TH:i', $value, 'Asia/Manila');
                        
                        // Check if survey already published - then only compare with now
                        if (in_array($this->survey->status, ['published', 'ongoing'])) {
                            $minAllowedTime = Carbon::now('Asia/Manila');
                            if ($endDate->lt($minAllowedTime)) {
                                $fail('The end date must be in the future.');
                            }
                        } 
                        // Survey pending and has start date - check against start date
                        elseif ($this->start_date) {
                            $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $this->start_date, 'Asia/Manila');
                            if ($endDate->lte($startDate)) {
                                $fail('The end date must be after the start date.');
                            }
                        } 
                        // Survey pending, no start date - check against now + 5 min
                        else {
                            $minAllowedTime = Carbon::now('Asia/Manila')->addMinutes(5);
                            if ($endDate->lt($minAllowedTime)) {
                                $fail('The end date must be at least 5 minutes in the future.');
                            }
                        }
                    } catch (\Exception $e) {
                        $fail('Please provide a valid date and time.');
                    }
                }
            ];
            
            // Run validation
            $this->validate($validationRules, [
                'start_date.date' => 'Please provide a valid date and time.',
                'end_date.date' => 'Please provide a valid date and time.',
            ]);

            // Special validation for surveys with start date - only for pending surveys
            if ($this->start_date && $this->survey->status === 'pending') {
                // Force a fresh load of the survey with all relationships to ensure accurate counts
                $this->survey->load(['pages.questions']);
                
                // Validate: at least 1 page
                if ($this->survey->pages->isEmpty()) {
                    $this->dispatch('validation-error', [
                        'message' => 'You must have at least 1 page in your survey before setting a start date.'
                    ]);
                    return;
                }

                // Validate: at least 6 REQUIRED questions total
                $totalRequiredQuestions = 0;
                foreach ($this->survey->pages as $page) {
                    // Only count questions where required = true
                    $totalRequiredQuestions += $page->questions->where('required', true)->count();
                }
                
                if ($totalRequiredQuestions < 6) {
                    $this->dispatch('validation-error', [
                        'message' => 'Your survey must have at least 6 required questions before setting a start date. Please mark at least ' . (6 - $totalRequiredQuestions) . ' more questions as required.'
                    ]);
                    return;
                }

                // Prevent setting start date for advanced survey if no demographic is set
                if ($this->type === 'advanced') {
                    // Force a refresh of tag relationships
                    $this->survey->load(['tags', 'institutionTags']);
                    
                    if ($this->isInstitutionOnly) {
                        // Institution-only: require at least one institution tag
                        if ($this->survey->institutionTags->isEmpty()) {
                            $this->dispatch('validation-error', [
                                'message' => 'You must set at least one demographic (institution tag) before setting a start date for an advanced survey.'
                            ]);
                            return;
                        }
                    } else {
                        // Public: require at least one general tag
                        if ($this->survey->tags->isEmpty()) {
                            $this->dispatch('validation-error', [
                                'message' => 'You must set at least one demographic (general tag) before setting a start date for an advanced survey.'
                            ]);
                            return;
                        }
                    }
                }
            }

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

                // When survey is published, preserve the original start_date
                if (in_array($this->survey->status, ['published', 'ongoing'])) {
                    // Don't update start_date if already published
                    $startDate = $this->survey->start_date;
                } else {
                    $startDate = $this->start_date ? Carbon::createFromFormat('Y-m-d\TH:i', $this->start_date, 'Asia/Manila') : null;
                }
                
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
                $this->survey->is_announced = $this->isAnnounced;
                $this->survey->is_guest_allowed = $this->isGuestAllowed; // Save guest allowed setting
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
            // Extract all validation errors and convert to a single message
            $errors = $e->validator->errors()->all();
            $this->dispatch('validation-error', [
                'message' => implode(' ', $errors)
            ]);
            return;
        } catch (\Exception $e) {
            // Handle other exceptions
            \Log::error("Save survey information error: " . $e->getMessage());
            $this->dispatch('validation-error', [
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
            return;
        }
    }

    public function saveSurveyTags()
    {
        try {
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
        } catch (\Exception $e) {
            $this->dispatch('validation-error', [
                'message' => 'Failed to save survey tags: ' . $e->getMessage()
            ]);
            return;
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
        try {
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
        } catch (\Exception $e) {
            $this->dispatch('validation-error', [
                'message' => 'Failed to save institution tags: ' . $e->getMessage()
            ]);
            return;
        }
    }

    /**
     * Remove the uploaded banner image preview
     */
    public function removeBannerImagePreview()
    {
        $this->banner_image = null;
    }

    /**
     * Delete the current banner image from storage
     */
    public function deleteCurrentBannerImage()
    {
        if ($this->survey->image_path) {
            Storage::disk('public')->delete($this->survey->image_path);
            $this->survey->image_path = null;
            $this->survey->save();
        }
    }
    
    // Helper method to show error alerts with SweetAlert2
    private function showErrorAlert($message)
    {
        $this->dispatch('showErrorAlert', message: $message);
    }

    // Add methods for collaborator management
    private function loadCollaborators()
    {
        $this->collaborators = [];
        
        if ($this->survey) {
            $surveyCollaborators = $this->survey->collaborators()->get();
            
            foreach ($surveyCollaborators as $user) {
                $this->collaborators[] = [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'name' => $user->first_name . ' ' . $user->last_name
                ];
            }
        }
    }
    
    public function addCollaborator()
    {
        try {
            // Validate UUID input with max length
            $this->validate([
                'newCollaboratorUuid' => 'required|string|uuid|max:36'
            ]);
            
            // Find user by UUID
            $user = User::where('uuid', $this->newCollaboratorUuid)->first();
            
            // Debug log to check if user exists
            \Log::debug("Collaborator check:", [
                'uuid' => $this->newCollaboratorUuid,
                'user_found' => (bool)$user
            ]);
            
            if (!$user) {
                // Make sure the message is correctly formatted for the client
                $errorMessage = 'No user found with this UUID. Please verify the UUID is correct and the user exists in the system.';
                
                \Log::debug("User not found error:", [
                    'message' => $errorMessage
                ]);
                
                // Use the correct dispatch format with array parameter
                $this->dispatch('validation-error', [
                    'message' => $errorMessage
                ]);
                return;
            }
            
            // Check if user is already the owner
            if ($user->id === $this->survey->user_id) {
                $this->dispatch('validation-error', [
                    'message' => 'This user is already the survey owner.'
                ]);
                return;
            }
            
            // Check if user is already a collaborator
            if ($this->survey->isCollaborator($user)) {
                $this->dispatch('validation-error', [
                    'message' => 'This user is already a collaborator on this survey.'
                ]);
                return;
            }
            
            // Add as collaborator
            $this->survey->collaborators()->attach($user->id, ['user_uuid' => $user->uuid]);
            
            // Send inbox message to the new collaborator
            InboxMessage::create([
                'recipient_id' => $user->id,
                'subject' => 'Added as Survey Collaborator',
                'message' => "You have been added as a collaborator to the survey '{$this->survey->title}' by {" . Auth::user()->first_name . " " . Auth::user()->last_name . "}.\n\n" .
                            "You can now view and edit this survey. To access it, go to Profile > My Surveys > Shared with Me.",
                'url' => route('surveys.create', $this->survey->uuid),
                'read_at' => null
            ]);
            
            // Refresh collaborator list
            $this->loadCollaborators();
            
            // Clear input only on successful addition
            $this->newCollaboratorUuid = '';
            
            // Show success message
            $this->dispatch('showSuccessAlert', [
                'message' => 'Collaborator added successfully!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Extract all validation errors and convert to a single message
            $errors = $e->validator->errors()->all();
            $this->dispatch('validation-error', [
                'message' => implode(' ', $errors)
            ]);
        } catch (\Exception $e) {
            \Log::error("Collaborator error: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('validation-error', [
                'message' => 'Failed to add collaborator: ' . $e->getMessage()
            ]);
        }
    }
    
    public function removeCollaborator($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->first();
            
            if ($user) {
                $this->survey->collaborators()->detach($user->id);
                $this->loadCollaborators();
                $this->dispatch('showSuccessAlert', message: 'Collaborator removed successfully!');
            }
        } catch (\Exception $e) {
            $this->dispatch('showErrorAlert', message: 'Failed to remove collaborator: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-settings-modal');
    }
}

