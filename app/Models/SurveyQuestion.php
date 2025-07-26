<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'survey_page_id',
        'question_text',
        'question_type',
        'order',
        'required',
        'stars', // For rating
        'likert_columns', // For likert
        'likert_rows', // For likert
        // 'limit_answers', // Removed
        'limit_condition', // Added
        'max_answers', // Keep
        'ai_summary',
    ];

    protected $casts = [
        'required' => 'boolean',
        'likert_columns' => 'json',
        'likert_rows' => 'json',
        // 'limit_answers' => 'boolean', // Removed
        // 'likert_columns' => 'array',
        // 'likert_rows' => 'array',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function page()
    {
        return $this->belongsTo(SurveyPage::class, 'survey_page_id');
    }

    public function choices()
    {
        return $this->hasMany(SurveyChoice::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'survey_question_id');
    }
}
