<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Report;
use App\Models\User;
use App\Models\SurveyQuestion;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TrustScoreService;
class ViewReportResponseModal extends Component
{
    public Response $response;
    public Survey $survey;
    public string $reason = '';
    public string $details = '';
    public $questionId = null;
    private TrustScoreService $trustScoreService;

    public array $reportReasons = [
        'inappropriate_content' => 'Inappropriate Content',
        'spam' => 'Spam or Misleading',
        'offensive' => 'Offensive Language',
        'suspicious' => 'Suspicious Activity',
        'duplicate' => 'Duplicate Response',
        'other' => 'Other'
    ];
    
    // State management
    public $message = '';
    public $showSuccess = false;
    public $showConfirmation = false; // New property for confirmation screen
    public $questions = [];
    public $selectedQuestionText = ''; // For displaying question text in confirmation
    

    public function boot(TrustScoreService $trustScoreService)
    {
        $this->trustScoreService = $trustScoreService;
    }
    
    public function mount(Response $response, Survey $survey)
    {
        $this->response = $response;
        $this->survey = $survey;
        $this->loadQuestions();
    }
    
    protected function loadQuestions()
    {
        $this->questions = [];
        $questionCounter = 1;
        
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions->sortBy('order') as $question) {
                $this->questions[] = [
                    'id' => $question->id,
                    'display' => "Q{$questionCounter}. {$question->question_text}",
                    'page_title' => $page->title
                ];
                $questionCounter++;
            }
        }
    }
    
    public function submitReport()
    {
        $this->validate([
            'questionId' => 'nullable',
            'reason' => 'required|string|in:' . implode(',', array_keys($this->reportReasons)),
            'details' => 'required|string|min:10|max:500',
        ], [
            'reason.required' => 'Please select a reason for the report.',
            'details.required' => 'Please provide details about the issue.',
            'details.min' => 'Please provide at least 10 characters of details.',
        ]);
        
        // Find selected question text if a question was selected
        $this->selectedQuestionText = '';
        if ($this->questionId) {
            foreach ($this->questions as $question) {
                if ($question['id'] == $this->questionId) {
                    $this->selectedQuestionText = $question['display'];
                    break;
                }
            }
        }
        
        // Show confirmation screen instead of immediately processing
        $this->showConfirmation = true;
    }
    
    public function confirmReport(TrustScoreService $trustScoreService)
    {
        try {
            DB::transaction(function () {
                // Validate that the question exists if provided
                $validatedQuestionId = null;
                if ($this->questionId) {
                    $question = SurveyQuestion::find($this->questionId);
                    if ($question) {
                        $validatedQuestionId = $this->questionId;
                        
                    } else {
                        
                        throw new \Exception('Selected question not found');
                    }
                }
                
                // Check if user is authenticated
                if (!auth()->check()) {
                    throw new \Exception('User not authenticated');
                }

                if ($this->response->user_id) {
                    // Get current valid reports before adding the new one
                    $existingValidReports = Report::where('respondent_id', $this->response->user_id)
                        ->whereIn('status', ['confirmed', 'unappealed'])
                        ->count();
                    
                    // Calculate trust score deduction for the respondent if available
                    // Important: Add +1 to count to include the current report we're creating
                    $calcTrustScoreDeduction = $this->trustScoreService->calculateReportedResponseDeduction(
                        $this->response->user_id, 
                        $existingValidReports + 1
                    );
                    $trustScoreDeduction = $calcTrustScoreDeduction['penalty_amount'];
                    $respondent = User::find($this->response->user_id);
                    if ($respondent) {
                        // Apply trust score deduction
                        $respondent->trust_score = max(0, $respondent->trust_score + $trustScoreDeduction);
                        
                        // Handle points deduction if survey had points allocated
                        if ($this->survey->points_allocated > 0) {
                            $pointsDeducted = $this->survey->points_allocated;
                            $respondent->points = max(0, $respondent->points - $pointsDeducted);
                            
                            Log::info('Deducted points from reported user', [
                                'user_id' => $respondent->id,
                                'points_deducted' => $pointsDeducted,
                                'new_points_balance' => $respondent->points
                            ]);
                        }
                        
                        $respondent->save();
                        
                        Log::info('Applied deductions to reported user', [
                            'user_id' => $respondent->id,
                            'trust_deduction' => $trustScoreDeduction,
                            'points_deducted' => $pointsDeducted,
                            'new_trust_score' => $respondent->trust_score,
                            'new_points' => $respondent->points
                        ]);
                    }
                }

                // Create the report record
                $report = Report::create([
                    'survey_id' => $this->survey->id,
                    'response_id' => $this->response->id,
                    'reporter_id' => auth()->id(),
                    'respondent_id' => $this->response->user_id ?? null,
                    'question_id' => $validatedQuestionId,
                    'reason' => $this->reason,
                    'details' => $this->details,
                    'status' => 'unappealed',
                    'trust_score_deduction' => $trustScoreDeduction,
                    'deduction_reversed' => false,
                    'points_deducted' => $pointsDeducted,
                    'points_restored' => false
                ]);

                $report->save();

                // Mark the response as reported
                $this->response->update(['reported' => true]);
                $this->response->save();
                
                // Send inbox notification to the reported user
                if ($this->response->user_id) {
                    $reasonText = $this->reportReasons[$this->reason] ?? 'Unknown reason';
                    $surveyTitle = $this->survey->title ?? 'Unknown Survey';
                    
                    // Get total valid reports (including the one we just created)
                    $totalValidReports = Report::where('respondent_id', $this->response->user_id)
                        ->whereIn('status', ['confirmed', 'unappealed'])
                        ->count();
                    
                    // Fixed version using if/else for clarity
                   $deductionMessage = $trustScoreDeduction < 0
    ? "As this is your " . $this->trustScoreService->getOrdinal($totalValidReports) . " reported response, a trust score penalty of " . abs($trustScoreDeduction) . " points has been applied to your account."
    : "No trust score deduction has been applied for this report.";

                    $pointsMessage = $pointsDeducted > 0 
                        ? "\n\nAdditionally, {$pointsDeducted} points earned from this survey have been temporarily deducted." 
                        : "";
                        
                    
                    $thresholdMessage = "\n\nYou now have {$totalValidReports} reported " . 
                        ($totalValidReports == 1 ? "response" : "responses") . " on your account. " .
                        "When a user exceeds 2 reported responses, they will receive trust score penalties " .
                        "for each additional report.";
                        
                    InboxMessage::create([
                        'recipient_id' => $this->response->user_id,
                        'subject' => 'Your Survey Response Has Been Reported',
                        'message' => "Your response to the survey '{$surveyTitle}' has been reported for: {$reasonText}.\n\nReport ID: #{$report->id}\n\n{$deductionMessage}{$pointsMessage}{$thresholdMessage}\n\nIf you believe this report was made in error, you can appeal this decision by submitting a support request through your Profile > Help Request section. Please include the Report ID #{$report->id} in your appeal.",
                        'related_url' => '/profile',
                        'read_at' => null
                    ]);
                }
            });
            
            // Success - update UI state
            $this->showConfirmation = false;
            $this->showSuccess = true;
            $this->message = "Response has been reported successfully.";
            
            // Notify parent component to refresh the current response
            $this->dispatch('responseReported');
            
            // Reset form
            $this->questionId = null;
            $this->reason = '';
            $this->details = '';
            
        } catch (\Exception $e) {
            $this->showConfirmation = false;
            $this->message = "We encountered an error while processing your report: " . $e->getMessage();
            
            // Show error message in UI
            $this->dispatch('show-error', ['message' => $this->message]);
        }
    }
    
    
    /**
     * Get ordinal suffix for a number (1st, 2nd, 3rd, etc.)
     * 
     * @param int $number
     * @return string
     */
    private function getOrdinal($number) {
        $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
        
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number. 'th';
        } else {
            return $number. $ends[$number % 10];
        }
    }
    
    public function cancelConfirmation()
    {
        // Go back to the form
        $this->showConfirmation = false;
    }
    
    public function closeModal()
    {
        // Reset all form fields and states
        $this->reset([
            'reason',
            'details',
            'questionId',
            'message',
            'showSuccess',
            'showConfirmation',
            'selectedQuestionText'
        ]);
        
        // Reset validation errors
        $this->resetValidation();
        
        $this->dispatch('close-modal', name: 'view-report-response-modal');
    }
    
    public function render()
    {
        return view('livewire.surveys.form-responses.modal.view-report-response-modal');
    }
}

