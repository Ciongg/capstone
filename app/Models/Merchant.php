<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'merchant_code',
        'logo_path',
        'description',
        'email',
        'contact_number',
        'partner_type',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}