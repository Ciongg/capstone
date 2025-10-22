<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveySnapshot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'title',
        'description',
        'target_respondents',
        'points_allocated',
        'demographic_tags', // JSON representation of survey tags
        'first_response_at',
        'metadata' // Additional survey data that might be useful to preserve
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'demographic_tags' => 'json',
        'metadata' => 'json',
        'first_response_at' => 'datetime',
        'target_respondents' => 'integer',
        'points_allocated' => 'decimal:2'
    ];

    /**
     * Get the survey that owns the snapshot.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
