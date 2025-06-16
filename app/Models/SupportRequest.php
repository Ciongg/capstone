<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'request_type',
        'status',
        'related_id',
        'related_model',
        'admin_notes',
        'admin_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user who created this support request
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who handled this support request
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Check if the request has been resolved
     */
    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if the request is still pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is in progress
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get related model instance if available
     */
    public function getRelatedModel()
    {
        if ($this->related_id && $this->related_model) {
            $modelClass = '\\App\\Models\\' . $this->related_model;
            if (class_exists($modelClass)) {
                return $modelClass::find($this->related_id);
            }
        }
        
        return null;
    }

    /**
     * Validate if a survey lock appeal is valid
     */
    public static function canCreateSurveyLockAppeal($surveyId)
    {
        $survey = Survey::find($surveyId);
        
        if (!$survey) {
            return false;
        }
        
        return $survey->canBeAppealed();
    }

    /**
     * Validate if a report appeal is valid
     */
    public static function canCreateReportAppeal($reportId)
    {
        $report = Report::find($reportId);
        
        if (!$report) {
            return false;
        }
        
        return $report->canBeAppealed();
    }

    /**
     * Validate if the support request can be created based on type and related model
     */
    public static function validateAppeal($requestType, $relatedId, $relatedModel)
    {
        if ($requestType === 'survey_lock_appeal' && $relatedModel === 'Survey') {
            return self::canCreateSurveyLockAppeal($relatedId);
        }
        
        if ($requestType === 'report_appeal' && $relatedModel === 'Report') {
            return self::canCreateReportAppeal($relatedId);
        }
        
        return true; // For other request types, allow creation
    }
}
