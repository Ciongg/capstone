<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'survey_page_id',
        'question_text',
        'question_type',   // 'essay', 'multiple_choice', 'page', 'date', 'likert', 'radio', 'rating', 'short_text'
        'order',
        'required',
        'stars',
        'likert_columns',
        'likert_rows',
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

    // Add this:
    public function answers()
    {
        return $this->hasMany(Answer::class, 'survey_question_id');
    }
}
