<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'cost',
        'quantity',
        'type',
        'description',
        'image_path', // Uncommented to allow updating image
    ];

    /**
     * Reward status constants
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_SOLD_OUT = 'sold_out';

    /**
     * Reward type constants
     */
    const TYPE_VOUCHER = 'Voucher';
    const TYPE_SYSTEM = 'System';
    const TYPE_MONETARY = 'Monetary';

    /**
     * Check if the reward is available for redemption
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->quantity > 0;
    }



    /**
     * Decrement quantity when redeemed
     *
     * @param int $amount
     * @return void
     */
    public function redeem(int $amount = 1): void
    {
        $this->quantity = max(0, $this->quantity - $amount);
        
        if ($this->quantity === 0) {
            $this->status = self::STATUS_SOLD_OUT;
        }
        
        $this->save();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Get the vouchers associated with this reward.
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
