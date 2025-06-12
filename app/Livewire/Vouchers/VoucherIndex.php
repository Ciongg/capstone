<?php

namespace App\Livewire\Vouchers;

use App\Models\UserVoucher;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VoucherIndex extends Component
{
    public $selectedVoucher = null;

    protected $listeners = [
        'redeemVoucher' => '$refresh',
      
    ];

    public function openRedeemModal($userVoucherId)
    {
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
