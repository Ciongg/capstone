<?php

namespace App\Livewire\SuperAdmin\Vouchers;

use App\Models\Reward;
use Livewire\Component;
use Livewire\WithPagination;

class VoucherManager extends Component
{
    use WithPagination;

    public $typeFilter = 'all';
    public $searchTerm = '';
    public $selectedRewardId = null;

    protected $queryString = [
        'typeFilter' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
    ];

    protected $listeners = [
        'vouchers-restocked' => '$refresh',
        'reward-updated' => '$refresh',
        'reward-error' => '$refresh',
        'rewardDeleted' => '$refresh',
        'voucherCreated' => '$refresh',
    ];

    public function filterByType($type)
    {
        $this->typeFilter = $type;
        $this->dispatch('$refresh');
    }

    public function updatedSearchTerm()
    {
         $this->dispatch('$refresh');
    }

    public function render()
    {
        $rewards = Reward::query()
            ->when($this->typeFilter !== 'all', function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->searchTerm . '%')
                            ->orWhereHas('merchant', function($merchantQuery) {
                                $merchantQuery->where('name', 'like', '%' . $this->searchTerm . '%');
                            });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.super-admin.vouchers.voucher-manager', [
            'rewards' => $rewards
        ]);
    }
}
