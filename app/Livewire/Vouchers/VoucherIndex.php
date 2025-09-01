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
        //populate the selected voucher details eager loading voucher relationship
        $userVoucher = UserVoucher::with('voucher')->find($userVoucherId);


        if ($userVoucher && $userVoucher->voucher && $userVoucher->voucher->expiry_date) {
            $now = TestTimeService::now();
            // Check if the voucher has expired
            if ($now->greaterThanOrEqualTo($userVoucher->voucher->expiry_date)) {
                // Mark as expired
                $userVoucher->status = UserVoucher::STATUS_EXPIRED;
                $userVoucher->save();

                // Dispatch browser event for alert
                $this->dispatch('voucher-expired-alert');
                // Refresh the component to update the list
                $this->dispatch('$refresh');
                return;
            }
        }
        
        //pass this to the modal to show data to that specific voucher opened
        $this->selectedVoucher = $userVoucherId;
        // Use dispatch to ensure the modal event is broadcasted
        $this->dispatch('open-modal', name: 'redeem-voucher-modal');
    }
 

    public function render()
    {
        return view('livewire.vouchers.voucher-index', [
            'userVouchers' => $this->userVouchers,
            'userVouchersHistory' => $this->userVouchersHistory
        ]);
    }
}
