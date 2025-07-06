<?php

namespace App\Livewire\Vouchers\Modal;

use App\Models\UserVoucher;
use Livewire\Component;
use App\Services\TestTimeService;

class ShowRedeemVoucher extends Component
{
    public $userVoucherId;
    public $userVoucher;
    public $showQrCodeView = false;
    public $timeRemaining = '';
    public $isExpired = false;
    
    public function mount($userVoucherId)
    {
        $this->userVoucherId = $userVoucherId;

        $this->userVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('id', $this->userVoucherId)
            ->first();

        if ($this->userVoucher && in_array($this->userVoucher->status, [UserVoucher::STATUS_ACTIVE, UserVoucher::STATUS_USED, UserVoucher::STATUS_EXPIRED])) {
            $this->showQrCodeView = true;
        }

        // Calculate initial time remaining if voucher is active
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->calculateTimeRemaining();
        }
    }

    public function redeemVoucher()
    {
        // Change status from available to active
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_AVAILABLE) {
            $now = TestTimeService::now();
            $expiresAt = $now->copy()->addMinutes(30);
            
            // Update the user voucher with activation time and expiration
            $this->userVoucher->status = UserVoucher::STATUS_ACTIVE;
            $this->userVoucher->activated_at = $now;
            $this->userVoucher->expires_at = $expiresAt;
            $this->userVoucher->save();
            
            // Refresh to get the latest data
            $this->userVoucher->refresh();
            $this->showQrCodeView = true;
            $this->calculateTimeRemaining();
            $this->dispatch('redeemVoucher'); // Dispatch event to refresh parent
        } elseif ($this->userVoucher && in_array($this->userVoucher->status, [UserVoucher::STATUS_ACTIVE, UserVoucher::STATUS_USED, UserVoucher::STATUS_EXPIRED])) {
            // Allow viewing QR even if already active, used, or expired
            $this->showQrCodeView = true;
            if ($this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
                $this->calculateTimeRemaining();
            }
        } else {
            // Handle error case
            session()->flash('error', 'Voucher not found or already used.');
        }
    }

    /**
     * Calculate time remaining for active vouchers
     */
    protected function calculateTimeRemaining()
    {
        if (!$this->userVoucher || $this->userVoucher->status !== UserVoucher::STATUS_ACTIVE) {
            $this->timeRemaining = '';
            $this->isExpired = true;
            return;
        }

        $now = TestTimeService::now();
        
        // If expires_at is not set, calculate it from activated_at
        if (!$this->userVoucher->expires_at && $this->userVoucher->activated_at) {
            $expiresAt = $this->userVoucher->activated_at->copy()->addMinutes(30);
            $this->userVoucher->expires_at = $expiresAt;
            $this->userVoucher->save();
        } elseif (!$this->userVoucher->expires_at) {
            // If neither activated_at nor expires_at exist, set them now
            $this->userVoucher->activated_at = $now;
            $this->userVoucher->expires_at = $now->copy()->addMinutes(30);
            $this->userVoucher->save();
        }

        $expiresAt = $this->userVoucher->expires_at;

        if ($now->greaterThanOrEqualTo($expiresAt)) {
            // Timer has expired, mark as expired if not already used
            if ($this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
                $this->userVoucher->status = UserVoucher::STATUS_EXPIRED;
                $this->userVoucher->save();
                $this->userVoucher->refresh();
            }
            $this->timeRemaining = '00:00';
            $this->isExpired = true;
        } else {
            // Calculate remaining time
            $totalSeconds = $now->diffInSeconds($expiresAt);
            $minutes = floor($totalSeconds / 60);
            $seconds = $totalSeconds % 60;
            $this->timeRemaining = sprintf('%02d:%02d', $minutes, $seconds);
            $this->isExpired = false;
        }
    }

    /**
     * Update timer every second for active vouchers
     */
    public function updateTimer()
    {
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->calculateTimeRemaining();
            
            // If expired during timer update, refresh the voucher data
            if ($this->isExpired) {
                $this->userVoucher->refresh();
            }
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
            if (in_array($this->userVoucher->status, [UserVoucher::STATUS_USED, UserVoucher::STATUS_EXPIRED])) {
                $this->showQrCodeView = true;
                $this->timeRemaining = '00:00';
                $this->isExpired = true;
            }
            
            return true; // Status changed
        }

        // Update timer for active vouchers
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->calculateTimeRemaining();
        }
        
        return false; // No change in status
    }

    public function render()
    {
        return view('livewire.vouchers.modal.show-redeem-voucher');
    }
}
