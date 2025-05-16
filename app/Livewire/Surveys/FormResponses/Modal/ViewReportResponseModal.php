<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;

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
     
        /* 
        Example of saving to database:
        ResponseReport::create([
            'response_id' => $this->response->id,
            'survey_id' => $this->survey->id,
            'question_id' => $this->questionId,
            'reason' => $this->reason,
            'details' => $this->details,
            'reporter_id' => auth()->id(),
            'status' => 'pending'
        ]);
        */
        
        $this->showConfirmation = false;
        $this->showSuccess = true;
        $this->message = "Response has been reported successfully.";
        
        // Reset form
        $this->questionId = null;
        $this->reason = '';
        $this->details = '';
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
