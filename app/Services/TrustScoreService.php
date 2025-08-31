<?php

namespace App\Services;

use App\Models\User;
use App\Models\Report;
use App\Models\Response;
use Illuminate\Support\Facades\Log;

class TrustScoreService
{
     /**
     * Base penalty amount for trust score deductions
     */
    const BASE_PENALTY = -5.0;
    
    /**
     * Threshold for false reports before penalties apply
     */
    const FALSE_REPORT_THRESHOLD = 2;
    
    /**
     * Threshold for reported responses before deductions apply
     */
    const REPORTED_RESPONSE_THRESHOLD = 2;
    
    /**
     * Calculate false report penalty for a reporter
     * 
     */
    public function calculateFalseReportPenalty($reporterId, $dismissedReportCountOverride = null)
    {
        try {
            
            // Get number of dismissed reports by this reporter (false reports)
            $dismissedReports = Report::where('reporter_id', $reporterId)
                ->where('status', 'dismissed')
                ->count();
                
            // Apply override if provided
            if ($dismissedReportCountOverride !== null) {
                $dismissedReports = $dismissedReportCountOverride;
            }
            
            // Get total number of reports initiated by this reporter
            $totalReports = Report::where('reporter_id', $reporterId)->count();
            
            // Calculate percentage
            $falseReportPercentage = ($totalReports > 0) ? ($dismissedReports / $totalReports) * 100 : 0;
            
            // Prepare result object
            $result = [
                'dismissed_reports' => $dismissedReports,
                'total_reports' => $totalReports,
                'percentage' => round($falseReportPercentage, 1),
                'threshold_met' => false,
                'penalty_amount' => 0,
            ];
            
            // Check threshold
            if ($dismissedReports <= self::FALSE_REPORT_THRESHOLD) {
             
                return $result;
            }
            
            // Threshold met
            $result['threshold_met'] = true;
            
            // Calculate modifier based on the percentage
            $modifier = $this->calculateModifier($falseReportPercentage);
            
            // Apply modifier to the base penalty
            $finalPenalty = self::BASE_PENALTY * $modifier;
            $result['penalty_amount'] = round($finalPenalty, 2);
            $result['modifier'] = $modifier;
            

            
            return $result;
        } catch (Exception $e) {
            Log::error('Error calculating false report penalty', [
                'reporter_id' => $reporterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback result with no penalty
            return [
                'dismissed_reports' => 0,
                'total_reports' => 0,
                'percentage' => 0,
                'threshold_met' => false,
                'penalty_amount' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate trust score deduction based on user's reported response history
    */
   
    public function calculateReportedResponseDeduction($userId, $reportCount = null)
    {
        try {
          
            
            // Get number of valid reports against this user (confirmed or unappealed)
            $validReports = $reportCount ?? Report::where('respondent_id', $userId)
                ->whereIn('status', ['confirmed', 'unappealed', 'under_appeal'])
                ->count();
                
            // Apply override if provided
            if ($reportCount !== null) {
                $validReports = $reportCount;
            }
            
            // Get total number of responses by this user (all responses, not just reported ones)
            $totalResponses = Response::where('user_id', $userId)->count();
            
            // Calculate percentage
            $reportedPercentage = ($totalResponses > 0) ? ($validReports / $totalResponses) * 100 : 0;
            
            // Prepare result object
            $result = [
                'valid_reports' => $validReports,
                'total_responses' => $totalResponses,
                'percentage' => round($reportedPercentage, 1),
                'threshold_met' => false,
                'penalty_amount' => 0,
                
            ];
            
            // Check threshold
            if ($validReports <= self::REPORTED_RESPONSE_THRESHOLD) {
              
                return $result;
            }
            
            // Threshold met
            $result['threshold_met'] = true;
            
            // Calculate modifier based on the percentage
            $modifier = $this->calculateModifier($reportedPercentage);
            
            // Apply modifier to the base deduction
            $finalDeduction = self::BASE_PENALTY * $modifier;
            $result['penalty_amount'] = round($finalDeduction, 2);
            $result['modifier'] = $modifier;
            
        
            
            return $result;
        } catch (Exception $e) {
            Log::error('Error calculating reported response deduction', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback result with no penalty
            return [
                'valid_reports' => 0,
                'total_responses' => 0,
                'percentage' => 0,
                'threshold_met' => false,
                'penalty_amount' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate modifier based on percentage
     */
    private function calculateModifier($percentage)
    {
        if ($percentage < 5) {
            return 0.5; // Less than 5% - reduced penalty
        } elseif ($percentage > 20) {
            return 1.5; // More than 20% - increased penalty
        } else {
            return 1.0; // Default modifier 
        }
    }
    
    /**
     * Get ordinal suffix for a number (1st, 2nd, 3rd, etc.)
     */
    public function getOrdinal($number)
    {
        $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
        
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number. 'th';
        } else {
            return $number. $ends[$number % 10];
        }
    }
}
