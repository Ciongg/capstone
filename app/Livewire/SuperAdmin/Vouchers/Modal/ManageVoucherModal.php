<?php

namespace App\Livewire\SuperAdmin\Vouchers\Modal;

use App\Models\Reward;
use App\Models\Voucher;
use App\Models\Merchant;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ManageVoucherModal extends Component
{
    use WithFileUploads;
    
    public $rewardId;
    public $name;
    public $description;
    public $status;
    public $cost;
    public $quantity;
    public $type;
    public $rank_requirement = 'silver';
    public $merchant_id;
    
    public $image;
    public $currentImage;
    public $availableVouchers;
    public $totalVouchers;
    public $restockQuantity = 1;
    
    public $showSuccess = false;
    public $message = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'merchant_id' => 'required|exists:merchants,id',
        'description' => 'required|string',
        'status' => 'required|string|in:available,unavailable,sold_out',
        'cost' => 'required|integer|min:0',
        'quantity' => 'nullable|integer|min:-1',
        'image' => 'nullable|image|max:2048',
        'restockQuantity' => 'nullable|integer|min:1|max:100',
        'rank_requirement' => 'required|in:silver,gold,diamond',
    ];

    public function mount($rewardId)
    {
        $this->rewardId = $rewardId;
        $this->loadReward();
    }

    public function loadReward()
    {
        $reward = Reward::findOrFail($this->rewardId);
        
        $this->name = $reward->name;
        $this->description = $reward->description;
        $this->status = $reward->status;
        $this->cost = $reward->cost;
        $this->quantity = $reward->quantity;
        $this->type = $reward->type;
        $this->currentImage = $reward->image_path;
        $this->rank_requirement = $reward->rank_requirement ?? 'silver';
        $this->merchant_id = $reward->merchant_id;
        
        // Get voucher counts if this is a voucher type reward
        if ($this->type == 'Voucher' || $this->type == 'voucher') {
            $this->refreshVoucherCounts();
        }
    }

    protected function refreshVoucherCounts()
    {
        $this->availableVouchers = Voucher::where('reward_id', $this->rewardId)
            ->where('availability', 'available')
            ->count();
            
        $this->totalVouchers = Voucher::where('reward_id', $this->rewardId)->count();
    }

    public function updateReward()
    {
        $this->validate();
        
        $reward = Reward::findOrFail($this->rewardId);
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'cost' => $this->cost,
            'quantity' => $this->quantity,
            'rank_requirement' => $this->rank_requirement,
            'merchant_id' => $this->merchant_id,
        ];
        
        // Process image if a new one was uploaded
        if ($this->image) {
            // Delete old image if it exists
            if ($reward->image_path && Storage::disk('public')->exists($reward->image_path)) {
                Storage::disk('public')->delete($reward->image_path);
            }
            
            // Store new image
            $imagePath = $this->image->store('reward-images', 'public');
            $data['image_path'] = $imagePath;
            $this->currentImage = $imagePath;
        }
        
        $reward->update($data);
        
        // If this is a voucher reward, also update all related vouchers
        if ($reward->type == 'voucher' || $reward->type == 'Voucher') {
            Voucher::where('reward_id', $reward->id)
                ->update([
                    'cost' => $reward->cost,
                    'promo' => $reward->description,
                    'image_path' => $reward->image_path,
                ]);
        }
        
        // Close modal and notify parent of update
        $this->dispatch('reward-updated', [
            'message' => 'Reward updated successfully.',
            'rewardId' => $this->rewardId
        ]);
        $this->closeModal();
    }
    
    public function restockVouchers()
    {
        $this->validate([
            'restockQuantity' => 'required|integer|min:1|max:100'
        ]);
        
        $reward = Reward::findOrFail($this->rewardId);
        
        // Verify this is a voucher reward
        if ($reward->type != 'voucher' && $reward->type != 'Voucher') {
            $this->dispatch('reward-error', [
                'message' => 'Only voucher rewards can be restocked.',
                'rewardId' => $this->rewardId
            ]);
            return;
        }
        
        // Get sample voucher to use as template
        $sampleVoucher = Voucher::where('reward_id', $this->rewardId)->first();
        
        if (!$sampleVoucher) {
            $this->dispatch('reward-error', [
                'message' => 'No template voucher found to replicate.',
                'rewardId' => $this->rewardId
            ]);
            return;
        }
        
        // Generate and create new vouchers
        $createdCount = 0;
        $maxAttempts = $this->restockQuantity * 3;
        $attempts = 0;
        
        while ($createdCount < $this->restockQuantity && $attempts < $maxAttempts) {
            $attempts++;
            
            // Generate unique reference number
            $referenceNo = $this->generateUniqueReferenceNumber($reward);
            
            // Check if reference number already exists
            if (Voucher::where('reference_no', $referenceNo)->exists()) {
                continue;
            }
            
            // Create the voucher
            $voucher = new Voucher();
            $voucher->reward_id = $reward->id;
            $voucher->reference_no = $referenceNo;
            $voucher->promo = $reward->description;
            $voucher->cost = $reward->cost;
            $voucher->availability = 'available';
            $voucher->expiry_date = $sampleVoucher->expiry_date;
            $voucher->image_path = $reward->image_path;
            $voucher->merchant_id = $reward->merchant_id;
            $voucher->save();
            
            $createdCount++;
        }
        
        // Update available vouchers count in the reward and sync the reward quantity
        $this->refreshVoucherCounts();
        $this->updateRewardQuantity($reward);
        
        // Notify the parent component and close the modal
        $this->dispatch('vouchers-restocked', [
            'message' => "Successfully added {$createdCount} new vouchers.",
            'rewardId' => $this->rewardId
        ]);
    }

    private function generateUniqueReferenceNumber($reward)
    {
        $merchant = $reward->merchant;
        $prefix = $merchant ? Str::upper(Str::substr($merchant->name, 0, 2)) : 'XX';
        $random = Str::upper(Str::random(6));
        $timestamp = Str::substr(time(), -4);
        
        return $prefix . '-' . $random . '-' . $timestamp;
    }

    /**
     * Update the reward quantity to match available vouchers count
     */
    private function updateRewardQuantity(Reward $reward)
    {
        // For voucher rewards, the quantity should reflect the number of available vouchers
        $availableVoucherCount = Voucher::where('reward_id', $reward->id)
            ->where('availability', 'available')
            ->count();
        
        $reward->quantity = $availableVoucherCount;
        
        // If there are no available vouchers, mark as sold out
        if ($availableVoucherCount === 0) {
            $reward->status = 'sold_out';
        } else {
            // If vouchers are available but status is sold_out, update it
            if ($reward->status === 'sold_out') {
                $reward->status = 'available';
            }
        }
        
        $reward->save();
        
        // Update local properties
        $this->quantity = $availableVoucherCount;
        $this->status = $reward->status;
    }
    
    public function closeModal()
    {
        $this->dispatch('close-modal', ['name' => 'reward-modal-' . $this->rewardId]);
    }

    public function deleteReward()
    {
        $reward = Reward::findOrFail($this->rewardId);
        // Delete all available vouchers for this reward
        \App\Models\Voucher::where('reward_id', $reward->id)
            ->where('availability', 'available')
            ->delete();
        $reward->delete();
        $this->showSuccess = true;
        $this->message = 'Reward deleted successfully.';
        // Do not close the modal immediately
        $this->dispatch('rewardDeleted');
    }

    public function render()
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('livewire.super-admin.vouchers.modal.manage-voucher-modal', [
            'merchants' => $merchants,
        ]);
    }
}
