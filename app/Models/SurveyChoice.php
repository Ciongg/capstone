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
        'order',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
