<?php

namespace App\Livewire\SuperAdmin\Merchants\Modal;

use Livewire\Component;
use App\Models\Merchant;

class ManageMerchantModal extends Component
{
    public $merchantId;
    public $name;
    public $merchant_code;
    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'merchant_code' => 'required|string|min:8|max:255|unique:merchants,merchant_code,{{merchantId}}',
    ];

    public function mount($merchantId)
    {
        $this->merchantId = $merchantId;
        $this->loadMerchant();
    }

    public function loadMerchant()
    {
        $merchant = Merchant::findOrFail($this->merchantId);
        $this->name = $merchant->name;
        $this->merchant_code = $merchant->merchant_code;
    }

    public function updateMerchant()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'merchant_code' => 'required|string|min:8|max:255|unique:merchants,merchant_code,' . $this->merchantId,
        ]);

        $merchant = Merchant::findOrFail($this->merchantId);
        $merchant->update([
            'name' => $this->name,
            'merchant_code' => $this->merchant_code,
        ]);

        $this->showSuccess = true;
        $this->message = 'Merchant updated successfully.';
        $this->dispatch('merchantUpdated');
        $this->closeModal();
    }

    public function deleteMerchant()
    {
        $merchant = Merchant::findOrFail($this->merchantId);
        // Delete all available vouchers for this merchant
        \App\Models\Voucher::where('merchant_id', $merchant->id)
            ->where('availability', 'available')
            ->delete();
        // Delete all rewards for this merchant
        \App\Models\Reward::where('merchant_id', $merchant->id)->delete();
        $merchant->delete();
        $this->dispatch('merchantDeleted');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'manage-merchant-modal');
        $this->name = '';
        $this->merchant_code = '';
    }

    public function render()
    {
        return view('livewire.super-admin.merchants.modal.manage-merchant-modal');
    }
} 