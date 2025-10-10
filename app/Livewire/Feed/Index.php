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
    // Basic properties
    public $accountUpgrade;
    public $accountDowngrade;
    public $search = '';
    public $modalSurveyId = null;
    
    // Unified filter system with default values
    public $activeFilters = [
        'topic' => null,
        'tags' => [],
        'institutionTags' => [],
        'type' => null,
        'institutionOnly' => false,
        'answerableOnly' => true 
    ];
    
    // Panel state management
    public $showFilterPanel = false;
    public $tempFilters = [];
    public $hasUnsavedFilterChanges = false;

    // For infinite scroll
    public $surveys = [];
    public $page = 1;
    public $perPage = 6;
    public $hasMorePages = false;
    public $loadingMore = false;
    
    public function mount()
    {
        $this->accountUpgrade = session('account-upgrade');
        $this->accountDowngrade = session('account-downgrade');
        $this->tempFilters = $this->activeFilters;
        $this->loadSurveys();
    }
    
    // SIMPLIFIED FILTER PANEL MANAGEMENT
    
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
        
        if ($this->showFilterPanel) {
            // Reset temp filters to match active filters when opening panel
            $this->tempFilters = $this->activeFilters;
            $this->hasUnsavedFilterChanges = false;
        }
    }
    
    // Generic method to update any temporary filter
    public function updateTempFilter($key, $value)
    {
        $this->tempFilters[$key] = $value;
        $this->checkForFilterChanges();
    }
    
    // Toggle a specific filter value (for simple toggles like survey type)
    public function toggleTempFilter($key, $value)
    {
        $this->tempFilters[$key] = ($this->tempFilters[$key] === $value) ? null : $value;
        $this->checkForFilterChanges();
    }
    
    // Toggle array-based filters (for tags)
    public function toggleTempArrayFilter($key, $id)
    {
        if (!isset($this->tempFilters[$key])) {
            $this->tempFilters[$key] = [];
        }
        
        $index = array_search($id, $this->tempFilters[$key]);
        
        if ($index !== false) {
            unset($this->tempFilters[$key][$index]);
            $this->tempFilters[$key] = array_values($this->tempFilters[$key]);
        } else {
            $this->tempFilters[$key][] = $id;
        }
        
        $this->checkForFilterChanges();
    }
    
    // Check if there are pending changes - must be public to be called from Blade
    public function checkForFilterChanges()
    {
        $this->hasUnsavedFilterChanges = $this->tempFilters != $this->activeFilters;
    }
    
    // Apply filters from panel
    public function applyFilters()
    {
        $this->activeFilters = $this->tempFilters;
        $this->showFilterPanel = false;
        $this->hasUnsavedFilterChanges = false;
        $this->reloadSurveys();
    }




    
    // SIMPLIFIED DIRECT FILTER OPERATIONS
    
    // Toggle topic filter (used for the rounded buttons on top of feed)
    public function toggleTopicFilter($topicId)
    {
        $wasActive = ($this->activeFilters['topic'] == $topicId);
        $this->activeFilters['topic'] = $wasActive ? null : $topicId;
        $this->reloadSurveys();
    }
    
    // Toggle boolean filters (like answerableOnly)
    public function toggleBooleanFilter($key)
    {
        $this->activeFilters[$key] = !$this->activeFilters[$key];
        $this->tempFilters[$key] = $this->activeFilters[$key];
        $this->reloadSurveys();
    }
    
    // Remove a specific tag filter
    public function removeTagFilter($tagId, $isInstitutionTag = false)
    {
        $filterKey = $isInstitutionTag ? 'institutionTags' : 'tags';
        $this->activeFilters[$filterKey] = array_filter(
            $this->activeFilters[$filterKey] ?? [],
            fn($id) => $id != $tagId
        );
        $this->reloadSurveys();
    }
    
    // Clear specific filter type
    public function clearFilter($key)
    {
        if ($this->showFilterPanel) {
            // Clear in temporary state
            $this->tempFilters[$key] = is_array($this->activeFilters[$key]) ? [] : null;
            $this->checkForFilterChanges();
        } else {
            // Clear in active filters
            $this->activeFilters[$key] = is_array($this->activeFilters[$key]) ? [] : null;
            $this->reloadSurveys();
        }
    }
    
    // Search updates
    public function updatedSearch()
    {
        $this->reloadSurveys();
    }
    
    // Clear search field
    public function clearSearch()
    {
        $this->search = '';
        $this->reloadSurveys();
    }
    
    // Reset all filters
    public function resetFilters()
    {
        $currentPanelState = $this->showFilterPanel;
        
        $this->search = '';
        $this->activeFilters = [
            'topic' => null,
            'tags' => [],
            'institutionTags' => [],
            'type' => null,
            'institutionOnly' => false,
            'answerableOnly' => true
        ];
        $this->tempFilters = $this->activeFilters;
        
        $this->reloadSurveys();
        $this->showFilterPanel = $currentPanelState;
    }
    
    // Helper method to reload surveys (consistent approach)
    private function reloadSurveys()
    {
        $this->page = 1;
        $this->loadSurveys();
    }
    
    // Helper to check if all filters are at default values
    private function noFiltersActive()
    {
        return empty($this->search) 
            && is_null($this->activeFilters['topic']) 
            && empty($this->activeFilters['tags'])
            && empty($this->activeFilters['institutionTags'])
            && is_null($this->activeFilters['type'])
            && $this->activeFilters['institutionOnly'] === false
            && $this->activeFilters['answerableOnly'] === true;
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

    // Helper method to mark surveys that haven't started yet
    private function markNotStartedSurveys($surveys)
    {
        $now = TestTimeService::now();
        
        return $surveys->each(function ($survey) use ($now) {
            $isNotStarted = $survey->start_date && $now->lt($survey->start_date);
            $survey->is_not_started_locked = $isNotStarted;
        });
    }
    
    // Helper method to apply common filters to any survey query
    private function applyCommonFilters($query)
    {
        // Exclude locked surveys
        $query->where('is_locked', false);
        
        // Apply search filter - ensure case-insensitive search with wildcards on both sides
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





















   
    // Load more surveys (for infinite scroll)
    public function loadMore()
    {
        $this->loadingMore = true;
        $this->page++;
        $this->loadSurveys(true);
        $this->loadingMore = false;
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