<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVoucher extends Model
{
    use HasFactory;

    // Add status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_ACTIVE = 'active';
    const STATUS_USED = 'used';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'reward_redemption_id',
        'status',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function rewardRedemption(): BelongsTo
    {
        return $this->belongsTo(RewardRedemption::class);
    }

    /**
     * Mark voucher as active when QR code is generated
     */
    public function markAsActive()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        return $this;
    }

    /**
     * Mark voucher as used when scanned
     */
    public function markAsUsed()
    {
        $this->status = self::STATUS_USED;
        $this->used_at = now();
        $this->save();
        return $this;
    }
}
