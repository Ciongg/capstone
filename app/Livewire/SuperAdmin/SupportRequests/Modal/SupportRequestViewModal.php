<?php

namespace App\Livewire\SuperAdmin\SupportRequests\Modal;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Models\Survey;
use App\Models\Report;
use App\Models\User;
use App\Models\InboxMessage;
use App\Models\Response; // Import Response model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TrustScoreService; 
class SupportRequestViewModal extends Component
{
    public $requestId;
    public $supportRequest;
    public $adminNotes;
    public $status;
    public $relatedItem = null;
    public $relatedItemTitle = null;
    private TrustScoreService $trustScoreService;

    protected $rules = [
        'adminNotes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,resolved,rejected',
    ];


    public function boot(TrustScoreService $trustScoreService)
    {
        $this->trustScoreService = $trustScoreService;
    }

    public function mount($requestId)
    {
        $this->requestId = $requestId;
        $this->loadSupportRequest();
    }

    public function loadSupportRequest()
    {
        //grabs all supportRequest and its related models (user and admin) eager loaded
        $this->supportRequest = SupportRequest::with(['user', 'admin'])
            ->findOrFail($this->requestId);
            
        $this->adminNotes = $this->supportRequest->admin_notes;
        $this->status = $this->supportRequest->status;
        
        // Load related item based on request type if looking at survey lock appeal or report appeal
        if ($this->supportRequest->related_id && $this->supportRequest->related_model) {
            if ($this->supportRequest->request_type === 'survey_lock_appeal' && $this->supportRequest->related_model === 'Survey') {
                $this->relatedItem = Survey::find($this->supportRequest->related_id);
                $this->relatedItemTitle = $this->relatedItem ? $this->relatedItem->title : 'Survey not found';
            } elseif ($this->supportRequest->request_type === 'report_appeal' && $this->supportRequest->related_model === 'Report') {
                // Update to find report by UUID for better security
                $this->relatedItem = Report::with(['survey', 'reporter', 'respondent'])->where('uuid', $this->supportRequest->related_id)->first();
                $this->relatedItemTitle = $this->relatedItem ? ($this->relatedItem->survey->title ?? 'Unknown Survey') : 'Report not found';
            }
        }
    }

    // Add a computed property to determine if the request is locked
    public function getIsLockedProperty()
    {
        if (!$this->supportRequest) {
            return false;
        }
        
        return in_array($this->supportRequest->status, ['resolved', 'rejected']);
    }

    public function updateRequest(TrustScoreService $trustScoreService)
    {
        // Prevent updates if the request is already resolved or rejected
        if ($this->getIsLockedProperty()) {
            $this->dispatch('notify', [
                'message' => "This support request is locked and cannot be modified because it has been {$this->supportRequest->status}.",
                'type' => 'error',
            ]);
            return;
        }
        
        $this->validate();
        
        $statusChanged = $this->supportRequest->status !== $this->status;
        $notesChanged = $this->supportRequest->admin_notes !== $this->adminNotes;
        $wasResolved = $this->status === 'resolved' && $this->supportRequest->status !== 'resolved';
        $wasRejected = $this->status === 'rejected' && $this->supportRequest->status !== 'rejected';
        $previousStatus = $this->supportRequest->status;
        
        $this->supportRequest->admin_notes = $this->adminNotes;
        $this->supportRequest->status = $this->status;
        $this->supportRequest->admin_id = auth()->id();
        
        if ($wasResolved) {
            $this->supportRequest->resolved_at = now();
        }
        
        $this->supportRequest->save();
        
        // Update associated report status if this is a report appeal
        if ($this->supportRequest->request_type === 'report_appeal' && $this->supportRequest->related_id) {
            $report = Report::where('uuid', $this->supportRequest->related_id)->first();
            
            
            if ($report) {
                try {
                    DB::transaction(function() use ($report, $wasResolved, $wasRejected) {
                       
                        if ($wasResolved) {
                            // Support request resolved = report dismissed and trust score deduction reversed
                            $report->markAsDismissed();
                          
                            // Also update the associated response to mark it as not reported anymore
                            if ($report->response_id) {
                                $response = Response::find($report->response_id);
                                if ($response) {
                                    $response->reported = false;
                                    $response->save();
                                }
                            }
                            
                            // Handle the respondent (user who was falsely reported)
                            if (!$report->deduction_reversed && $report->respondent_id) {
                                $respondent = User::find($report->respondent_id);
                                if ($respondent) {
                                    // Only restore trust score if there was a deduction
                                    if ($report->trust_score_deduction) {
                                        $deductionAmount = abs($report->trust_score_deduction); // Convert to positive for addition
                                        $respondent->trust_score += $deductionAmount;
                                    }
                                    
                                    // Restore points if they were deducted
                                    if ($report->points_deducted > 0 && !$report->points_restored) {
                                        $respondent->points += $report->points_deducted;
                                        $report->points_restored = true;
                                      
                                        
                                        // Add to notification message
                                        $pointsMessage = "\n\nThe {$report->points_deducted} points that were deducted from your account have been restored.";
                                        
                                        // Notify the user about points restoration
                                        InboxMessage::create([
                                            'recipient_id' => $respondent->id,
                                            'subject' => 'Points Restored After Successful Appeal',
                                            'message' => "Your appeal for Report #{$report->uuid} has been approved.{$pointsMessage}\n\n" . 
                                                ($report->trust_score_deduction ? "Your trust score has also been restored by {$deductionAmount} points." : "No trust score adjustment was needed."),
                                            'read_at' => null
                                        ]);
                                    }
                                    
                                    $respondent->save();
                                }
                            }
                            
                            // Handle the reporter (user who made the false report) - ALWAYS process this
                            if ($report->reporter_id) {
                                $reporter = User::find($report->reporter_id);
                                if ($reporter) {

                                    // Calculate penalty based on reporter's false report history
                                    $calcFalseReport = $this->trustScoreService->calculateFalseReportPenalty($report->reporter_id);
                                    $penaltyAmount = $calcFalseReport['penalty_amount']; // Get the actual penalty amount
                                    // Apply penalty if applicable (could be 0)
                                    if ($penaltyAmount < 0) {
                                        $reporter->trust_score = max(0, $reporter->trust_score + $penaltyAmount);
                                    }
                                    $reporter->save();
                                    
                                    // Always record the penalty amount in the report (even if 0)
                                    $report->reporter_trust_score_deduction = $penaltyAmount;

                                    // Get count of dismissed reports for this reporter
                                    $dismissedReportsCount = Report::where('reporter_id', $reporter->id)
                                        ->where('status', 'dismissed')
                                        ->count();
                                    
                                    // ALWAYS notify the false reporter with detailed message
                                    InboxMessage::create([
                                        'recipient_id' => $reporter->id,
                                        'subject' => 'Report Reviewed and Dismissed',
                                        'message' => "Your report (ID: {$report->uuid}) has been reviewed and determined to be invalid. 

                                        You now have {$dismissedReportsCount} false " . ($dismissedReportsCount == 1 ? "report" : "reports") . " on your account.

                                        When a user exceeds 2 false reports, they will receive trust score penalties for each additional false report. As this is your " . $this->trustScoreService->getOrdinal($dismissedReportsCount) . " false report, " . ($penaltyAmount < 0 ? "a trust score penalty of " . abs($penaltyAmount) . " points has been applied to your account." : "no penalty has been applied yet.") . "

                                        Please ensure all reports are legitimate to avoid future penalties. Multiple false reports may result in increasing penalties and account restrictions.",
                                        'read_at' => null
                                    ]);
                                }
                            }
                            
                            // ALWAYS mark the report as processed
                            $report->deduction_reversed = true;
                            $report->save();
                        } elseif ($wasRejected) {
                            // Support request rejected = report confirmed
                            $report->markAsConfirmed();
                            
                            // No change to trust scores as the original deduction stands
                            // No restoration of points either - mark them as permanently deducted
                            if ($report->points_deducted > 0 && !$report->points_restored) {
                                $report->points_restored = true; 
                                $report->save();
                                
                                // Notify the user that points deduction is permanent
                                if ($report->respondent_id) {
                                    InboxMessage::create([
                                        'recipient_id' => $report->respondent_id,
                                        'subject' => 'Appeal Rejected - Points Permanently Deducted',
                                        'message' => "Your appeal for Report #{$report->uuid} has been rejected. The {$report->points_deducted} points that were deducted will not be restored. The trust score deduction also remains in effect.",
                                        'read_at' => null
                                    ]);
                                }
                            }
                        }
                    });
                } catch (\Exception $e) {
                    Log::error('Error processing report appeal', [
                        'report_id' => $report->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Send inbox notification if status changed OR admin notes changed
        if (($statusChanged || $notesChanged) && $this->supportRequest->user_id) {
            $this->sendStatusUpdateNotification($previousStatus, $statusChanged, $notesChanged);
        }
        
        $this->dispatch('supportRequestUpdated', $this->supportRequest->id);
        
        // Dispatch refresh event to parent index
        $this->dispatch('refreshSupportRequests');
        
        $statusName = str_replace('_', ' ', ucfirst($this->status));
        $this->dispatch('notify', [
            'message' => "Support request updated successfully. Status: {$statusName}",
            'type' => 'success',
        ]);
        
        // Refresh the request data
        $this->loadSupportRequest();
    }
    
    private function sendStatusUpdateNotification($previousStatus, $statusChanged, $notesChanged)
    {
        $requestTypeText = ucfirst(str_replace('_', ' ', $this->supportRequest->request_type));
        $newStatusText = ucfirst(str_replace('_', ' ', $this->status));
        
        $adminNotesText = !empty($this->adminNotes) 
            ? $this->adminNotes 
            : 'No admin comment';
        
        // Create a subject line appropriate to what changed
        $subject = "";
        if ($statusChanged && $notesChanged) {
            $subject = "Support Request Update - {$requestTypeText} #{$this->supportRequest->id} - Status: {$newStatusText}";
        } elseif ($statusChanged) {
            $subject = "Support Request Status Update - {$requestTypeText} #{$this->supportRequest->id} - Status: {$newStatusText}";
        } else { // Only notes changed
            $subject = "Support Request Update - {$requestTypeText} #{$this->supportRequest->id}";
        }
        
        // Create a message body appropriate to what changed
        $messageBody = "";
        if ($statusChanged) {
            $messageBody = "Your Support Request for {$requestTypeText} has been updated to status: {$newStatusText}\n\n";
        } else {
            $messageBody = "Your Support Request for {$requestTypeText} has been updated with new information.\n\n";
        }
        
        $messageBody .= "Admin Notes:\n{$adminNotesText}";
        
        InboxMessage::create([
            'recipient_id' => $this->supportRequest->user_id,
            'subject' => $subject,
            'message' => $messageBody,
            'read_at' => null
        ]);
    }

    
 
    
    public function render()
    {
        return view('livewire.super-admin.support-requests.modal.support-request-view-modal');
    }
}

