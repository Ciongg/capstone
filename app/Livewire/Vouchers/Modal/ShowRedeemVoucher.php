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

        $this->userVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward']) // Changed from redemption to rewardRedemption
            ->where('id', $this->userVoucherId)
            ->first();

        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->showQrCodeView = true;
        }
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

        } else {
            // Handle error case
            session()->flash('error', 'Voucher not found or already used.');
        }
    }
     
    /**
     * Simulate a scan of the QR code (for testing)
     */
    public function simulateScan()
    {
        try {
            // Get the voucher
            $this->userVoucher->markAsUsed();
            
            // IMPORTANT: Also update the parent voucher's availability
            $voucher = $this->userVoucher->voucher;
            $voucher->availability = 'used';
            $voucher->save();
            
            session()->flash('success', 'Voucher successfully redeemed!');
            
            // Emit an event to refresh the parent component - with explicit naming
            $this->dispatch('voucherRedeemed', $this->userVoucherId);
            
            // Refresh to show updated status
            $this->loadUserVoucher();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error redeeming voucher: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.vouchers.modal.show-redeem-voucher');
    }
}
