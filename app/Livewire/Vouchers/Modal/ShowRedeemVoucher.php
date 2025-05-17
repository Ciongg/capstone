<?php

namespace App\Livewire\Vouchers\Modal;

use App\Models\UserVoucher;
use Livewire\Component;

class ShowRedeemVoucher extends Component
{
    public $userVoucherId;
    public $userVoucher;
    public $showQrCodeView = false;
    
    public function mount($userVoucherId)
    {
        $this->userVoucherId = $userVoucherId;
        $this->loadUserVoucher();

        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->showQrCodeView = true;
        }
    }
    
    public function loadUserVoucher()
    {
        $this->userVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward']) // Changed from redemption to rewardRedemption
            ->where('id', $this->userVoucherId)
            ->first();
    }
    
    public function redeemVoucher()
    {
        // Change status from available to active
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_AVAILABLE) {
            $this->userVoucher->markAsActive();
            $this->showQrCodeView = true;
            $this->dispatch('redeemVoucher'); // Dispatch event to refresh parent
        } elseif ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            // Allow viewing QR even if already active
            $this->showQrCodeView = true;
            // Optionally dispatch refresh here too if needed, though status isn't changing
            // $this->dispatch('redeemVoucher'); 
        } else {
            // Handle error case
            session()->flash('error', 'Voucher not found or already used.');
        }
    }
    
    public function simulateScan()
    {
        // Test button action to change from active to used
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->userVoucher->markAsUsed();
            $this->dispatch('redeemVoucher'); // Refresh parent voucher list
            session()->flash('success', 'Voucher has been marked as used successfully.');
        } else {
            session()->flash('error', 'Voucher cannot be used at this time.');
        }
        
        // Reload the voucher data to see the updated status
        $this->loadUserVoucher();
    }
    
    public function render()
    {
        return view('livewire.vouchers.modal.show-redeem-voucher');
    }
}
