<?php

namespace App\Livewire\SuperAdmin\UserSurveys;

use App\Models\Survey;
use Livewire\Component;
use Livewire\WithPagination;

class UserSurveysIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    public $searchTerm = '';
    public $selectedSurveyId = null;
    
    protected $listeners = [
        'surveyStatusUpdated' => '$refresh',
    ];
    
    public function render()
    {
        // Query builder for surveys
        $query = Survey::query()->with('user');
        
        // Apply search filter if provided
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        // Apply status filter
        if ($this->statusFilter === 'published') {
            $query->where('status', 'published')->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'ongoing') {
            $query->where('status', 'ongoing')->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'finished') {
            $query->where('status', 'finished')->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'pending') {
            $query->where('status', 'pending')->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'locked') {
            $query->where('is_locked', true)->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'archived') {
            $query->onlyTrashed(); // Only show archived surveys
        } elseif ($this->statusFilter === 'all') {
            $query->withTrashed(); // Include archived surveys
        }
        
        // Apply type filter if not "all"
        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }
        
        // Get surveys with applied filters
        $surveys = $query->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('livewire.super-admin.user-surveys.user-surveys-index', [
            'surveys' => $surveys,
            'pendingCount' => Survey::where('status', 'pending')->count(),
            'publishedCount' => Survey::where('status', 'published')->count(),
            'lockedCount' => Survey::where('is_locked', true)->count(),
            'archivedCount' => Survey::onlyTrashed()->count(),
            'ongoingCount' => Survey::where('status', 'ongoing')->count(),
            'finishedCount' => Survey::where('status', 'finished')->count(),
        ]);
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
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }
}
