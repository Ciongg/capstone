<?php

namespace App\Livewire\SuperAdmin\RewardRedemptions\Modal;

use App\Models\RewardRedemption;
use Livewire\Component;

class RewardRedemptionModal extends Component
{
    public $redemption = null;
    public $redemptionId;
    public $selectedStatus;
    
    public function mount($redemptionId)
    {
        $this->redemptionId = $redemptionId;
        $this->loadRedemption();
    }
    
    public function loadRedemption()
    {
        if ($this->redemptionId) {
            $this->redemption = RewardRedemption::with(['user', 'reward'])->find($this->redemptionId);
            if ($this->redemption) {
                $this->selectedStatus = $this->redemption->status;
            }
        }
    }
    
    public function updateStatus($status = null)
    {
        if (!$this->redemption) {
            return;
        }
        
        // If no status is passed, use the selectedStatus property
        $statusToUpdate = $status ?? $this->selectedStatus;
        
        $this->redemption->status = $statusToUpdate;
        $this->redemption->save();
        
        $statusText = ucfirst($statusToUpdate);
        session()->flash('modal_message', "Redemption status updated to {$statusText}");
        
        // Notify the parent component that the status was updated
        $this->dispatch('redemptionStatusUpdated');
    }
    
    public function render()
    {
        return view('livewire.super-admin.reward-redemptions.modal.reward-redemption-modal');
    }
}
