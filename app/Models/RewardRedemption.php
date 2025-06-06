<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RewardRedemption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'gcash_number',
        'reward_type',
        'status',
        'quantity'
    ];

    /**
     * Constants for redemption statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the user who redeemed the reward
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward that was redeemed
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Get the user voucher associated with the redemption
     */
    public function userVoucher(): HasOne
    {
        return $this->hasOne(UserVoucher::class);
    }
}
