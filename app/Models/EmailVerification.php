<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    //check if otp expired
    public function isExpired(): bool
    {
        return $this->expires_at->isPast(); // isPast is a carbon function
    }

    //check if otp is valid and not expired
    public function isValid(string $otpCode): bool
    {
        return !$this->isExpired() && $this->otp_code === $otpCode;
        //so return when this this is not expired and this otp code is equal to the otp code passed in
    }
} 