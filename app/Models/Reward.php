<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Merchant;

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
        'image_path', 
        'rank_requirement',
        'merchant_id',
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

    /**
     * Check if the reward is available for redemption
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->quantity > 0;
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

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the earliest expiry date from all available vouchers of this reward
     * 
     * @return \Carbon\Carbon|null
     */
    public function getEarliestExpiryDate()
    {
        if ($this->type !== 'voucher' && $this->type !== 'Voucher') {
            return null;
        }
        
        return $this->vouchers()
            ->where('availability', 'available')
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->first()?->expiry_date;
    }
}
