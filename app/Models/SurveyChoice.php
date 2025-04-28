<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_question_id',
        'choice_text',
    ];

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class);
    }
}
