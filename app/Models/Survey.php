<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasUuid;

class Survey extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'status',   // 'pending', 'published', 'ongoing', 'finished'
        'type',     // 'basic', 'advanced'
        'survey_topic_id', 
        'target_respondents',
        'start_date',
        'end_date',
        'points_allocated',
        'image_path',
        'is_institution_only',
        'is_locked', 
        'lock_reason',
        'is_announced', // Add this to fillable
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
        'is_announced' => 'boolean', // Add this to cast attributes
        'target_respondents' => 'integer',
        'points_allocated' => 'integer',
        'boost_count' => 'integer',
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
        return $this->hasMany(Response::class);
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

    /**
     * Get the announcements for this survey
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Relationship with collaborators (users who can edit this survey)
     */
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'survey_collaborators')
                    ->withPivot('user_uuid')
                    ->withTimestamps();
    }

    /**
     * Check if the given user is a collaborator on this survey
     */
    public function isCollaborator(User $user)
    {
        return $this->collaborators()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the survey snapshot.
     */
    public function snapshot()
    {
        return $this->hasOne(SurveySnapshot::class);
    }
}
