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
        'status',   // 'pending', 'published', 'ongoing', 'finished'
        'type',     // 'basic', 'advanced'
        'survey_topic_id', // Added survey topic
        'target_respondents',
        'start_date',
        'end_date',
        'points_allocated',
        'image_path',
        'is_institution_only',
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

    public function responses()
    {
        return $this->hasMany(\App\Models\Response::class);
    }

    /**
     * Get the tags associated with the survey.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'survey_tag')
            ->withPivot('tag_name')
            ->withTimestamps();
    }

    /**
     * Get the institution tags associated with the survey.
     */
    public function institutionTags()
    {
        return $this->belongsToMany(InstitutionTag::class, 'institution_survey_tags')
                    ->withPivot('tag_name')
                    ->withTimestamps();
    }

    /**
     * Get the topic associated with the survey.
     */
    public function topic()
    {
        return $this->belongsTo(SurveyTopic::class, 'survey_topic_id');
    }
}
