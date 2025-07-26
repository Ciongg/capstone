<?php

namespace App\Livewire\SuperAdmin\Merchants\Modal;

use Livewire\Component;
use App\Models\Merchant;

class CreateMerchantModal extends Component
{
    public $name;
    public $merchant_code;
    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'merchant_code' => 'required|string|min:8|max:255|unique:merchants,merchant_code',
    ];

    public function createMerchant()
    {
        $this->validate();

        Merchant::create([
            'name' => $this->name,
            'merchant_code' => $this->merchant_code,
        ]);

        $this->dispatch('merchantCreated');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'create-merchant-modal');
        $this->name = '';
        $this->merchant_code = '';
    }

    public function render()
    {
        return view('livewire.super-admin.merchants.modal.create-merchant-modal');
    }
} 