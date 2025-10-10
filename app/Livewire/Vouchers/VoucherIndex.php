<?php

namespace App\Livewire\Vouchers;

use App\Models\UserVoucher;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\TestTimeService;
class VoucherIndex extends Component
{
    public $selectedVoucher = null;

    protected $listeners = [
        'redeemVoucher' => '$refresh',
      
    ];

    public function openRedeemModal($userVoucherId)
    {
        $userVoucher = UserVoucher::with('voucher')->find($userVoucherId);
        if ($userVoucher && $userVoucher->voucher && $userVoucher->voucher->expiry_date) {
            $now = TestTimeService::now();
            if ($now->greaterThanOrEqualTo($userVoucher->voucher->expiry_date)) {
                // Mark as expired
                $userVoucher->status = UserVoucher::STATUS_EXPIRED;
                $userVoucher->save();
                // Dispatch browser event for SweetAlert2
                $this->dispatch('voucher-expired-alert');
                // Refresh the component to update the list
                $this->dispatch('$refresh');
                return;
            }
        }
        $this->selectedVoucher = $userVoucherId;
        // Use dispatch to ensure the modal event is broadcasted
        $this->dispatch('open-modal', name: 'redeem-voucher-modal');
    }
 

    // Change back to public to make them accessible in the template
    public function getUserVouchersProperty()
    {
        return UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['available', 'active']) 
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Change back to public to make them accessible in the template
    public function getUserVouchersHistoryProperty()
    {
        return UserVoucher::with(['voucher', 'rewardRedemption.reward'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['used', 'expired', 'unavailable'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.vouchers.voucher-index', [
            'userVouchers' => $this->userVouchers,
            'userVouchersHistory' => $this->userVouchersHistory
        ]);
    }
}
