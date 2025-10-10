<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyGenerationJob extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'survey_id',
        'status',
        'result',
        'user_id',
    ];
    
    /**
     * Get the survey that owns the job.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
    
    /**
     * Get the user that created the job.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
