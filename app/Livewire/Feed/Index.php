<?php

namespace App\Livewire\Feed;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyTopic;
use App\Models\TagCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    // Basic search property remains the same
    public $search = '';
    
    // Unified filter system
    public $activeFilters = [
        'topic' => null,
        'tags' => []
    ];
    
    // Panel for multi-tag selection
    public $showFilterPanel = false;
    public $tempSelectedTagIds = []; // Temporary storage for selected tags in panel
    public $pendingTagChanges = false;
    public $isLoading = false;
    
    // For the survey detail modal
    public $modalSurveyId = null;

    public function mount()
    {
        // Initialize any default states if necessary
    }

    // Toggle filter panel visibility and initialize temp selection
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
        
        if ($this->showFilterPanel) {
            // Initialize temp selection with current tags when opening
            $this->tempSelectedTagIds = $this->activeFilters['tags'];
            $this->pendingTagChanges = false;
        }
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
        
        // Close the panel
        $this->showFilterPanel = false;
        $this->pendingTagChanges = false;
        
        // Force a full component refresh
        $this->dispatch('filter-changed');
        
        // Ensure we reset the page if using pagination
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    // Cancel panel without applying changes
    public function cancelPanelTagFilters()
    {
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

    // Reset all filters with SPA-like experience
    protected function resetFiltersWithSpaExperience()
    {
        // Reset all filter values
        $this->search = '';
        $this->activeFilters = [
            'topic' => null,
            'tags' => []
        ];
        $this->tempSelectedTagIds = [];
        
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
        // Build query
        $query = Survey::query()
            ->whereIn('status', ['ongoing', 'published'])
            ->with(['user', 'tags', 'topic']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply topic filter
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

        // Get surveys with pagination
        $surveys = $query->latest()->paginate(10);
        
        // Reset loading state after render
        $this->isLoading = false;
        
        return view('livewire.feed.index', [
            'surveys' => $surveys,
            'userPoints' => Auth::user()?->points ?? 0,
            'topics' => SurveyTopic::orderBy('name')->get(),
            'tagCategories' => TagCategory::with('tags')->orderBy('name')->get(),
        ]);
    }
}