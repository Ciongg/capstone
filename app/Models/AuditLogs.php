<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogs extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        // Ensure arrays are JSON-encoded/decoded automatically
        'before' => 'array',
        'after' => 'array',
        'changed_fields' => 'array',
        'meta' => 'array',
        // Helpful type casts
        'created_at' => 'datetime',
        'resource_id' => 'integer',
        'performed_by' => 'integer',
    ];
}
