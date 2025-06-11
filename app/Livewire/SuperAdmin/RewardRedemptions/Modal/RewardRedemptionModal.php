<?php

namespace App\Livewire\SuperAdmin\RewardRedemptions\Modal;

use App\Models\RewardRedemption;
use Livewire\Component;

class RewardRedemptionModal extends Component
{
    public $redemption = null;
    public $redemptionId;
    
    public function mount($redemptionId)
    {
        $this->redemptionId = $redemptionId;
        $this->loadRedemption();
    }
    
    public function loadRedemption()
    {
        if ($this->redemptionId) {
            $this->redemption = RewardRedemption::with(['user', 'reward'])->find($this->redemptionId);
        }
    }
    
    public function updateStatus($status)
    {
        if (!$this->redemption) {
            return;
        }
        
        $this->redemption->status = $status;
        $this->redemption->save();
        
        $statusText = ucfirst($status);
        session()->flash('modal_message', "Redemption status updated to {$statusText}");
        
        // Notify the parent component that the status was updated
        $this->dispatch('redemptionStatusUpdated');
    }
    
    public function render()
    {
        return view('livewire.super-admin.reward-redemptions.modal.reward-redemption-modal');
    }
}
