<?php

namespace App\Livewire\SuperAdmin\RewardRedemptions;

use App\Models\RewardRedemption;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class RewardRedemptionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Add filter property for status
    public $statusFilter = 'all';
    public $selectedRedemptionId = null;
    public $searchTerm = '';
    
    // Add counts for each status
    public $pendingCount = 0;
    public $completedCount = 0;
    public $rejectedCount = 0;
    
    protected $listeners = [
        'redemptionStatusUpdated' => '$refresh',
    ];
    
    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
    ];
    
    public function mount()
    {
        $this->updateCounts();
    }
    
    private function updateCounts()
    {
        $this->pendingCount = RewardRedemption::where('status', 'pending')->count();
        $this->completedCount = RewardRedemption::where('status', 'completed')->count();
        $this->rejectedCount = RewardRedemption::where('status', 'rejected')->count();
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
    
    public function render()
    {
        // Query builder for redemptions
        $query = RewardRedemption::with(['user', 'reward']);
        
        // Apply search filter if provided
        if (!empty($this->searchTerm)) {
            $query->where(function (Builder $query) {
                // Search by user UUID - join with users table
                $query->whereHas('user', function (Builder $subQuery) {
                    $subQuery->where('uuid', $this->searchTerm);
                })
                // Search by reward name
                ->orWhereHas('reward', function (Builder $subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }
        
        // Apply status filter if not "all"
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        // Get redemptions with applied filters
        $redemptions = $query->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('livewire.super-admin.reward-redemptions.reward-redemption-index', [
            'redemptions' => $redemptions,
        ]);
    }
}
