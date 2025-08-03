<?php

namespace App\Livewire\SuperAdmin\UserSurveys;

use Livewire\Component;
use App\Models\Survey;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class UserSurveysIndex extends Component
{
    use WithPagination;
    
    public $searchTerm = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    public $institutionFilter = 'all'; // New property for institution filter
    public $selectedSurveyId = null;

    // Counts for each filter category
    public $pendingCount = 0;
    public $publishedCount = 0;
    public $lockedCount = 0;
    public $archivedCount = 0;
    public $basicCount = 0; // New property for basic count
    public $advancedCount = 0; // New property for advanced count
    public $institutionCount = 0; // New property for institution count

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
        'institutionFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        $this->updateCounts();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function filterByType($type)
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function filterByInstitution($filter)
    {
        $this->institutionFilter = $filter;
        $this->resetPage();
    }

    private function updateCounts()
    {
        $this->pendingCount = Survey::where('status', 'pending')->count();
        $this->publishedCount = Survey::where('status', 'published')->count();
        $this->lockedCount = Survey::where('is_locked', true)->count();
        $this->archivedCount = Survey::onlyTrashed()->count();
        $this->basicCount = Survey::where('type', 'basic')->count();
        $this->advancedCount = Survey::where('type', 'advanced')->count();
        $this->institutionCount = Survey::where('is_institution_only', true)->count();
    }

    public function render()
    {
        $query = Survey::query()->with(['user', 'responses'])
            ->withCount('responses');

        // Apply search filter - only search by title or uuid
        if (!empty($this->searchTerm)) {
            $query->where(function (Builder $query) {
                $query->where('title', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('uuid', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'archived') {
                $query->onlyTrashed();
            } elseif ($this->statusFilter === 'locked') {
                $query->where('is_locked', true);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        // Apply institution filter
        if ($this->institutionFilter !== 'all') {
            $query->where('is_institution_only', true);
        }

        $surveys = $query->latest()->paginate(10);

        return view('livewire.super-admin.user-surveys.user-surveys-index', [
            'surveys' => $surveys,
        ]);
    }
}
