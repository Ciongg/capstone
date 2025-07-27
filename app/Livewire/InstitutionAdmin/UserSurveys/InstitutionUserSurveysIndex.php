<?php

namespace App\Livewire\InstitutionAdmin\UserSurveys;

use App\Models\Survey;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InstitutionUserSurveysIndex extends Component
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
        // Get the institution id of the logged-in admin
        $institutionId = Auth::user()->institution_id;

        // Query builder for surveys, only for users under this institution
        $query = Survey::query()
            ->with('user')
            ->whereHas('user', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            });
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
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
            $query->onlyTrashed();
        } elseif ($this->statusFilter === 'all') {
            $query->withTrashed();
        }
        
        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }
        
        $surveys = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        // For counts, restrict to institution's users
        $baseCountQuery = function($status = null, $isLocked = null, $trashed = false) use ($institutionId) {
            $q = Survey::query()->whereHas('user', function($subQ) use ($institutionId) {
                $subQ->where('institution_id', $institutionId);
            });
            if ($status) $q->where('status', $status);
            if (!is_null($isLocked)) $q->where('is_locked', $isLocked);
            if ($trashed) $q->onlyTrashed();
            return $q->count();
        };

        return view('livewire.institution-admin.user-surveys.institution-user-surveys-index', [
            'surveys' => $surveys,
            'pendingCount' => $baseCountQuery('pending'),
            'publishedCount' => $baseCountQuery('published'),
            'lockedCount' => $baseCountQuery(null, true),
            'archivedCount' => $baseCountQuery(null, null, true),
            'ongoingCount' => $baseCountQuery('ongoing'),
            'finishedCount' => $baseCountQuery('finished'),
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
