<?php

namespace App\Livewire\Vouchers;

use App\Models\Voucher;
use App\Models\UserVoucher;
use Livewire\Component;
use App\Services\TestTimeService;

class VoucherVerify extends Component
{
    public $referenceNo;
    public $voucher;
    public $userVoucher;
    
    public $valid = false;
    public $message;
    public $usedAt = null;
    public $redeemed = false; // Track if we've redeemed this voucher in this session
    
    public $merchantCodeInput;
    public $merchantCodeValidated = false;
    
    public function mount($reference_no)
    {
        $this->referenceNo = $reference_no;
    }

    public function submitMerchantCode()
    {
        // Find the voucher by reference number
        $this->voucher = Voucher::where('reference_no', $this->referenceNo)->first();
        if (!$this->voucher) {
            $this->valid = false;
            $this->message = 'Invalid voucher! This voucher does not exist.';
            return;
        }
        // if a voucher has a non existent merchant
        $merchant = $this->voucher->merchant;
        if (!$merchant) {
            $this->valid = false;
            $this->message = 'No merchant associated with this voucher.';
            return;
        }

        // if the merchant code is wrong
        if (trim($this->merchantCodeInput) !== $merchant->merchant_code) {
            $this->valid = false;
            $this->message = 'Incorrect merchant code for this voucher. Please check the code and try again.';
            return;
        }

        //else it's valid

        $this->merchantCodeValidated = true;
        $this->verifyVoucher();
    }
    
    public function verifyVoucher()
    {
        // Find the voucher by reference number
        $this->voucher = Voucher::where('reference_no', $this->referenceNo)->first();
        
        //if there is no voucher
        if (!$this->voucher) {
            $this->valid = false;
            $this->message = 'Invalid voucher! This voucher does not exist.';
            return;
        }
        
        // Get the user voucher associated with this voucher
        $this->userVoucher = UserVoucher::where('voucher_id', $this->voucher->id)->first();
        
        //if voucher is not associated to any user
        if (!$this->userVoucher) {
            $this->valid = false;
            $this->message = 'Invalid voucher! This voucher is not assigned to any user.';
            return;
        }
        
        // Check if it's the first time being scanned
        if ($this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            // Check for expiry based on activation time (30 min window)
            $now = TestTimeService::now();

            //if uservoucher is activated then check if it's over 30 minutes based on activated at time.
            if ($this->userVoucher->activated_at) {
                $activatedAt = $this->userVoucher->activated_at;
                if ($now->diffInMinutes($activatedAt) >= 30) {

                    // Mark user's voucher as expired
                    $this->userVoucher->status = UserVoucher::STATUS_EXPIRED;
                    $this->userVoucher->save();

                     //set the voucher availability to expired as well
                    $this->voucher->availability = 'expired';
                    $this->voucher->save();

                    $this->valid = false;
                    $this->message = 'Invalid! This voucher has expired (over 30 minutes since activation).';
                    return;
                }
            }
            // Check for voucher expiry date as well
            if ($this->voucher->expiry_date && $now->gt($this->voucher->expiry_date)) {
                $this->userVoucher->status = UserVoucher::STATUS_EXPIRED;
                $this->userVoucher->save();

                $this->voucher->availability = 'expired';
                $this->voucher->save();

                $this->valid = false;
                $this->message = 'Invalid! This voucher has expired.';
                return;
            }


            // Mark as used on first visit
            $this->userVoucher->markAsUsed();
            $this->voucher->availability = 'used';
            $this->voucher->save();
            $this->valid = true;
            $this->message = 'Valid! This voucher is real and has been marked as used.';
            $this->redeemed = true;
        //if voucher already used before
        } else if ($this->userVoucher->status === UserVoucher::STATUS_USED) {
            $this->valid = false;
            $this->message = 'Invalid! This voucher was used before.';
            $this->usedAt = $this->userVoucher->used_at;
        // if voucher is not activated yet by user
        } else {
            $this->valid = false;
            $this->message = 'Invalid! This voucher is not active.';
        }
    }
    
    public function render()
    {
        return view('livewire.vouchers.voucher-verify');
    }
}
