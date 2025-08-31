<?php

namespace App\Livewire\SuperAdmin\Vouchers\Modal;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Voucher;
use App\Models\Reward;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Merchant;

class CreateVoucherModal extends Component
{
    use WithFileUploads;

    // Voucher properties
    public $name; 
    public $merchant_id;
    public $description;
    public $cost;
    public $rank_requirement = 'silver';
    public $expiry_date;
    public $image;
    public $quantity = 1;

    // For form handling
    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'merchant_id' => 'required|exists:merchants,id',
        'description' => 'required|string|max:1000',
        'cost' => 'required|integer|min:0',
        'rank_requirement' => 'required|in:silver,gold,diamond',
        'quantity' => 'required|integer|min:1|max:100',
        'expiry_date' => 'nullable|date|after_or_equal:today',
        'image' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->rank_requirement = 'silver';
        $this->quantity = 1;
    }

    public function createVoucher()
    {
        $this->validate();

        // Process and save image if provided
        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('voucher-images', 'public');
        }

        // Create the reward record first
        $reward = Reward::create([
            'name' => $this->name,
            'description' => $this->description,
            'status' => 'available',
            'cost' => $this->cost,
            'quantity' => $this->quantity,
            'type' => 'voucher',
            'rank_requirement' => $this->rank_requirement,
            'image_path' => $imagePath,
            'merchant_id' => $this->merchant_id,
        ]);

        // Parse expiry date
        $expiryDate = $this->expiry_date ? Carbon::parse($this->expiry_date) : null;

        // Generate and save the specified quantity of vouchers
        for ($i = 0; $i < $this->quantity; $i++) {
            $referenceNo = $this->generateUniqueReferenceNumber();
            // Ensure uniqueness
            while (Voucher::where('reference_no', $referenceNo)->exists()) {
                $referenceNo = $this->generateUniqueReferenceNumber();
            }
            Voucher::create([
                'reward_id' => $reward->id,
                'reference_no' => $referenceNo,
                'promo' => $this->name,
                'cost' => $this->cost,
                'availability' => 'available',
                'expiry_date' => $expiryDate,
                'image_path' => $imagePath,
                'merchant_id' => $this->merchant_id,
            ]);
        }

        // Show success message
        $this->showSuccess = true;
        $this->message = "Successfully created {$this->quantity} voucher" . ($this->quantity > 1 ? 's' : '') . " and a new reward.";
        $this->reset([
            'name', 'description', 'cost', 'rank_requirement',
            'expiry_date', 'image', 'quantity', 'merchant_id'
        ]);
        $this->dispatch('voucherCreated');
       
    }

    /**
     * Generate a unique reference number for the voucher
     */
    private function generateUniqueReferenceNumber()
    {
        $merchant = Merchant::find($this->merchant_id);
        $prefix = $merchant ? Str::upper(Str::substr($merchant->name, 0, 2)) : 'XX';
        $random = Str::upper(Str::random(6));
        $timestamp = Str::substr(time(), -4);
        
        return $prefix . '-' . $random . '-' . $timestamp;
    }

    /**
     * Remove the uploaded image preview
     */
    public function removeImagePreview()
    {
        $this->image = null;
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', ['name' => 'create-voucher-modal']);
        $this->reset();
    }

    public function render()
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('livewire.super-admin.vouchers.modal.create-voucher-modal', [
            'merchants' => $merchants,
        ]);
    }
}
