<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserTwoFactorSetting extends Model
{
    protected $fillable = [
        'user_id',
        'enabled',
        'secret',
        'recovery_codes',
        'confirmed_at'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'recovery_codes' => 'array',
        'confirmed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Encrypt secret when setting
    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = $value ? Crypt::encryptString($value) : null;
    }

    // Decrypt secret when getting
    public function getSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
