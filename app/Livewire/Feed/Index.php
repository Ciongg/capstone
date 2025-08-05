<?php

namespace App\Livewire\Feed;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyTopic;
use App\Models\TagCategory;
use App\Models\Tag;
use App\Models\InstitutionTagCategory;
use App\Models\InstitutionTag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\TestTimeService;
class Index extends Component
{
    public $accountUpgrade;
    public $accountDowngrade;

    //filtering openable filtering system 

    // Basic search property remains the same
    public $search = '';
    
    // Unified filter system - add answerableOnly key
    public $activeFilters = [
        'topic' => null,
        'tags' => [],
        'institutionTags' => [],
        'type' => null,
        'institutionOnly' => false,
        'answerableOnly' => true  // Set to true by default
    ];
    
    // Panel for multi-tag selection
    public $showFilterPanel = false;
    public $tempSelectedTagIds = [];
    public $tempSelectedInstitutionTagIds = [];
    
    // Add temporary properties for survey type and filters
    public $tempSurveyType = null;
    public $tempInstitutionOnly = false;
    public $tempAnswerableOnly = true; // Set to true by default
    
    // Renamed for clarity - describes what this variable actually means
    public $hasUnsavedFilterChanges = false;
    
   
    
    // For the survey detail modal
    public $modalSurveyId = null;

    // For infinite scroll
    public $surveys = [];
    public $page = 1;
    public $perPage = 6; // Ensure this is set to 6
    public $hasMorePages = false;
    public $loadingMore = false;
    
    public function mount()
    {
        $this->accountUpgrade = session('account-upgrade');
        $this->accountDowngrade = session('account-downgrade');

        $this->loadSurveys();
    }
    
    // Consistent method to update the "changes pending" flag
    private function updatePendingChangesFlag()
    {
        // Check if temp selections differ from active filters
        $this->hasUnsavedFilterChanges = 
            $this->tempSelectedTagIds != $this->activeFilters['tags'] ||
            $this->tempSelectedInstitutionTagIds != $this->activeFilters['institutionTags'] ||
            $this->tempSurveyType !== $this->activeFilters['type'] ||
            $this->tempInstitutionOnly !== $this->activeFilters['institutionOnly'] ||
            $this->tempAnswerableOnly !== $this->activeFilters['answerableOnly'];
    }

    // Toggle filter panel visibility and initialize temp selection
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
        
        if ($this->showFilterPanel) {
            // Initialize temp selection with current filters when opening
            $this->tempSelectedTagIds = $this->activeFilters['tags'];
            $this->tempSelectedInstitutionTagIds = $this->activeFilters['institutionTags'];
            $this->tempSurveyType = $this->activeFilters['type'];
            $this->tempInstitutionOnly = $this->activeFilters['institutionOnly'];
            $this->tempAnswerableOnly = $this->activeFilters['answerableOnly'];
            $this->hasUnsavedFilterChanges = false; // No changes yet when we first open
        }
    }

    // Handle temporary survey type filter selection within panel
    public function toggleTempSurveyType($type)
    {
        // Toggle the type filter (set to null if already active)
        $this->tempSurveyType = ($this->tempSurveyType === $type) ? null : $type;
        // Update pending changes flag consistently
        $this->updatePendingChangesFlag();
    }
    
    // Clear temporary survey type filter within panel
    public function clearTempSurveyType()
    {
        $this->tempSurveyType = null;
        // Update pending changes flag consistently
        $this->updatePendingChangesFlag();
    }




    // Handle topic filter selection the rounded buttons on top of the survey feed.
    public function toggleTopicFilter($topicId)
    {
        // Store previous state to check if we're removing the filter
        $wasActive = ($this->activeFilters['topic'] == $topicId);
        
        // Toggle the topic filter
        $this->activeFilters['topic'] = $wasActive ? null : $topicId;
        
        // Always reload surveys when toggling topic filter
        $this->page = 1;
        $this->loadSurveys();
    }
    

    // Handle tag selection in filter panel for general tags
    public function togglePanelTagFilter($tagId)
    {
        // Check if tag is already in the temp selection
        $index = array_search($tagId, $this->tempSelectedTagIds);
        
        // Toggle the tag in temp selection
        if ($index !== false) {
            // Remove it if already selected
            unset($this->tempSelectedTagIds[$index]);
            $this->tempSelectedTagIds = array_values($this->tempSelectedTagIds); // Reindex array
        } else {
            // Add it if not selected
            $this->tempSelectedTagIds[] = $tagId;
        }
        
        // Update pending changes flag consistently
        $this->updatePendingChangesFlag();
    }
    
    // Handle tag selection in filter panel for institution tags
    public function togglePanelInstitutionTagFilter($tagId)
    {
        // Check if tag is already in the temp selection
        $index = array_search($tagId, $this->tempSelectedInstitutionTagIds);
        
        // Toggle the tag in temp selection
        if ($index !== false) {
            // Remove it if already selected
            unset($this->tempSelectedInstitutionTagIds[$index]);
            $this->tempSelectedInstitutionTagIds = array_values($this->tempSelectedInstitutionTagIds); // Reindex array
        } else {
            // Add it if not selected
            $this->tempSelectedInstitutionTagIds[] = $tagId;
        }
        
        // Update pending changes flag consistently
        $this->updatePendingChangesFlag();
    }
    
    // Apply tag filters from panel
    public function applyPanelTagFilters()
    {
        // Save selected tags to the unified filter
        $this->activeFilters['tags'] = $this->tempSelectedTagIds;
        $this->activeFilters['institutionTags'] = $this->tempSelectedInstitutionTagIds;
        
        // Save survey type and access settings
        $this->activeFilters['type'] = $this->tempSurveyType;
        $this->activeFilters['institutionOnly'] = $this->tempInstitutionOnly;
        $this->activeFilters['answerableOnly'] = $this->tempAnswerableOnly;
        
        // Close the panel
        $this->showFilterPanel = false;
        $this->hasUnsavedFilterChanges = false; // Clear pending changes after applying
        
        // Reset page and reload surveys
        $this->page = 1;
        $this->loadSurveys();
    }

    // Toggle answerable filter - for the applied filters display
    public function toggleAnswerableFilter()
    {
        $this->activeFilters['answerableOnly'] = !$this->activeFilters['answerableOnly'];
        $this->tempAnswerableOnly = $this->activeFilters['answerableOnly'];
        
        $this->page = 1;
        $this->loadSurveys();
    }

    // Clear all tags from panel
    public function clearPanelTagFilter()
    {
        if ($this->showFilterPanel) {
            // Just clear the temp selection if panel is open
            $this->tempSelectedTagIds = [];
            $this->updatePendingChangesFlag();
            $this->showFilterPanel = true;
        } else {
            // Otherwise clear the actual tag filters
     
            $this->activeFilters['tags'] = [];
            
            // If no other filters remain, reset everything
            if (empty($this->search) && is_null($this->activeFilters['topic'])) {
                $this->resetFilters();
            }
        }
    }

    // Clear all institution tags from panel
    public function clearPanelInstitutionTagFilter()
    {
        if ($this->showFilterPanel) {
            // Just clear the temp selection if panel is open
            $this->tempSelectedInstitutionTagIds = [];
            $this->updatePendingChangesFlag();
        } else {
            // Otherwise clear the actual tag filters
   
            $this->activeFilters['institutionTags'] = [];
            
            // If no other filters remain, reset everything
            if (empty($this->search) && is_null($this->activeFilters['topic']) && empty($this->activeFilters['tags'])) {
                $this->resetFilters();
            }
        }
    }



    // Helper method to check if all filters are inactive
    private function noFiltersActive()
    {
        return empty($this->search) 
            && is_null($this->activeFilters['topic']) 
            && empty($this->activeFilters['tags'])
            && empty($this->activeFilters['institutionTags'])
            && is_null($this->activeFilters['type'])
            && $this->activeFilters['institutionOnly'] === false
            && $this->activeFilters['answerableOnly'] === true; // Default state is true
    }
    
    // Helper method to handle filter clearing and reloading logic
    private function handleFilterChange()
    {
        if ($this->noFiltersActive()) {
            $this->resetFilters();
        } else {
            $this->page = 1;
            $this->loadSurveys();
        }
    }

    // Remove a specific tag filter - used in applied filters.blade.php
    public function removeTagFilter($tagId, $isInstitutionTag = false)
    {
        // Determine the correct filter key
        $filterKey = $isInstitutionTag ? 'institutionTags' : 'tags';

        // Remove the tag from the selected filter type
        $this->activeFilters[$filterKey] = array_filter(
            $this->activeFilters[$filterKey] ?? [],
            fn($id) => $id != $tagId //returns true if id is not equal to tagId getting rid of the selected tag to remove.
        );

        $this->handleFilterChange();
    }

    // Add this method to handle search updates
    public function updatedSearch()
    {
        $this->handleFilterChange();
    }

    // Clear search in applied filters.blade.php
    public function clearSearch()
    {
        $this->search = '';
        $this->handleFilterChange();
    }

    // Clear survey type filter
    public function clearSurveyTypeFilter()
    {
        $this->activeFilters['type'] = null;
        $this->handleFilterChange();
    }
    
    // Clear topic filter 
    public function clearTopicFilter()
    {
        $this->activeFilters['topic'] = null;
        $this->handleFilterChange();
    }



    // Modified reset filter to preserve panel state when clearing filters
    public function resetFilters()
    {
        // Store current panel state - we want to preserve it
        $currentPanelState = $this->showFilterPanel;
        
        // Reset all filter values
        $this->search = '';
        $this->activeFilters = [
            'topic' => null,
            'tags' => [],
            'institutionTags' => [],
            'type' => null,
            'institutionOnly' => false,
            'answerableOnly' => true // Reset to default (true)
        ];
        $this->tempSelectedTagIds = [];
        $this->tempSelectedInstitutionTagIds = [];
        $this->tempSurveyType = null;
        $this->tempInstitutionOnly = false;
        $this->tempAnswerableOnly = true; // Reset to default (true)
        
        // Reset page and reload surveys
        $this->page = 1;
        $this->loadSurveys();
        
        // Restore panel state - don't close if it was open
        $this->showFilterPanel = $currentPanelState;
    }













    // Load more surveys
    public function loadMore()
    {
        $this->loadingMore = true;
        $this->page++;
        $this->loadSurveys(true);
        $this->loadingMore = false;
    }
    
    // Helper method to mark surveys that have reached their response limit
    private function markResponseLimitReachedSurveys($surveys)
    {
        return $surveys->each(function ($survey) {
            if ($survey->target_respondents) {
                $currentResponseCount = $survey->responses()->count();
                $survey->is_response_limit_locked = ($currentResponseCount >= $survey->target_respondents);
            } else {
                $survey->is_response_limit_locked = false;
            }
        });
    }

    // Helper method to mark surveys that have expired
    private function markExpiredSurveys($surveys)
    {
        $now = TestTimeService::now();
        
        return $surveys->each(function ($survey) use ($now) {
            $isExpired = $survey->end_date && $now->gt($survey->end_date);
            $survey->is_expired_locked = $isExpired;
            
            // Update database status to 'finished' if survey is expired and not already finished
            if ($isExpired && $survey->status !== 'finished') {
                // Get a fresh instance of the survey to update
                $surveyToUpdate = Survey::find($survey->id);
                if ($surveyToUpdate) {
                    $surveyToUpdate->status = 'finished';
                    $surveyToUpdate->save();
                    
                    // Update the current instance's status to match the database
                    $survey->status = 'finished';
                }
            }
        });
    }

    // Add a new helper method to mark surveys that haven't started yet
    private function markNotStartedSurveys($surveys)
    {
        $now = TestTimeService::now();
        
        return $surveys->each(function ($survey) use ($now) {
            $isNotStarted = $survey->start_date && $now->lt($survey->start_date);
            $survey->is_not_started_locked = $isNotStarted;
        });
    }

    // Load surveys based on filters - Add not started check
    protected function loadSurveys($append = false)
    {
        // Get authenticated user
        $user = Auth::user();

        // User tag info for filtering
        $userGeneralTags = $user ? $user->tags()->pluck('tags.id')->toArray() : [];
        $userInstitutionTags = $user ? $user->institutionTags()->pluck('institution_tags.id')->toArray() : [];
        $userInstitutionId = $user ? $user->institution_id : null;
        
        // Current date for filtering expired surveys - Use TestTimeService consistently
        $now = TestTimeService::now();
        
        // STEP 1: First get basic surveys with standard filters
        $basicQuery = Survey::query()
            ->where('type', 'basic')
            ->whereIn('status', ['published', 'ongoing']) // Only published and ongoing surveys
            ->with(['user', 'tags', 'institutionTags', 'topic', 'responses']);
            
        // Apply common filters to basic survey query
        $this->applyCommonFilters($basicQuery);
        
        // STEP 2: Then get advanced surveys with demographic filters
        $advancedQuery = Survey::query()
            ->where('type', 'advanced')
            ->whereIn('status', ['published', 'ongoing']) // Only published and ongoing surveys
            ->with(['user', 'tags', 'institutionTags', 'topic', 'responses']);
        
        // Apply common filters to advanced survey query
        $this->applyCommonFilters($advancedQuery);
        
        // STEP 3: Based on filter type, decide what surveys to show
        if ($this->activeFilters['type'] === 'basic') {
            // If explicitly filtering for basic, only run basic query
            $basicSurveys = $basicQuery->get();
            $advancedSurveys = collect([]);
        }
        else if ($this->activeFilters['type'] === 'advanced') {
            // If explicitly filtering for advanced, only run advanced query
            $basicSurveys = collect([]);
            $advancedSurveys = $advancedQuery->get();
            
            // Mark advanced surveys as locked if demographics don't match
            $advancedSurveys = $this->markLockedAdvancedSurveys($advancedSurveys, $userGeneralTags, $userInstitutionTags);
        }
        else {
            // Default case (All Types) - get both types with appropriate filtering
            $basicSurveys = $basicQuery->get();
            $advancedSurveys = $advancedQuery->get();
            
            // Mark advanced surveys as locked if demographics don't match
            $advancedSurveys = $this->markLockedAdvancedSurveys($advancedSurveys, $userGeneralTags, $userInstitutionTags);
        }
        
        // STEP 4: Mark all surveys for various lock conditions
        $combinedSurveys = $basicSurveys->concat($advancedSurveys);
        $combinedSurveys = $this->markLockedInstitutionSurveys($combinedSurveys, $user, $userInstitutionId);
        $combinedSurveys = $this->markExpiredSurveys($combinedSurveys);
        $combinedSurveys = $this->markResponseLimitReachedSurveys($combinedSurveys);
        $combinedSurveys = $this->markNotStartedSurveys($combinedSurveys); // Add the new check
        
        // STEP 5: Apply answerable only filter if enabled
        if ($this->activeFilters['answerableOnly']) {
            $combinedSurveys = $combinedSurveys->filter(function($survey) {
                return !$survey->is_demographic_locked 
                    && !$survey->is_institution_locked 
                    && !$survey->is_expired_locked 
                    && !$survey->is_response_limit_locked
                    && !$survey->is_not_started_locked; // Add the new condition
            });
        }
        
        // STEP 6: Apply ordering/pagination
        $combinedSurveys = $combinedSurveys->unique('id');
        
        // Apply global sorting with TestTimeService
        $combinedSurveys = $combinedSurveys
            ->unique('id')
            ->sortBy(function ($survey) {
                return [
                    // NEGATE points to simulate descending sort
                    -$survey->points_allocated,

                    // Use real end_date, or far future if null - Use TestTimeService for consistency
                    $survey->end_date ?? TestTimeService::now()->addYears(100),
                ];
            })
            ->values(); // Re-index the collection

        // Handle pagination
        $total = $combinedSurveys->count();
        $this->hasMorePages = $total > ($this->page * $this->perPage);
        
        $paginatedSurveys = $combinedSurveys->slice(($this->page - 1) * $this->perPage, $this->perPage + 1);
        
        if ($this->hasMorePages && $paginatedSurveys->count() > $this->perPage) {
            $paginatedSurveys = $paginatedSurveys->take($this->perPage);
        }
        
        // Either append or replace surveys
        if ($append) {
            $this->surveys = collect($this->surveys)->concat($paginatedSurveys)->values();
        } else {
            $this->surveys = $paginatedSurveys;
        }

        $this->dispatch('filter-changed');
    }
   
    // Helper method to apply common filters to any survey query
    private function applyCommonFilters($query)
    {
        // Exclude locked surveys
        $query->where('is_locked', false);
        
        // Apply search filter
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply topic filter - Only when a specific topic is selected
        if (!is_null($this->activeFilters['topic'])) {
            $query->where('survey_topic_id', $this->activeFilters['topic']);
        }
        
        // Apply tag filters based on whether we're filtering by institution tags or regular tags
        if ($this->activeFilters['institutionOnly'] && !empty($this->activeFilters['institutionTags'])) {
            // For institution-only surveys, use institution tags
            foreach ($this->activeFilters['institutionTags'] as $tagId) {
                $query->whereHas('institutionTags', function ($q) use ($tagId) {
                    $q->where('institution_tags.id', $tagId);
                });
            }
        } else if (!empty($this->activeFilters['tags'])) {
            // For regular surveys, use regular tags
            foreach ($this->activeFilters['tags'] as $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            }
        }
        
        // Apply institution only filter
        if ($this->activeFilters['institutionOnly']) {
            $query->where('is_institution_only', true);
        }
    }

     // Helper method to mark advanced surveys as locked based on user demographics
    private function markLockedAdvancedSurveys($advancedSurveys, $userGeneralTags, $userInstitutionTags)
    {
        return $advancedSurveys->map(function ($survey) use ($userGeneralTags, $userInstitutionTags) {
            $surveyTags = $survey->tags->pluck('id')->toArray();
            $surveyInstTags = $survey->institutionTags->pluck('id')->toArray();
            
            // If advanced survey has no tags at all, it's not locked
            if (empty($surveyTags) && empty($surveyInstTags)) {
                $survey->is_demographic_locked = false;
                return $survey;
            }
            
            // STRICT MATCHING: ALL survey tags must be in user's demographics
            // Check if ANY survey tags exist that are NOT in user tags
            $unmatchedGeneralTags = array_diff($surveyTags, $userGeneralTags);
            $unmatchedInstTags = array_diff($surveyInstTags, $userInstitutionTags);
            
            // If both arrays are empty, it means all tags are matched
            $generalTagsMatch = empty($surveyTags) || empty($unmatchedGeneralTags);
            $institutionTagsMatch = empty($surveyInstTags) || empty($unmatchedInstTags);
            
            // Survey is locked if any tags don't match
            $survey->is_demographic_locked = !($generalTagsMatch && $institutionTagsMatch);
            
            return $survey;
        });
    }

    // Helper method to mark institution-only surveys as locked
    private function markLockedInstitutionSurveys($surveys, $user, $userInstitutionId)
    {
        return $surveys->each(function ($survey) use ($user, $userInstitutionId) {
            if ($survey->is_institution_only && $user) {
                $surveyCreatorInstitutionId = $survey->user->institution_id;
                $survey->is_institution_locked = ($userInstitutionId !== $surveyCreatorInstitutionId);
            } else {
                $survey->is_institution_locked = $survey->is_institution_only;
            }
        });
    }

    
    
    // Listen for filter change events
    protected function getListeners()
    {
        return ['filter-changed' => '$refresh'];
    }


    // Main render method with unified filtering
    public function render()
    {
        return view('livewire.feed.index', [
            'userPoints' => Auth::user()?->points ?? 0,
            'topics' => SurveyTopic::orderBy('name')->get(),
            'tagCategories' => TagCategory::with('tags')->orderBy('name')->get(),
            'institutionTagCategories' => InstitutionTagCategory::with('tags')->orderBy('name')->get(),
        ]);
    }
}