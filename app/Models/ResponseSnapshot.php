<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseSnapshot extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'response_id',
        'first_name',
        'last_name',
        'trust_score',
        'points',
        'account_level',
        'experience_points',
        'rank',
        'title',
        'started_at',
        'completed_at',
        'completion_time_seconds',
        'demographic_tags'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'trust_score' => 'decimal:2',
        'points' => 'decimal:2',
        'experience_points' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'completion_time_seconds' => 'integer',
        'demographic_tags' => 'json'
    ];
    
    /**
     * Get the response that owns the snapshot.
     */
    public function response()
    {
        return $this->belongsTo(Response::class);
    }
}
