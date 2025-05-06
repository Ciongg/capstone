<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['response_id', 'survey_question_id', 'answer', 'other_text'];

    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
