<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasUuid;

class Report extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'survey_id',
        'response_id',
        'reporter_id',
        'respondent_id',
        'question_id',
        'reason',
        'details',
        'status',
        'trust_score_deduction',
        'deduction_reversed',
        'reporter_trust_score_deduction',
        'points_deducted',
        'points_restored'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deduction_reversed' => 'boolean',
        'points_restored' => 'boolean',
    ];

    // Status constants
    // centralized reference for status values instead of hardcoding strings throughout the codebase
    
    const STATUS_PENDING = 'pending';
    const STATUS_UNAPPEALED = 'unappealed';
    const STATUS_UNDER_APPEAL = 'under_appeal';
    const STATUS_DISMISSED = 'dismissed';
    const STATUS_CONFIRMED = 'confirmed';

    //business logic methods / helper methods
    //instead of $report->status === 'unappealed', use the function for better maintainability

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


    /**
     * Mark the report as dismissed (appeal successful)
     */
    public function markAsDismissed()
    {
        $this->status = 'dismissed';
        $this->save();
        
        return $this; 
    }

    /**
     * Mark the report as confirmed (appeal rejected)
     */
    public function markAsConfirmed()
    {
        $this->status = 'confirmed';
        $this->save();
        
        return $this;
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
        return $this->belongsTo(SurveyQuestion::class);
    }

}
