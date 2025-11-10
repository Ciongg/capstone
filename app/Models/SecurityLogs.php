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

    protected $fillable = [
        'user_id',
        'email',
        'event_type',
        'outcome',
        'ip',
        'route',
        'http_method',
        'http_status',
        'message',
        'meta',
        'geo',
        'user_agent',
    ];

    /**
     * Get the user that owns the security log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
