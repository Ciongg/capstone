<?php

namespace App\Livewire\SuperAdmin\UserList;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserListIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    
    public $statusFilter = 'all';
    public $typeFilter = 'all'; // Changed from roleFilter to typeFilter
    public $searchTerm = '';
    public $selectedUserId = null;
    
    protected $listeners = [
        'userStatusUpdated' => '$refresh',
    ];
    
    public function render()
    {
        // Query builder for users
        $query = User::query();
        
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
        
        // Get users with applied filters
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('livewire.super-admin.user-list.user-list-index', [
            'users' => $users,
            'activeCount' => User::where('is_active', true)->whereNull('deleted_at')->count(),
            'inactiveCount' => User::where('is_active', false)->whereNull('deleted_at')->count(),
            'archivedCount' => User::onlyTrashed()->count(),
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
