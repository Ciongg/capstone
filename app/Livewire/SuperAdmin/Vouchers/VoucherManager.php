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
    public $successMessage = '';
    public $errorMessage = '';

    protected $queryString = [
        'typeFilter' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
    ];

    protected $listeners = [
        'vouchers-restocked' => 'handleVoucherRestocked',
        'reward-updated' => 'handleRewardUpdated',
        'reward-error' => 'handleRewardError',
    ];

    public function filterByType($type)
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function handleVoucherRestocked($data)
    {
        $this->successMessage = $data['message'];
        $this->resetPage();
    }

    public function handleRewardUpdated($data)
    {
        $this->successMessage = $data['message'];
        $this->resetPage();
    }

    public function handleRewardError($data)
    {
        $this->errorMessage = $data['message'];
    }

    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
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
                            ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.super-admin.vouchers.voucher-manager', [
            'rewards' => $rewards
        ]);
    }
}
