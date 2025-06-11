<?php

namespace App\Livewire\SuperAdmin\RewardRedemptions;

use App\Models\RewardRedemption;
use Livewire\Component;
use Livewire\WithPagination;

class RewardRedemptionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Add filter property for status
    public $statusFilter = 'all';
    public $selectedRedemptionId = null;
    
    protected $listeners = [
        'redemptionStatusUpdated' => '$refresh',
    ];
    
    public function render()
    {
        // Query builder for redemptions
        $query = RewardRedemption::with(['user', 'reward']);
        
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

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }
}
