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
    public $search = '';
    public $activeTopicId = null;
    public $activePanelTagId = null; // Stores the ID of the tag selected from the filter panel
    public $showFilterPanel = false;
    public $activeTagId = null;      // For quick tag filter on survey cards
    public $modalSurveyId = null;    // For the "Read More" survey detail modal

    // If you have query string parameters, define them here.
    // Based on the request to mimic app.blade.php, modalSurveyId should not be in queryString.
    // protected $queryString = [
    //     'search' => ['except' => ''],
    //     'activeTopicId' => ['except' => null, 'as' => 'topic'],
    //     'activePanelTagId' => ['except' => null, 'as' => 'tag_filter'],
    //     'activeTagId' => ['except' => null, 'as' => 'tag'], // For quick tag filter if needed in URL
    // ];

    public function mount()
    {
        // Initialize any default states if necessary
    }

    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }

    public function toggleTopicFilter($topicId)
    {
        // Store previous state to check if we're removing the filter
        $wasActive = ($this->activeTopicId == $topicId);
        
        // Toggle the filter
        $this->activeTopicId = ($this->activeTopicId == $topicId) ? null : $topicId;
        
        // Notify Alpine for immediate UI update
        $this->dispatch('activeTopicIdChanged', $this->activeTopicId);
        
        // If we just removed this filter and there are no other filters, reset with SPA experience
        if ($wasActive && empty($this->search) && is_null($this->activePanelTagId)) {
            $this->resetFiltersWithSpaExperience();
        }
        
        return null;
    }

    public function togglePanelTagFilter($tagId)
    {
        $this->activePanelTagId = ($this->activePanelTagId == $tagId) ? null : $tagId;
    }

    public function filterByTag($tagId)
    {
        $this->activeTagId = ($this->activeTagId == $tagId) ? null : $tagId;
    }

    protected function refreshIfAllFiltersCleared()
    {
        // Check if all filters are cleared
        if (empty($this->search) && is_null($this->activeTopicId) && is_null($this->activePanelTagId)) {
            // Redirect to the same route to force a page refresh
            return $this->redirect(request()->header('Referer') ?? route('feed.index'));
        }
        
        return null;
    }

    protected function resetFiltersWithSpaExperience()
    {
        // Reset all filter values
        $this->reset(['search', 'activeTopicId', 'activePanelTagId', 'activeTagId']);
        
        // Notify Alpine about the topic filter being reset
        $this->dispatch('activeTopicIdChanged', null);
        
        // Add a "loading" effect to simulate data being refreshed
        $this->dispatch('filters-reset-loading');
    }

    public function clearAllFilters()
    {
        $this->resetFiltersWithSpaExperience();
    }

    public function clearTopicFilter()
    {
        $this->activeTopicId = null;
        
        // Notify Alpine
        $this->dispatch('activeTopicIdChanged', null);
        
        // If no filters remain, reset everything with SPA experience
        if (empty($this->search) && is_null($this->activePanelTagId)) {
            $this->resetFiltersWithSpaExperience();
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        
        // If no filters remain, reset everything with SPA experience
        if (is_null($this->activeTopicId) && is_null($this->activePanelTagId)) {
            $this->resetFiltersWithSpaExperience();
        }
    }

    public function clearPanelTagFilter()
    {
        $this->activePanelTagId = null;
        
        // If no filters remain, reset everything with SPA experience
        if (empty($this->search) && is_null($this->activeTopicId)) {
            $this->resetFiltersWithSpaExperience();
        }
    }

    public function clearTagFilter() // For the quick tag filter
    {
        $this->activeTagId = null;
    }

    // This method is called when the modal dispatches the 'close' event
    public function closeSurveyModal()
    {
        $this->modalSurveyId = null;
    }

    public function render()
    {
        $query = Survey::query()
            ->whereIn('status', ['ongoing', 'published'])
            ->with(['user', 'tags', 'topic']); // Eager load relationships

        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->activeTopicId)) {
            $query->where('survey_topic_id', $this->activeTopicId);
        }

        // Handle quick tag filter from survey cards
        if (!empty($this->activeTagId)) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->activeTagId);
            });
        }

        // Handle tag filter from the filter panel
        if (!empty($this->activePanelTagId)) {
            // If you want panel tag to override or combine with quick tag, adjust logic here.
            // For now, assuming they are separate or you might want to clear one when other is set.
            // This example lets them both apply if different tags are somehow selected.
            // A common approach is to have one $activeTagId and the panel sets that.
            // For simplicity with your current structure:
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->activePanelTagId);
            });
        }

        $surveys = $query->latest()->get();

        $userPoints = Auth::user()?->points ?? 0;
        $topics = SurveyTopic::orderBy('name')->get();
        $tagCategories = TagCategory::with('tags')->orderBy('name')->get(); // For the filter panel
        // $tags = Tag::orderBy('name')->get(); // Only if needed directly in this view, not for the modal

        return view('livewire.feed.index', [
            'surveys' => $surveys,
            'userPoints' => $userPoints,
            'topics' => $topics,
            'tagCategories' => $tagCategories,
            // 'tags' => $tags, // Pass if needed for something else
        ]);
    }
}