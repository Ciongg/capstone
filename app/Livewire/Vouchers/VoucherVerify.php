<?php

namespace App\Livewire\Vouchers;

use App\Models\Voucher;
use App\Models\UserVoucher;
use Livewire\Component;

class VoucherVerify extends Component
{
    public $referenceNo;
    public $voucher;
    public $userVoucher;
    public $valid = false;
    public $message;
    public $usedAt = null;
    public $redeemed = false; // Track if we've redeemed this voucher in this session
    
    public function mount($reference_no)
    {
        $this->referenceNo = $reference_no;
        $this->verifyVoucher();
    }
    
    public function verifyVoucher()
    {
        // Find the voucher by reference number
        $this->voucher = Voucher::where('reference_no', $this->referenceNo)->first();
        
        if (!$this->voucher) {
            $this->valid = false;
            $this->message = 'Invalid voucher! This voucher does not exist.';
            return;
        }
        
        // Get the user voucher associated with this voucher
        $this->userVoucher = UserVoucher::where('voucher_id', $this->voucher->id)->first();
        
        if (!$this->userVoucher) {
            $this->valid = false;
            $this->message = 'Invalid voucher! This voucher is not assigned to any user.';
            return;
        }
        
        // Check if it's the first time being scanned
        if ($this->userVoucher->status === UserVoucher::STATUS_ACTIVE) {
            // Mark as used on first visit
            $this->userVoucher->markAsUsed();
            $this->voucher->availability = 'used';
            $this->voucher->save();
            
            $this->valid = true;
            $this->message = 'Valid! This voucher is real and has been marked as used.';
            $this->redeemed = true;
        } else if ($this->userVoucher->status === UserVoucher::STATUS_USED) {
            $this->valid = false;
            $this->message = 'Invalid! This voucher was used before.';
            $this->usedAt = $this->userVoucher->used_at;
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
