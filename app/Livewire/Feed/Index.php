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
    
    // Unified filter system - add type and institutionOnly keys
    public $activeFilters = [
        'topic' => null,
        'tags' => [],
        'type' => null,
        'institutionOnly' => false
    ];
    
    // Panel for multi-tag selection
    public $showFilterPanel = false;
    public $tempSelectedTagIds = []; // Temporary storage for selected tags in panel
    
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
        
        // Check if survey type has changed
        $typeChanged = $this->tempSurveyType !== $this->activeFilters['type'];
        
        // Check if institution only setting has changed
        $institutionChanged = $this->tempInstitutionOnly !== $this->activeFilters['institutionOnly'];
        
        return $tagsChanged || $typeChanged || $institutionChanged;
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
        if ($wasActive && empty($this->search) && empty($this->activeFilters['tags'])) {
            $this->resetFiltersWithSpaExperience();
            return;
        }
    }
    
    // Clear topic filter
    public function clearTopicFilter()
    {
        $this->isLoading = true;
        $this->activeFilters['topic'] = null;
        
        // Notify Alpine for immediate UI update
        $this->dispatch('activeTopicChanged', null);
        
        // If no other filters remain, reset everything
        if (empty($this->search) && empty($this->activeFilters['tags'])) {
            $this->resetFiltersWithSpaExperience();
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

    // Apply tag filters from panel
    public function applyPanelTagFilters()
    {
        $this->isLoading = true;
        
        // Save selected tags to the unified filter
        $this->activeFilters['tags'] = $this->tempSelectedTagIds;
        
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

    // Quick tag filter from survey cards
    public function filterByTag($tagId)
    {
        $this->isLoading = true;
        
        // Check if this tag is already active
        $index = array_search($tagId, $this->activeFilters['tags']);
        
        if ($index !== false) {
            // Remove tag if already selected (toggle off)
            unset($this->activeFilters['tags'][$index]);
            $this->activeFilters['tags'] = array_values($this->activeFilters['tags']);
        } else {
            // Add tag if not selected (toggle on)
            $this->activeFilters['tags'] = [$tagId]; // Replace existing tags with just this one
        }
        
        // Force a full component refresh
        $this->dispatch('filter-changed');
        
        // Ensure we reset the page if using pagination
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    // Remove a specific tag filter
    public function removeTagFilter($tagId)
    {
        $this->isLoading = true;
        
        // Remove this tag from filters
        $this->activeFilters['tags'] = array_values(array_filter(
            $this->activeFilters['tags'],
            fn($id) => $id != $tagId
        ));
        
        // If no filters remain, reset everything
        if (empty($this->search) && is_null($this->activeFilters['topic']) && empty($this->activeFilters['tags'])) {
            $this->resetFiltersWithSpaExperience();
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
        
        // Force a full component refresh
        $this->dispatch('filter-changed');
    }

    // Load more surveys
    public function loadMore()
    {
        $this->loadingMore = true;
        $this->page++;
        $this->loadSurveys(true);
        $this->loadingMore = false;
    }
    
    // Load surveys based on filters
    protected function loadSurveys($append = false)
    {
        // Build query - Include all relevant statuses including 'pending'
        $query = Survey::query()
            ->whereIn('status', ['pending', 'ongoing', 'published'])
            ->with(['user', 'tags', 'topic']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply topic filter - Only when a specific topic is selected
        if (!is_null($this->activeFilters['topic'])) {
            $query->where('survey_topic_id', $this->activeFilters['topic']);
        }
        
        // Apply tag filters (AND logic)
        if (!empty($this->activeFilters['tags'])) {
            foreach ($this->activeFilters['tags'] as $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            }
        }
        
        // Apply survey type filter
        if (!is_null($this->activeFilters['type'])) {
            $query->where('type', $this->activeFilters['type']);
        }
        
        // Apply institution only filter
        if ($this->activeFilters['institutionOnly']) {
            $query->where('is_institution_only', true);
        }

        // Custom ordering logic:
        // 1. Highest points first
        // 2. Then advanced surveys before basic
        // 3. Then surveys with closer end dates first
        $query->orderBy('points_allocated', 'desc')
              ->orderByRaw("CASE WHEN type = 'advanced' THEN 0 ELSE 1 END")
              ->orderByRaw("CASE WHEN end_date IS NULL THEN 1 ELSE 0 END")
              ->orderBy('end_date', 'asc');

        // Get surveys with limit and offset
        $newSurveys = $query->skip(($this->page - 1) * $this->perPage)
                            ->take($this->perPage + 1)  // Take one extra to check if there are more
                            ->get();
        
        // Check if we have more pages
        $this->hasMorePages = $newSurveys->count() > $this->perPage;
        
        // Remove the extra item if we have more pages
        if ($this->hasMorePages) {
            $newSurveys = $newSurveys->take($this->perPage);
        }
        
        // Either append or replace surveys
        if ($append) {
            $this->surveys = collect($this->surveys)->concat($newSurveys)->values();
        } else {
            $this->surveys = $newSurveys;
        }
    }

    // Modified reset filter to reset page as well
    protected function resetFiltersWithSpaExperience()
    {
        // Reset all filter values
        $this->search = '';
        $this->activeFilters = [
            'topic' => null,
            'tags' => [],
            'type' => null,
            'institutionOnly' => false
        ];
        $this->tempSelectedTagIds = [];
        $this->tempSurveyType = null;
        $this->tempInstitutionOnly = false;
        
        // Reset page and reload surveys
        $this->page = 1;
        $this->loadSurveys();
        
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