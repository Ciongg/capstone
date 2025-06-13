<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Report;
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

                // Create the report record
                $report = Report::create([
                    'survey_id' => $this->survey->id,
                    'response_id' => $this->response->id,
                    'reporter_id' => auth()->id(),
                    'respondent_id' => $this->response->user_id ?? null,
                    'question_id' => $validatedQuestionId,
                    'reason' => $this->reason,
                    'details' => $this->details,
                    'status' => 'pending'
                ]);

                // Mark the response as reported
                $this->response->update(['reported' => true]);

                // Send inbox notification to the reported user
                if ($this->response->user_id) {
                    $reasonText = $this->reportReasons[$this->reason] ?? 'Unknown reason';
                    $surveyTitle = $this->survey->title ?? 'Unknown Survey';
                    
                    InboxMessage::create([
                        'recipient_id' => $this->response->user_id,
                        
                        'subject' => 'Your Survey Response Has Been Reported',
                        'message' => "Your response to the survey '{$surveyTitle}' has been reported for: {$reasonText}.\n\nReport ID: #{$report->id}\n\nIf you believe this report was made in error, you can appeal this decision by submitting a support request through your Profile > Help Request section. Please include the Report ID #{$report->id} in your appeal.",
                        'related_url' => '/profile',
                        'read_at' => null
                    ]);
                }

                Log::info('Report created successfully', ['report_id' => $report->id]);
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
            // Log the error with more details
            Log::error('Report creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'survey_id' => $this->survey->id ?? 'null',
                'response_id' => $this->response->id ?? 'null',
                'user_id' => auth()->id() ?? 'null'
            ]);
            
            $this->showConfirmation = false;
            $this->message = "We encountered an error while processing your report: " . $e->getMessage();
            
            // Show error message in UI
            $this->dispatch('show-error', ['message' => $this->message]);
        }
    }
    
    public function cancelConfirmation()
    {
        // Go back to the form
        $this->showConfirmation = false;
    }
    
    public function closeModal()
    {
        $this->dispatch('close-modal', ['name' => 'view-report-response-modal']);
    }
    
    public function render()
    {
        return view('livewire.surveys.form-responses.modal.view-report-response-modal');
    }
}
