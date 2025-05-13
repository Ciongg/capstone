<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyTopic extends Model
{
    use HasFactory;

    protected $table = 'survey_topic';
    
    protected $fillable = ['name'];

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }
}
