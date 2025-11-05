<?php

namespace App\Livewire\SuperAdmin\Merchants\Modal;

use Livewire\Component;
use App\Models\Merchant;
use Livewire\WithFileUploads;
use App\Services\AuditLogService;

class CreateMerchantModal extends Component
{
    use WithFileUploads;

    public $name;
    public $merchant_code;
    public $image; // Merchant logo upload
    // new fields
    public $description;
    public $email;
    public $contact_number;
    public $partner_type = 'Merchant'; // new

    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'merchant_code' => 'required|string|min:8|max:255|unique:merchants,merchant_code',
        'image' => 'nullable|image|max:2048',
        // new validations
        'description' => 'nullable|string|max:1028',
        'email' => 'nullable|email|max:255',
        'contact_number' => 'nullable|string|max:50',
        'partner_type' => 'required|in:Affiliate,Merchant', // new
    ];

    public function createMerchant()
    {
        $this->validate();

        $imagePath = $this->image ? $this->image->store('merchants', 'public') : null;

        $merchant = Merchant::create([
            'name' => $this->name,
            'merchant_code' => $this->merchant_code,
            'logo_path' => $imagePath,
            // new fields
            'description' => $this->description,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'partner_type' => $this->partner_type, // new
        ]);

        // Audit log the merchant creation
        AuditLogService::logCreate(
            resourceType: 'Merchant',
            resourceId: $merchant->id,
            data: [
                'name' => $merchant->name,
                'merchant_code' => $merchant->merchant_code,
                'partner_type' => $merchant->partner_type,
                'email' => $merchant->email,
                'contact_number' => $merchant->contact_number,
                'logo_path' => $merchant->logo_path, // Track actual logo path
            ],
            message: "Created {$merchant->partner_type}: '{$merchant->name}' with code '{$merchant->merchant_code}'"
        );

        $this->dispatch('merchantCreated');
        $this->closeModal();
    }

    public function removeImagePreview()
    {
        $this->image = null;
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'create-merchant-modal');
        $this->name = '';
        $this->merchant_code = '';
        $this->image = null;
        // reset new fields
        $this->description = null;
        $this->email = null;
        $this->contact_number = null;
        $this->partner_type = 'Merchant'; // reset
    }

    public function render()
    {
        return view('livewire.super-admin.merchants.modal.create-merchant-modal');
    }
}