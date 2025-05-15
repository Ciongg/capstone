<?php

namespace App\Livewire\Feed;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyTopic;
use App\Models\TagCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    // Basic search property remains the same
    public $search = '';
    
    // Unified filter system - add institutionTags key
    public $activeFilters = [
        'topic' => null,
        'tags' => [],
        'institutionTags' => [], // Add a new filter for institution tags
        'type' => null,
        'institutionOnly' => false
    ];
    
    // Panel for multi-tag selection
    public $showFilterPanel = false;
    public $tempSelectedTagIds = []; // Temporary storage for selected tags in panel
    public $tempSelectedInstitutionTagIds = []; // Add this new property for institution tags
    
    // Add temporary properties for survey type and institution filters
    public $tempSurveyType = null;
    public $tempInstitutionOnly = false;
    
    public $pendingTagChanges = false;
    public $isLoading = false;
    
    // For the survey detail modal
    public $modalSurveyId = null;

    // For infinite scroll
    public $surveys = [];
    public $page = 1;
    public $perPage = 6;
    public $hasMorePages = false;
    public $loadingMore = false;
    
    public function mount()
    {
        $this->loadSurveys();
    }

    // Toggle filter panel visibility and initialize temp selection
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
        
        if ($this->showFilterPanel) {
            // Initialize temp selection with current tags when opening
            $this->tempSelectedTagIds = $this->activeFilters['tags'];
            $this->tempSelectedInstitutionTagIds = $this->activeFilters['institutionTags'];
            // Initialize temp survey type and institution filter values
            $this->tempSurveyType = $this->activeFilters['type'];
            $this->tempInstitutionOnly = $this->activeFilters['institutionOnly'];
            $this->pendingTagChanges = false;
        }
    }

    // Check if there are pending changes to any filters
    public function hasPendingChanges()
    {
        // Check if tags have changed
        $tagsChanged = $this->tempSelectedTagIds != $this->activeFilters['tags'];
        
        // Check if institution tags have changed
        $institutionTagsChanged = $this->tempSelectedInstitutionTagIds != $this->activeFilters['institutionTags'];
        
        // Check if survey type has changed
        $typeChanged = $this->tempSurveyType !== $this->activeFilters['type'];
        
        // Check if institution only setting has changed
        $institutionChanged = $this->tempInstitutionOnly !== $this->activeFilters['institutionOnly'];
        
        return $tagsChanged || $typeChanged || $institutionChanged || $institutionTagsChanged;
    }

    // Handle temporary survey type filter selection within panel
    public function toggleTempSurveyType($type)
    {
        // Toggle the type filter (set to null if already active)
        $this->tempSurveyType = ($this->tempSurveyType === $type) ? null : $type;
        // Set the pending changes flag
        $this->pendingTagChanges = $this->hasPendingChanges();
    }
    
    // Clear temporary survey type filter within panel
    public function clearTempSurveyType()
    {
        $this->tempSurveyType = null;
        // Set the pending changes flag
        $this->pendingTagChanges = $this->hasPendingChanges();
    }

    // Handle topic filter selection
    public function toggleTopicFilter($topicId)
    {
        $this->isLoading = true;
        
        // Store previous state to check if we're removing the filter
        $wasActive = ($this->activeFilters['topic'] == $topicId);
        
        // Toggle the topic filter
        $this->activeFilters['topic'] = ($wasActive) ? null : $topicId;
        
        // Notify Alpine for immediate UI update
        $this->dispatch('activeTopicChanged', $this->activeFilters['topic']);
        
        // If we just removed this filter and there are no other filters, reset everything
        if ($wasActive && 
            empty($this->search) && 
            empty($this->activeFilters['tags']) && 
            empty($this->activeFilters['institutionTags']) && 
            is_null($this->activeFilters['type']) &&
            $this->activeFilters['institutionOnly'] === false) {
            $this->resetFiltersWithSpaExperience();
            return;
        }
        
        // Always reload surveys when toggling topic filter
        $this->page = 1;
        $this->loadSurveys();
    }
    
    // Clear topic filter
    public function clearTopicFilter()
    {
        $this->isLoading = true;
        $this->activeFilters['topic'] = null;
        
        // Notify Alpine for immediate UI update
        $this->dispatch('activeTopicChanged', null);
        
        // If no other filters remain, reset everything
        if (empty($this->search) && 
            empty($this->activeFilters['tags']) && 
            empty($this->activeFilters['institutionTags']) && 
            is_null($this->activeFilters['type']) &&
            $this->activeFilters['institutionOnly'] === false) {
            $this->resetFiltersWithSpaExperience();
            
        } else {
            // Otherwise just reload the surveys with remaining filters
            $this->page = 1;
            $this->loadSurveys();
            $this->dispatch('filters-reset-loading');
            $this->dispatch('filter-changed');
        }
    }

    // Handle tag selection in filter panel (temporary)
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
        
        $this->pendingTagChanges = true;
    }
    
    // Handle institution tag selection in filter panel (temporary)
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
        
        $this->pendingTagChanges = true;
    }
    
    // Apply tag filters from panel
    public function applyPanelTagFilters()
    {
        $this->isLoading = true;
        
        // Save selected tags to the unified filter
        $this->activeFilters['tags'] = $this->tempSelectedTagIds;
        $this->activeFilters['institutionTags'] = $this->tempSelectedInstitutionTagIds;
        
        // Save survey type and institution only settings
        $this->activeFilters['type'] = $this->tempSurveyType;
        $this->activeFilters['institutionOnly'] = $this->tempInstitutionOnly;
        
        // Close the panel
        $this->showFilterPanel = false;
        $this->pendingTagChanges = false;
        
        // Reset page and reload surveys
        $this->page = 1;
        $this->loadSurveys();
        
        $this->isLoading = false;
        
        // Force a full component refresh
        $this->dispatch('filter-changed');
    }

    // Cancel panel without applying changes
    public function cancelPanelTagFilters()
    {
        // Reset temp values to match current active filters
        $this->tempSelectedTagIds = $this->activeFilters['tags'];
        $this->tempSelectedInstitutionTagIds = $this->activeFilters['institutionTags'];
        $this->tempSurveyType = $this->activeFilters['type'];
        $this->tempInstitutionOnly = $this->activeFilters['institutionOnly'];
        
        // Close panel
        $this->showFilterPanel = false;
        $this->pendingTagChanges = false;
    }

    // Clear all tags from panel
    public function clearPanelTagFilter()
    {
        if ($this->showFilterPanel) {
            // Just clear the temp selection if panel is open
            $this->tempSelectedTagIds = [];
            $this->pendingTagChanges = true;
        } else {
            // Otherwise clear the actual tag filters
            $this->isLoading = true;
            $this->activeFilters['tags'] = [];
            
            // If no other filters remain, reset everything
            if (empty($this->search) && is_null($this->activeFilters['topic'])) {
                $this->resetFiltersWithSpaExperience();
            }
        }
    }

    // Clear all institution tags from panel
    public function clearPanelInstitutionTagFilter()
    {
        if ($this->showFilterPanel) {
            // Just clear the temp selection if panel is open
            $this->tempSelectedInstitutionTagIds = [];
            $this->pendingTagChanges = true;
        } else {
            // Otherwise clear the actual tag filters
            $this->isLoading = true;
            $this->activeFilters['institutionTags'] = [];
            
            // If no other filters remain, reset everything
            if (empty($this->search) && is_null($this->activeFilters['topic']) && empty($this->activeFilters['tags'])) {
                $this->resetFiltersWithSpaExperience();
            }
        }
    }

    // Quick tag filter from survey cards - Modified to handle institution tags
    public function filterByTag($tagId, $isInstitutionTag = false)
    {
        $this->isLoading = true;
        
        $filterKey = $isInstitutionTag ? 'institutionTags' : 'tags';
        
        // Check if this tag is already active
        $index = array_search($tagId, $this->activeFilters[$filterKey]);
        
        if ($index !== false) {
            // Remove tag if already selected (toggle off)
            unset($this->activeFilters[$filterKey][$index]);
            $this->activeFilters[$filterKey] = array_values($this->activeFilters[$filterKey]);
        } else {
            // Add tag if not selected (toggle on)
            $this->activeFilters[$filterKey] = [$tagId]; // Replace existing tags with just this one
        }
        
        // Force a full component refresh
        $this->dispatch('filter-changed');
        
        // Ensure we reset the page if using pagination
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    // Remove a specific tag filter - Modified to handle institution tags
    public function removeTagFilter($tagId, $isInstitutionTag = false)
    {
        $this->isLoading = true;
        
        $filterKey = $isInstitutionTag ? 'institutionTags' : 'tags';
        
        // Remove this tag from filters
        $this->activeFilters[$filterKey] = array_values(array_filter(
            $this->activeFilters[$filterKey],
            fn($id) => $id != $tagId
        ));
        
        // Only reset everything if ALL filters are empty (not just this specific tag type)
        if (empty($this->search) && 
            is_null($this->activeFilters['topic']) && 
            empty($this->activeFilters['tags']) && 
            empty($this->activeFilters['institutionTags']) &&
            is_null($this->activeFilters['type']) &&
            $this->activeFilters['institutionOnly'] === false) {
            $this->resetFiltersWithSpaExperience();
        } else {
            // Otherwise, just reload the surveys with the remaining filters
            $this->page = 1;
            $this->loadSurveys();
            $this->dispatch('filter-changed');
        }
    }

    // Clear search
    public function clearSearch()
    {
        $this->search = '';
        
        // If no other filters remain, reset everything
        if (is_null($this->activeFilters['topic']) && empty($this->activeFilters['tags'])) {
            $this->resetFiltersWithSpaExperience();
        }

        
    }

    // Clear all filters
    public function clearAllFilters()
    {
        $this->resetFiltersWithSpaExperience();
    }

    // Handle survey type filter selection
    public function toggleSurveyTypeFilter($type)
    {
        $this->isLoading = true;
        
        // Toggle the type filter (set to null if already active)
        $this->activeFilters['type'] = ($this->activeFilters['type'] === $type) ? null : $type;
        
        // Force a full component refresh
        $this->dispatch('filter-changed');
        
        // Ensure we reset the page if using pagination
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }
    
    // Clear survey type filter
    public function clearSurveyTypeFilter()
    {
        $this->isLoading = true;
        $this->activeFilters['type'] = null;
         $this->dispatch('activeTopicChanged', null);
        
        // If no other filters remain, reset everything
        if (empty($this->search) && 
            is_null($this->activeFilters['topic']) && 
            empty($this->activeFilters['tags']) && 
            empty($this->activeFilters['institutionTags']) &&
            $this->activeFilters['institutionOnly'] === false) {
            $this->resetFiltersWithSpaExperience();
        } else {
            // Otherwise just reload the surveys with remaining filters
            $this->page = 1;
            $this->loadSurveys();
            
            // Add a "loading" effect to simulate data being refreshed
            $this->dispatch('filters-reset-loading');
            $this->dispatch('filter-changed');
        }
    }

    

    // Load more surveys
    public function loadMore()
    {
        $this->loadingMore = true;
        $this->page++;
        $this->loadSurveys(true);
        $this->loadingMore = false;
    }
    
    // Load surveys based on filters - Modified to handle institution tags
    protected function loadSurveys($append = false)
{
    // Get authenticated user
    $user = Auth::user();

    // These relationships are defined in the User model but IDE may flag them incorrectly
    $userGeneralTags = $user ? $user->tags()->pluck('tags.id')->toArray() : [];
    $userInstitutionTags = $user ? $user->institutionTags()->pluck('institution_tags.id')->toArray() : [];
    
    // STEP 1: First get basic surveys with standard filters
    $basicQuery = Survey::query()
        ->where('type', 'basic')
        ->whereIn('status', ['published', 'ongoing']) // Only published and ongoing surveys
        ->with(['user', 'tags', 'institutionTags', 'topic']);
        
    // Apply common filters to basic survey query
    $this->applyCommonFilters($basicQuery);
    
    // STEP 2: Then get advanced surveys with demographic filters
    $advancedQuery = Survey::query()
        ->where('type', 'advanced')
        ->whereIn('status', ['published', 'ongoing']) // Only published and ongoing surveys
        ->with(['user', 'tags', 'institutionTags', 'topic']);
        
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
        // but still apply demographic filtering to advanced surveys
        $basicSurveys = collect([]);
        $advancedSurveys = $advancedQuery->get();
        
        // Apply demographic filtering to advanced surveys
        $advancedSurveys = $this->filterAdvancedSurveysByDemographics($advancedSurveys, $userGeneralTags, $userInstitutionTags);
    }
    else {
        // Default case (All Types) - get both types with appropriate filtering
        $basicSurveys = $basicQuery->get();
        $advancedSurveys = $advancedQuery->get();
        
        // Apply demographic filtering to advanced surveys
        $advancedSurveys = $this->filterAdvancedSurveysByDemographics($advancedSurveys, $userGeneralTags, $userInstitutionTags);
    }
    
    // STEP 4: Combine surveys and apply ordering/pagination
    $combinedSurveys = $basicSurveys->concat($advancedSurveys)->unique('id');
    
    // Apply global sorting
    $combinedSurveys = $combinedSurveys->sortByDesc('points_allocated')
        ->sortBy(function($survey) {
            // Advanced surveys first, then basic
            return $survey->type === 'advanced' ? 0 : 1;
        })
        ->sortBy(function($survey) {
            // Surveys with end dates first, then null dates
            return $survey->end_date === null ? 1 : 0;
        })
        ->sortBy('end_date')
        ->values();
    
    // Log counts for debugging
    Log::info('Survey counts in feed:', [
        'basic_count' => $basicSurveys->count(),
        'advanced_filtered_count' => $advancedSurveys->count(),
        'total_combined' => $combinedSurveys->count()
    ]);
    
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
}

// Helper method to apply common filters to any survey query
private function applyCommonFilters($query)
{
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

// Helper method to filter advanced surveys by user demographics
private function filterAdvancedSurveysByDemographics($advancedSurveys, $userGeneralTags, $userInstitutionTags)
{
    return $advancedSurveys->filter(function ($survey) use ($userGeneralTags, $userInstitutionTags) {
        $surveyTags = $survey->tags->pluck('id')->toArray();
        $surveyInstTags = $survey->institutionTags->pluck('id')->toArray();
        
        // If advanced survey has no tags at all, include it
        if (empty($surveyTags) && empty($surveyInstTags)) {
            return true;
        }
        
        // STRICT MATCHING: ALL survey tags must be in user's demographics
        // Check if ANY survey tags exist that are NOT in user tags
        $unmatchedGeneralTags = array_diff($surveyTags, $userGeneralTags);
        $unmatchedInstTags = array_diff($surveyInstTags, $userInstitutionTags);
        
        // If both arrays are empty, it means all tags are matched
        // For general tags: if survey has general tags, ALL must match
        // For institution tags: if survey has institution tags, ALL must match
        $generalTagsMatch = empty($surveyTags) || empty($unmatchedGeneralTags);
        $institutionTagsMatch = empty($surveyInstTags) || empty($unmatchedInstTags);
        
        // Log the matching process for debugging
        Log::debug('Advanced survey filter:', [
            'survey_id' => $survey->id,
            'survey_title' => $survey->title,
            'survey_tags' => $surveyTags,
            'survey_inst_tags' => $surveyInstTags,
            'user_tags' => $userGeneralTags,
            'user_inst_tags' => $userInstitutionTags,
            'unmatched_general' => $unmatchedGeneralTags,
            'unmatched_inst' => $unmatchedInstTags,
            'general_match' => $generalTagsMatch,
            'inst_match' => $institutionTagsMatch,
            'overall_match' => $generalTagsMatch && $institutionTagsMatch
        ]);
        
        // Only include survey if ALL of its tags match the user's demographics
        return $generalTagsMatch && $institutionTagsMatch;
    });
}
    // Modified reset filter to preserve panel state when clearing filters
    protected function resetFiltersWithSpaExperience()
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
            'institutionOnly' => false
        ];
        $this->tempSelectedTagIds = [];
        $this->tempSelectedInstitutionTagIds = [];
        $this->tempSurveyType = null;
        $this->tempInstitutionOnly = false;
        
        // Reset page and reload surveys
        $this->page = 1;
        $this->loadSurveys();
        
        // Restore panel state - don't close if it was open
        $this->showFilterPanel = $currentPanelState;
        
        // Notify Alpine about the topic filter being reset
        $this->dispatch('activeTopicChanged', null);
        
        // Add a "loading" effect to simulate data being refreshed
        $this->dispatch('filters-reset-loading');
    }
    
    // Listen for filter change events
    protected function getListeners()
    {
        return array_merge(
            parent::getListeners() ?? [],
            [
                'filter-changed' => '$refresh'
            ]
        );
    }

    // Main render method with unified filtering
    public function render()
    {
        return view('livewire.feed.index', [
            'userPoints' => Auth::user()?->points ?? 0,
            'topics' => SurveyTopic::orderBy('name')->get(),
            'tagCategories' => TagCategory::with('tags')->orderBy('name')->get(),
        ]);
    }
}