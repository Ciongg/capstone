<?php

namespace App\Livewire\SuperAdmin\Vouchers\Modal;

use Livewire\Component;
use App\Models\Voucher;
use App\Models\UserVoucher;
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
        
        // Store the old status for comparison
        $oldStatus = $this->voucher->availability;
        
        // Update the voucher status
        $this->voucher->availability = $this->selectedStatus;
        $this->voucher->save();
        
        // Also update any associated UserVoucher records for consistency
        $userVouchers = $this->voucher->userVouchers()->get();
        $updatedUserVouchers = 0;
        
        if ($userVouchers->count() > 0) {
            foreach ($userVouchers as $userVoucher) {
                // Map Voucher status to UserVoucher status
                $newUserVoucherStatus = $this->mapVoucherStatusToUserVoucherStatus($this->selectedStatus);
                
                // Only update if there's a status change needed
                if ($userVoucher->status !== $newUserVoucherStatus) {
                    $userVoucher->status = $newUserVoucherStatus;
                    
                    // If status is used, set the used_at timestamp
                    if ($newUserVoucherStatus === UserVoucher::STATUS_USED && !$userVoucher->used_at) {
                        $userVoucher->used_at = now();
                    }
                    
                    // If status is expired, we might want to record when it expired
                    // (optional, depends on your requirements)
                    if ($newUserVoucherStatus === UserVoucher::STATUS_EXPIRED) {
                        $userVoucher->expires_at = now();
                    }
                    
                    $userVoucher->save();
                    $updatedUserVouchers++;
                }
            }
        }
        
        // Create appropriate notification message
        $message = "Voucher status updated to " . ucfirst($this->selectedStatus);
        if ($updatedUserVouchers > 0) {
            $message .= " (Also updated $updatedUserVouchers user vouchers)";
        }
        
        // Dispatch events to update UI and notify parent components
        $this->dispatch('notify', [
            'message' => $message,
            'type' => 'success'
        ]);
        
        // Dispatch event to refresh the voucher list
        $this->dispatch('voucherStatusUpdated');
    }
    
    /**
     * Map Voucher status to appropriate UserVoucher status
     * 
     * @param string $voucherStatus
     * @return string
     */
    private function mapVoucherStatusToUserVoucherStatus($voucherStatus)
    {
        switch ($voucherStatus) {
            case 'available':
                return UserVoucher::STATUS_AVAILABLE;
            case 'unavailable':
                return UserVoucher::STATUS_UNAVAILABLE;
            case 'expired':
                return UserVoucher::STATUS_EXPIRED;
            case 'used':
                return UserVoucher::STATUS_USED;
            default:
                return UserVoucher::STATUS_UNAVAILABLE;
        }
    }
    
    public function render()
    {
        return view('livewire.super-admin.vouchers.modal.view-voucher-modal');
    }
}
