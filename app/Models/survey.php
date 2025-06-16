<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

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
        'is_locked', // Added for lock/unlock functionality
        'lock_reason', // Added to store the reason for locking
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_institution_only' => 'boolean',
        'is_locked' => 'boolean',
        'points' => 'decimal:2', // Assuming points can have decimals, adjust if not
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

    /**
     * Check if the survey is locked
     */
    public function isLocked()
    {
        return $this->is_locked === true;
    }

    /**
     * Check if the survey can be appealed (must be locked)
     */
    public function canBeAppealed()
    {
        return $this->isLocked();
    }
}
