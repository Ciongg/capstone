<?php

namespace App\Livewire\SuperAdmin\UserList;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UserListIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    public $searchTerm = '';
    public $selectedUserId = null;
    public $isInstitutionAdmin = false;
    public $institutionId = null;
    public $institutionName = null;
    
    protected $listeners = [
        'userStatusUpdated' => '$refresh',
    ];
    
    public function mount()
    {
        // Check if user has permission to access user management
        $user = Auth::user();
        
        if (!in_array($user->type, ['super_admin', 'institution_admin'])) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }
        
        // Determine if the current user is an institution admin
        $user = Auth::user();
        $this->isInstitutionAdmin = $user->type === 'institution_admin';
        
        if ($this->isInstitutionAdmin) {
            $this->institutionId = $user->institution_id;
            $this->institutionName = $user->institution ? $user->institution->name : null;
            
            // Validate institution access
            if (!$this->institutionId) {
                session()->flash('error', 'You must be associated with an institution to manage users.');
            }
        }
    }
    
    public function render()
    {
        // Double-check permissions on every render
        $user = Auth::user();
        
        if (!in_array($user->type, ['super_admin', 'institution_admin'])) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }
        
        // Query builder for users
        $query = User::query();
        
        // If institution admin, only show users from their institution
        if ($this->isInstitutionAdmin && $this->institutionId) {
            $query->where('institution_id', $this->institutionId);
        }
        
        // Apply search filter if provided
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true)->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false)->whereNull('deleted_at');
        } elseif ($this->statusFilter === 'archived') {
            $query->onlyTrashed();
        } elseif ($this->statusFilter === 'all') {
            $query->withTrashed(); // Include soft deleted users
        }
        
        // Apply type filter if not "all"
        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        // If institution admin, don't allow filtering to show super_admin users
        if ($this->isInstitutionAdmin && $this->typeFilter === 'super_admin') {
            $this->typeFilter = 'all';
        }
        
        // Get users with applied filters
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Count queries with appropriate filters
        $activeCountQuery = User::where('is_active', true)->whereNull('deleted_at');
        $inactiveCountQuery = User::where('is_active', false)->whereNull('deleted_at');
        $archivedCountQuery = User::onlyTrashed();
        
        // Apply institution filter to counts if needed
        if ($this->isInstitutionAdmin && $this->institutionId) {
            $activeCountQuery->where('institution_id', $this->institutionId);
            $inactiveCountQuery->where('institution_id', $this->institutionId);
            $archivedCountQuery->where('institution_id', $this->institutionId);
        }
            
        return view('livewire.super-admin.user-list.user-list-index', [
            'users' => $users,
            'activeCount' => $activeCountQuery->count(),
            'inactiveCount' => $inactiveCountQuery->count(),
            'archivedCount' => $archivedCountQuery->count(),
            'isInstitutionAdmin' => $this->isInstitutionAdmin,
            'institutionName' => $this->institutionName,
        ]);
    }

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }
    
    public function filterByType($type) // Changed from filterByRole to filterByType
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }
}
