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
     * Process voucher when QR code is scanned
     */
    // public function OnScan()
    // {
    //     try {
    //         // Get the voucher
    //         $this->userVoucher->markAsUsed();
            
    //         // IMPORTANT: Also update the parent voucher's availability
    //         $voucher = $this->userVoucher->voucher;
    //         $voucher->availability = 'used';
    //         $voucher->save();
            
    //         // Refresh the voucher data to ensure it shows the latest status
    //         $this->userVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward'])
    //             ->where('id', $this->userVoucherId)
    //             ->first();
            
    //         session()->flash('success', 'Voucher successfully redeemed!');
            
    //         // Emit an event to refresh the parent component - with explicit naming
    //         $this->dispatch('redeemVoucher', $this->userVoucherId);
    //     } catch (\Exception $e) {
    //         session()->flash('error', 'Error redeeming voucher: ' . $e->getMessage());
    //     }
    // }
    
    /**
     * Force refresh the voucher data
     */
    public function refreshVoucherData()
    {
        $this->userVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('id', $this->userVoucherId)
            ->first();
            
        if ($this->userVoucher->status === UserVoucher::STATUS_USED) {
            $this->showQrCodeView = true;
        }
    }

    /**
     * Check the current status of the voucher
     * This method is called periodically via polling
     */
    public function checkVoucherStatus()
    {
        // Get the latest voucher data from the database
        $latestVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('id', $this->userVoucherId)
            ->first();
            
        // Only update if there's been a change in status
        if ($latestVoucher && $latestVoucher->status !== $this->userVoucher->status) {
            $this->userVoucher = $latestVoucher;
            
            // If the status has changed to 'used', ensure we're showing the QR code view
            if ($this->userVoucher->status === UserVoucher::STATUS_USED) {
                $this->showQrCodeView = true;
            }
            
            return true; // Status changed
        }
        
        return false; // No change in status
    }

    public function render()
    {
        return view('livewire.vouchers.modal.show-redeem-voucher');
    }
}
