<?php

namespace App\Livewire\SuperAdmin\Vouchers\Modal;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Voucher;
use App\Models\Reward;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreateVoucherModal extends Component
{
    use WithFileUploads;

    // Voucher properties
    public $name; 
    public $store_name;
    public $promo;
    public $cost;
    public $level_requirement = 0;
    public $expiry_date;
    public $image;
    public $quantity = 1;

    // For form handling
    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'store_name' => 'required|string|max:255',
        'promo' => 'required|string|max:2000',
        'cost' => 'required|integer|min:0',
        'level_requirement' => 'required|integer|min:0',
        'quantity' => 'required|integer|min:1|max:100',
        'expiry_date' => 'nullable|date|after_or_equal:today',
        'image' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        // Initialize with default values
        $this->level_requirement = 0;
        $this->quantity = 1;
    }

    public function createVoucher()
    {
        $this->validate();

        // Process and save image if provided
        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('voucher-images', 'public');
            
            // Make sure the path is correctly formatted for storage retrieval
            if (Str::startsWith($imagePath, 'storage/')) {
                $imagePath = Str::replaceFirst('storage/', '', $imagePath);
            }
        }

        // Create the reward record first
        $reward = new Reward();
        $reward->name = $this->name;
        $reward->description = $this->promo;
        $reward->status = 'available';
        $reward->cost = $this->cost;
        $reward->quantity = $this->quantity;
        $reward->type = 'voucher';
        $reward->image_path = $imagePath;
        $reward->save();

        // Parse expiry date
        $expiryDate = null;
        if ($this->expiry_date) {
            $expiryDate = Carbon::parse($this->expiry_date);
        }

        // Generate and save the specified quantity of vouchers
        $createdCount = 0;
        $maxAttempts = $this->quantity * 3; // Allow for some retry attempts in case of duplicates
        $attempts = 0;

        while ($createdCount < $this->quantity && $attempts < $maxAttempts) {
            $attempts++;
            
            // Generate unique reference number
            $referenceNo = $this->generateUniqueReferenceNumber();
            
            // Check if reference number already exists
            if (Voucher::where('reference_no', $referenceNo)->exists()) {
                continue; // Skip this iteration and try again
            }

            // Create the voucher
            $voucher = new Voucher();
            $voucher->reward_id = $reward->id;
            $voucher->reference_no = $referenceNo;
            $voucher->store_name = $this->store_name;
            $voucher->promo = $this->promo;
            $voucher->cost = $this->cost;
            $voucher->level_requirement = $this->level_requirement;
            $voucher->availability = 'available'; // Always set to available
            $voucher->expiry_date = $expiryDate;
            $voucher->image_path = $imagePath;
            $voucher->save();

            $createdCount++;
        }

        // Show success message
        $this->showSuccess = true;
        $this->message = "Successfully created {$createdCount} voucher" . ($createdCount > 1 ? 's' : '') . " and a new reward.";
        
        // Reset form fields
        $this->reset([
            'name', 'store_name', 'promo', 'cost', 'level_requirement',
            'expiry_date', 'image', 'quantity'
        ]);
        
        // Refresh the parent component to show the new vouchers
        $this->dispatch('voucherCreated');
    }

    /**
     * Generate a unique reference number for the voucher
     */
    private function generateUniqueReferenceNumber()
    {
        $prefix = Str::upper(Str::substr($this->store_name, 0, 2));
        $random = Str::upper(Str::random(6));
        $timestamp = Str::substr(time(), -4);
        
        return $prefix . '-' . $random . '-' . $timestamp;
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', ['name' => 'create-voucher-modal']);
        $this->reset();
    }

    public function render()
    {
        return view('livewire.super-admin.vouchers.modal.create-voucher-modal');
    }
}
