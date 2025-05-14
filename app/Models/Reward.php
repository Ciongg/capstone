<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'cost',
        'quantity',
        'type',
        'description',
        // 'image_path', // Commented out for now
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
     * Get the formatted cost with points symbol
     *
     * @return string
     */
    public function getFormattedCostAttribute(): string
    {
        return $this->cost . ' pts';
    }

    /**
     * Get full image URL
     *
     * @return string|null
     */
    /* // Commented out for now
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
    */

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
}
