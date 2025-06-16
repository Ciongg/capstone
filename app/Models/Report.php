<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'response_id',
        'reporter_id',
        'respondent_id',
        'question_id',
        'reason',
        'details',
        'status',

    ];

    // Status constants
    const STATUS_UNAPPEALED = 'unappealed';
    const STATUS_UNDER_APPEAL = 'under_appeal';
    const STATUS_DISMISSED = 'dismissed';
    const STATUS_CONFIRMED = 'confirmed';

    public function canBeAppealed()
    {
        return $this->status === self::STATUS_UNAPPEALED;
    }

     public function isUnderAppeal()
    {
        return $this->status === self::STATUS_UNDER_APPEAL;
    }


     public function markAsUnderAppeal()
    {
        $this->update(['status' => self::STATUS_UNDER_APPEAL]);
    }


    public function markAsDismissed()
    {
        $this->update(['status' => self::STATUS_DISMISSED]);
    }

    public function markAsConfirmed()
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }




    /**
     * Get the survey associated with the report.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the response associated with the report.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * Get the user who created the report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user who was reported.
     */
    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    /**
     * Get the question associated with the report.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }

    /**
     * Get the user who reviewed the report.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
