<?php

namespace App\Livewire\SuperAdmin\Merchants\Modal;

use Livewire\Component;
use App\Models\Merchant;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ManageMerchantModal extends Component
{
    use WithFileUploads;

    public $merchantId;
    public $name;
    public $merchant_code;
    // Logo handling
    public $image; // new uploaded logo
    public $currentImage; // existing logo path
    public $imageMarkedForDeletion = false;
    public $showSuccess = false;
    public $message = '';
    // new fields
    public $description;
    public $email;
    public $contact_number;
    public $partner_type = 'Merchant'; // new

    protected $rules = [
        'name' => 'required|string|max:255',
        // keep placeholder unique, actual validate happens in updateMerchant
        'merchant_code' => 'required|string|min:8|max:255|unique:merchants,merchant_code,{{merchantId}}',
        'image' => 'nullable|image|max:2048',
        // new validations
        'description' => 'nullable|string|max:1028',
        'email' => 'nullable|email|max:255',
        'contact_number' => 'nullable|string|max:50',
        'partner_type' => 'required|in:Affiliate,Merchant',
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
        $this->currentImage = $merchant->logo_path; // load existing logo path
        // new fields
        $this->description = $merchant->description;
        $this->email = $merchant->email;
        $this->contact_number = $merchant->contact_number;
        $this->partner_type = $merchant->partner_type ?: 'Merchant'; // new
    }

    public function updateMerchant()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'merchant_code' => 'required|string|min:8|max:255|unique:merchants,merchant_code,' . $this->merchantId,
            'image' => 'nullable|image|max:2048',
            // new validations
            'description' => 'nullable|string|max:1028',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:50',
            'partner_type' => 'required|in:Affiliate,Merchant',
        ]);

        $merchant = Merchant::findOrFail($this->merchantId);

        // Start with current logo path
        $imagePath = $this->currentImage;

        // Handle deletion of current logo
        if ($this->imageMarkedForDeletion && $this->currentImage) {
            Storage::disk('public')->delete($this->currentImage);
            $imagePath = null;
        }
        // Handle upload of a new logo
        elseif ($this->image) {
            if ($this->currentImage) {
                Storage::disk('public')->delete($this->currentImage);
            }
            $imagePath = $this->image->store('merchants', 'public');
        }

        $merchant->update([
            'name' => $this->name,
            'merchant_code' => $this->merchant_code,
            'logo_path' => $imagePath,
            // new fields
            'description' => $this->description,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'partner_type' => $this->partner_type, // new
        ]);

        // Reset deletion flag
        $this->imageMarkedForDeletion = false;

        $this->showSuccess = true;
        $this->message = 'Merchant updated successfully.';
        $this->dispatch('merchantUpdated');
        $this->closeModal();
    }

    public function deleteMerchant()
    {
        $merchant = Merchant::findOrFail($this->merchantId);

        // Delete logo from storage if exists
        if ($merchant->logo_path) {
            Storage::disk('public')->delete($merchant->logo_path);
        }

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

    /**
     * Remove the uploaded logo preview
     */
    public function removeImagePreview()
    {
        $this->image = null;
    }

    /**
     * Mark the current logo for deletion
     */
    public function markImageForDeletion()
    {
        $this->imageMarkedForDeletion = true;
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'manage-merchant-modal');
        $this->name = '';
        $this->merchant_code = '';
        $this->image = null;
        $this->currentImage = null;
        $this->imageMarkedForDeletion = false;
    }

    public function render()
    {
        return view('livewire.super-admin.merchants.modal.manage-merchant-modal');
    }
}