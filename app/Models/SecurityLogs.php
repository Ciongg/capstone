<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLogs extends Model
{
    protected $table = 'security_logs';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        // Ensure arrays are JSON-encoded/decoded automatically
        'meta' => 'array',
        'geo' => 'array',
        // Helpful type casts
        'created_at' => 'datetime',
        'http_status' => 'integer',
        'resource_id' => 'integer',
        'user_id' => 'integer',
    ];
}
