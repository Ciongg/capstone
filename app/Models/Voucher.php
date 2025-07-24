<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Merchant;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'reward_id',
        'reference_no',
        'store_name',
        'promo',
        'cost',
        'expiry_date',
        'availability',
        'merchant_id',
        'image_path',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    

    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }
}
