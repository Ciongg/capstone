<?php

namespace App\Livewire\SuperAdmin\Vouchers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Voucher;

class VoucherInventoryIndex extends Component
{
    use WithPagination;
    
    public $searchTerm = '';
    public $availabilityFilter = 'all';
    public $selectedVoucherId = null;
    
    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'availabilityFilter' => ['except' => 'all'],
    ];

    protected $listeners = [
        'voucherStatusUpdated' => '$refresh',
    ];

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function filterByAvailability($availability)
    {
        $this->availabilityFilter = $availability;
        $this->resetPage();
    }
    
    public function render()
    {
        $query = Voucher::query()
            ->with('reward');
        
        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('reference_no', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('promo', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        // Apply availability filter
        if ($this->availabilityFilter !== 'all') {
            $query->where('availability', $this->availabilityFilter);
        }
        
        // Count vouchers by availability status
        $availableCount = Voucher::where('availability', 'available')->count();
        $usedCount = Voucher::where('availability', 'used')->count();
        $expiredCount = Voucher::where('availability', 'expired')->count();
        $unavailableCount = Voucher::where('availability', 'unavailable')->count();
        
        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('livewire.super-admin.vouchers.voucher-inventory-index', [
            'vouchers' => $vouchers,
            'availableCount' => $availableCount,
            'usedCount' => $usedCount,
            'expiredCount' => $expiredCount,
            'unavailableCount' => $unavailableCount,
        ]);
    }
}
