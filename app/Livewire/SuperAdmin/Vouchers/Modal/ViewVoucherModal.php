<?php

namespace App\Livewire\SuperAdmin\Vouchers\Modal;

use Livewire\Component;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;

class ViewVoucherModal extends Component
{
    public $voucherId;
    public $voucher;
    public $availability;
    public $selectedStatus;
    
    // Add the hook for when voucherId is updated
    public function updated($property)
    {
        if ($property === 'voucherId') {
            $this->loadVoucher();
        }
    }
    
    public function mount($voucherId = null)
    {
        if ($voucherId) {
            $this->voucherId = $voucherId;
            $this->loadVoucher();
        }
    }
    
    public function loadVoucher()
    {
        if ($this->voucherId) {
            $this->voucher = Voucher::with('reward')->find($this->voucherId);
            if ($this->voucher) {
                $this->availability = $this->voucher->availability;
                $this->selectedStatus = $this->voucher->availability;
            }
        }
    }
    
    public function updateStatus($status)
    {
        $this->selectedStatus = $status;
        $this->updateVoucher();
    }
    
    public function updateVoucher()
    {
        $this->validate([
            'selectedStatus' => 'required|in:available,unavailable,expired,used',
        ]);
        
        if (!$this->voucher) {
            return;
        }
        
        $this->voucher->availability = $this->selectedStatus;
        $this->voucher->save();
        
        // Dispatch events to update UI and notify parent components
        $this->dispatch('notify', [
            'message' => "Voucher status updated to " . ucfirst($this->selectedStatus),
            'type' => 'success'
        ]);
        
        // Dispatch event to refresh the voucher list
        $this->dispatch('voucherStatusUpdated');
    }
    
    public function render()
    {
        return view('livewire.super-admin.vouchers.modal.view-voucher-modal');
    }
}
