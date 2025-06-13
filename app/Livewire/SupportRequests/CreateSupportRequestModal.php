<?php

namespace App\Livewire\SupportRequests;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Models\Survey;
use App\Models\Report;

class CreateSupportRequestModal extends Component
{
    // Form fields
    public $subject = '';
    public $description = '';
    public $request_type = '';
    public $status = 'pending';
    
    // Additional fields that might be needed for specific request types
    public $related_id = null;
    public $related_model = null;
    
    // Form submission state
    public $showSuccess = false;
    public $message = '';
    
    // Form validation rules
    protected function rules()
    {
        $rules = [
            'subject' => 'required|min:5|max:255',
            'description' => 'required|min:20',
            'request_type' => 'required|in:survey_lock_appeal,report_appeal,account_issue,survey_question,other',
        ];
        
        // Make related_id required for specific request types
        if (in_array($this->request_type, ['survey_lock_appeal', 'report_appeal'])) {
            $rules['related_id'] = [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->request_type === 'survey_lock_appeal') {
                        if (!$this->validateSurveyOwnership($value)) {
                            $fail('The survey ID is invalid or you do not own this survey.');
                        }
                    } elseif ($this->request_type === 'report_appeal') {
                        if (!$this->validateReportRespondent($value)) {
                            $fail('The report ID is invalid or you are not the reported user in this report.');
                        }
                    }
                }
            ];
        }
        
        return $rules;
    }
    
    /**
     * Validate that the survey belongs to the authenticated user
     */
    private function validateSurveyOwnership($surveyId)
    {
        return Survey::where('id', $surveyId)
            ->where('user_id', auth()->id())
            ->exists();
    }
    
    /**
     * Validate that the report exists and the user is the respondent
     */
    private function validateReportRespondent($reportId)
    {
        return Report::where('id', $reportId)
            ->where('respondent_id', auth()->id())
            ->exists();
    }
    
    // Custom validation messages
    protected function messages()
    {
        return [
            'related_id.required' => $this->request_type === 'survey_lock_appeal' 
                ? 'Survey ID is required for survey lock appeals.'
                : 'Report ID is required for report appeals.',
            'related_id.numeric' => 'The ID must be a valid number.',
        ];
    }

    // Submit the support request
    public function submitRequest()
    {
        $this->validate();
        
        try {
            // Set the related_model based on request type
            $relatedModel = null;
            if ($this->request_type === 'survey_lock_appeal') {
                $relatedModel = 'Survey';
            } elseif ($this->request_type === 'report_appeal') {
                $relatedModel = 'Report';
            }
            
            SupportRequest::create([
                'user_id' => auth()->id(),
                'subject' => $this->subject,
                'description' => $this->description,
                'request_type' => $this->request_type,
                'status' => $this->status,
                'related_id' => $this->related_id,
                'related_model' => $relatedModel,
            ]);
            
            $this->showSuccess = true;
            $this->message = 'Your support request has been submitted successfully. We will get back to you soon.';
            
            // Reset form fields
            $this->reset(['subject', 'description', 'request_type', 'related_id', 'related_model']);
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while submitting your request. Please try again.');
        }
    }
    
    // Close the modal
    public function closeModal()
    {
        $this->dispatch('close-modal', ['name' => 'support-request-modal']);
        $this->reset();
    }
    
    public function render()
    {
        return view('livewire.support-requests.create-support-request-modal');
    }
}
