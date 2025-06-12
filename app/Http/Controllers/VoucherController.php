<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Verify a voucher by its reference number
     * This is a public route that can be accessed by scanning a QR code
     */
    public function verify($reference_no)
    {
        // Find the voucher and associated user voucher for meta tags
        $voucher = Voucher::where('reference_no', $reference_no)->first();
        $userVoucher = null;
        
        if ($voucher) {
            $userVoucher = UserVoucher::where('voucher_id', $voucher->id)->first();
        }
        
        return view('vouchers.show-voucher-status', [
            'reference_no' => $reference_no,
            'voucher' => $voucher,
            'userVoucher' => $userVoucher,
            'title' => 'Voucher Verification'
        ]);
    }
}
