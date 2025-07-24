<?php

namespace App\Livewire\SuperAdmin\Merchants;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Merchant;

class MerchantIndex extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $selectedMerchantId = null;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
    ];

    protected $listeners = [
        'merchantUpdated' => '$refresh',
        'merchantCreated' => '$refresh',
        'voucherCreated' => '$refresh',
    ];

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Merchant::query();

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('merchant_code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $merchants = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.super-admin.merchants.merchant-index', [
            'merchants' => $merchants,
        ]);
    }
} 