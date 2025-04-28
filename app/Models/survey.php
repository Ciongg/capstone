<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',   // 'wip', 'published', 'finished'
        'type',     // 'basic', 'advanced'
    ];

    public function pages()
    {
        return $this->hasMany(SurveyPage::class);
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
