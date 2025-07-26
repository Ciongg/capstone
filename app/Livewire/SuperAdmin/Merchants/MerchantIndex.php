<?php

namespace App\Livewire\SuperAdmin\Merchants;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;

class MerchantIndex extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $selectedMerchantId = null;
    public $createModalKey = 0;
    public $manageModalKey = 0;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
    ];

    protected $listeners = [
        'merchantUpdated' => '$refresh',
        'merchantCreated' => '$refresh',
        'voucherCreated' => '$refresh',
        'merchantDeleted' => '$refresh',
        'refreshModal' => 'handleRefreshModal',
    ];

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->createModalKey = now()->timestamp;
    }

    public function handleRefreshModal($params)
    {
        if (($params['name'] ?? null) === 'create') {
            $this->createModalKey++;
        } elseif (($params['name'] ?? null) === 'manage') {
            $this->manageModalKey++;
        }
    }

    public function mount()
    {
        // Check if user has permission to access merchant management
        $user = Auth::user();
        
        if ($user->type !== 'super_admin') {
            abort(403, 'Access denied. Only super administrators can manage merchants.');
        }
    }

    public function render()
    {
        // Double-check permissions on every render
        $user = Auth::user();
        
        if ($user->type !== 'super_admin') {
            abort(403, 'Access denied. Only super administrators can manage merchants.');
        }

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