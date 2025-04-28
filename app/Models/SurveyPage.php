<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SurveyPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'page_number',
        'title',
        'subtitle',
        
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class);
    }
}
