<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'request_type',
        'status',
        'related_id',
        'related_model',
        'admin_notes',
        'admin_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user who created this support request
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who handled this support request
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Check if the request has been resolved
     */
    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if the request is still pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is in progress
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

}
