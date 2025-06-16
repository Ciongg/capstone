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

class ViewReportResponseModal extends Component
{
    public Response $response;
    public Survey $survey;
    public string $reason = '';
    public string $details = '';
    public $questionId = null;
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
    
    public function confirmReport()
    {
        try {
            DB::transaction(function () {
                // Validate that the question exists if provided
                $validatedQuestionId = null;
                if ($this->questionId) {
                    $question = SurveyQuestion::find($this->questionId);
                    if ($question) {
                        $validatedQuestionId = $this->questionId;
                        Log::info('Question validated', ['question_id' => $validatedQuestionId]);
                    } else {
                        Log::warning('Question not found', ['question_id' => $this->questionId]);
                        throw new \Exception('Selected question not found');
                    }
                }
                
                // Check if user is authenticated
                if (!auth()->check()) {
                    throw new \Exception('User not authenticated');
                }

                // Calculate trust score deduction for the respondent if available
                $trustScoreDeduction = 0;
                $pointsDeducted = 0;
                
                if ($this->response->user_id) {
                    // Calculate trust score deduction
                    $trustScoreDeduction = $this->calculateTrustScoreDeduction($this->response->user_id);
                    
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

                // Mark the response as reported
                $this->response->update(['reported' => true]);

                // Send inbox notification to the reported user
                if ($this->response->user_id) {
                    $reasonText = $this->reportReasons[$this->reason] ?? 'Unknown reason';
                    $surveyTitle = $this->survey->title ?? 'Unknown Survey';
                    
                    $pointsMessage = $pointsDeducted > 0 
                        ? "\n\nAdditionally, {$pointsDeducted} points earned from this survey have been temporarily deducted." 
                        : "";
                        
                    InboxMessage::create([
                        'recipient_id' => $this->response->user_id,
                        'subject' => 'Your Survey Response Has Been Reported',
                        'message' => "Your response to the survey '{$surveyTitle}' has been reported for: {$reasonText}.\n\nReport ID: #{$report->id}\n\nThis has affected your trust score by {$trustScoreDeduction} points.{$pointsMessage} If you believe this report was made in error, you can appeal this decision by submitting a support request through your Profile > Help Request section. Please include the Report ID #{$report->id} in your appeal.",
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
     * Calculate trust score deduction based on user's report history
     * 
     * @param int $userId
     * @return float Negative value representing the deduction amount
     */
    private function calculateTrustScoreDeduction($userId)
    {
        // Base deduction amount
        $baseDeduction = -5.0;
        
        // Get total number of responses by this user
        $totalResponses = Response::where('user_id', $userId)->count();
        if ($totalResponses === 0) {
            return $baseDeduction; // Default to base deduction if no responses
        }
        
        // Get number of valid reports against this user (exclude dismissed/appealed reports)
        $reportedResponses = Report::where('respondent_id', $userId)
            ->where('status', '!=', 'dismissed')
            ->count();
        
        // Calculate percentage of reported responses
        $reportPercentage = ($reportedResponses / $totalResponses) * 100;
        
        // Determine modifier based on the percentage
        $modifier = 1.0; // Default modifier (5-20%)
        
        if ($reportPercentage < 5) {
            $modifier = 0.5; // Less than 5% - reduced deduction
        } elseif ($reportPercentage > 10) {
            $modifier = 1.5; // More than 10% - increased deduction
        }
        
        // Apply modifier to the base deduction
        $finalDeduction = $baseDeduction * $modifier;
        
        Log::info('Trust score deduction calculated', [
            'user_id' => $userId,
            'total_responses' => $totalResponses,
            'valid_reported_responses' => $reportedResponses,
            'report_percentage' => $reportPercentage,
            'modifier' => $modifier,
            'base_deduction' => $baseDeduction,
            'final_deduction' => $finalDeduction
        ]);
        
        return round($finalDeduction, 2); // Round to 2 decimal places
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
