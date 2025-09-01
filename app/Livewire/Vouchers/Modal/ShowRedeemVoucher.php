<?php

namespace App\Livewire\Vouchers\Modal;

use App\Models\UserVoucher;
use Livewire\Component;
use App\Services\TestTimeService;

class ShowRedeemVoucher extends Component
{
    // Public properties for use in the component/view
    public $userVoucherId;
    public $userVoucher;
    public $showQrCodeView = false;
    public $timeRemaining = '';
    public $isExpired = false;
    
    /**
     * Initialize the component with the voucher ID.
     * Loads the voucher and sets up the initial state.
     */
    public function mount($userVoucherId)
    {
        $this->userVoucherId = $userVoucherId;

        // Load the voucher with related data
        $this->userVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('id', $this->userVoucherId)
            ->first();

        // Show QR code view if voucher is active, used, or expired
        if ($this->userVoucher && in_array($this->userVoucher->status, [UserVoucher::STATUS_ACTIVE, UserVoucher::STATUS_USED, UserVoucher::STATUS_EXPIRED])) {
            $this->showQrCodeView = true;
        }

        // If voucher is active, calculate the remaining time
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->calculateTimeRemaining();
        }
    }

    /**
     * Redeem the voucher: activate it and start the timer.
     * Handles different voucher statuses.
     */
    public function redeemVoucher()
    {
        // If voucher is available, activate it and set expiration
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_AVAILABLE) {
            $now = TestTimeService::now();
            //30 minutes expiration timer
            $expiresAt = $now->copy()->addMinutes(30); 
            
            // Update voucher status and times
            $this->userVoucher->status = UserVoucher::STATUS_ACTIVE;
            $this->userVoucher->activated_at = $now;
            $this->userVoucher->expires_at = $expiresAt;
            $this->userVoucher->save();
            
            // Refresh data and update view
            $this->userVoucher->refresh();
            $this->showQrCodeView = true;
            $this->calculateTimeRemaining();
            $this->dispatch('redeemVoucher'); // Notify parent

            //else if voucher is already active/used/expired open and calculate time remaining
        } elseif ($this->userVoucher && in_array($this->userVoucher->status, [UserVoucher::STATUS_ACTIVE, UserVoucher::STATUS_USED, UserVoucher::STATUS_EXPIRED])) {
            // If already active/used/expired, just show QR code
            $this->showQrCodeView = true;
            if ($this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
                $this->calculateTimeRemaining();
            }
        } else {
            // Handle invalid or already used voucher
            session()->flash('error', 'Voucher not found or already used.');
        }
    }

    /**
     * Calculate time remaining for active vouchers.
     * Updates the timer and handles expiration.
     */
    protected function calculateTimeRemaining()
    {
        if (!$this->userVoucher || $this->userVoucher->status !== UserVoucher::STATUS_ACTIVE) {
            $this->timeRemaining = '';
            $this->isExpired = true;
            return;
        }

        $now = TestTimeService::now();
        
        // If expires_at is missing, set it based on activation time
        if (!$this->userVoucher->expires_at && $this->userVoucher->activated_at) {
            $expiresAt = $this->userVoucher->activated_at->copy()->addMinutes(30);
            $this->userVoucher->expires_at = $expiresAt;
            $this->userVoucher->save();
        } elseif (!$this->userVoucher->expires_at) {
            // If both are missing, set them now
            $this->userVoucher->activated_at = $now;
            $this->userVoucher->expires_at = $now->copy()->addMinutes(30);
            $this->userVoucher->save();
        }

        $expiresAt = $this->userVoucher->expires_at;

        // If expired, update status and timer
        if ($now->greaterThanOrEqualTo($expiresAt)) {
            if ($this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
                $this->userVoucher->status = UserVoucher::STATUS_EXPIRED;
                $this->userVoucher->save();
                $this->userVoucher->refresh();
                $this->dispatch('redeemVoucher'); // Notify parent
            }
            $this->timeRemaining = '00:00';
            $this->isExpired = true;
        } else {
            // Calculate minutes and seconds left
            $totalSeconds = $now->diffInSeconds($expiresAt);
            $minutes = floor($totalSeconds / 60);
            $seconds = $totalSeconds % 60;
            $this->timeRemaining = sprintf('%02d:%02d', $minutes, $seconds);
            $this->isExpired = false;
        }
    }

    /**
     * Update the timer every second for active vouchers.
     * Called via polling or JS interval.
     */
    public function updateTimer()
    {
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->calculateTimeRemaining();
            
            // If expired, refresh voucher data
            if ($this->isExpired) {
                $this->userVoucher->refresh();
            }
        }
    }

    /**
     * Periodically check the voucher status from the database.
     * Updates the view if status changes.
     */
    public function checkVoucherStatus()
    {
        // Reload voucher from database
        $latestVoucher = UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('id', $this->userVoucherId)
            ->first();
            
        // If status changed, update local data and view
        if ($latestVoucher && $latestVoucher->status !== $this->userVoucher->status) {
            $this->userVoucher = $latestVoucher;
            
            // If now used or expired, update view
            if (in_array($this->userVoucher->status, [UserVoucher::STATUS_USED, UserVoucher::STATUS_EXPIRED])) {
                $this->showQrCodeView = true;
                $this->timeRemaining = '00:00';
                $this->isExpired = true;
            }
            
            return true; // Status changed
        }

        // If still active, update timer
        if ($this->userVoucher && $this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            $this->calculateTimeRemaining();
        }
        
        return false; // No change in status
    }

    /**
     * Render the Livewire component view.
     */
    public function render()
    {
        return view('livewire.vouchers.modal.show-redeem-voucher');
    }
}
