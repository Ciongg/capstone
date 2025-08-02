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
                'string', // Changed from numeric to string for UUIDs
                function ($attribute, $value, $fail) {
                    if ($this->request_type === 'survey_lock_appeal') {
                        if (!$this->validateSurveyOwnership($value)) {
                            $fail('The survey ID is invalid or you do not own this survey.');
                        }
                        // Check if the survey is actually locked
                        if (!$this->validateSurveyIsLocked($value)) {
                            $fail('This survey is not currently locked. Lock appeals can only be submitted for locked surveys.');
                        }
                    } elseif ($this->request_type === 'report_appeal') {
                        $validation = $this->validateReportAppeal($value);
                        if (!$validation['valid']) {
                            $fail($validation['message']);
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
    private function validateSurveyOwnership($surveyUuid)
    {
        return Survey::where('uuid', $surveyUuid)
            ->where('user_id', auth()->id())
            ->exists();
    }

    /**
     * Validate that the survey is actually locked
     */
    private function validateSurveyIsLocked($surveyUuid)
    {
        return Survey::where('uuid', $surveyUuid)
            ->where('is_locked', true)
            ->exists();
    }

    /**
     * Validate that the report exists, user is the respondent, and can be appealed
     */
    private function validateReportAppeal($reportUuid)
    {
        $report = Report::where('uuid', $reportUuid)
            ->where('respondent_id', auth()->id())
            ->first();

        if (!$report) {
            return [
                'valid' => false,
                'message' => 'The report ID is invalid or you are not the reported user in this report.'
            ];
        }

        if ($report->isUnderAppeal()) {
            return [
                'valid' => false,
                'message' => 'This report is already under appeal. You cannot submit another appeal for the same report.'
            ];
        }

        if ($report->status === Report::STATUS_CONFIRMED || $report->status === Report::STATUS_DISMISSED) {
            return [
                'valid' => false,
                'message' => 'This report has already been under investigation before.'
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate that the report exists and the user is the respondent
     */
    private function validateReportRespondent($reportUuid)
    {
        return Report::where('uuid', $reportUuid)
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
            'related_id.string' => 'The ID must be a valid identifier.',
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

            // Create the support request - using UUID as related_id
            $supportRequest = SupportRequest::create([
                'user_id' => auth()->id(),
                'subject' => $this->subject,
                'description' => $this->description,
                'request_type' => $this->request_type,
                'status' => $this->status,
                'related_id' => $this->related_id, // This is now a UUID string
                'related_model' => $relatedModel,
            ]);

            // If this is a report appeal, update the report status
            if ($this->request_type === 'report_appeal' && $this->related_id) {
                // Find report by UUID instead of ID
                $report = Report::where('uuid', $this->related_id)->first();
                if ($report && $report->canBeAppealed()) {
                    $report->markAsUnderAppeal();
                }
            }

            // Reset form
            $this->reset([
                'subject', 
                'description', 
                'request_type', 
                'related_id', 
                'related_model'
            ]);
            
            $this->showSuccess = true;
            $this->message = 'Your support request has been submitted successfully. We will review it and get back to you soon.';
            
        } catch (\Exception $e) {
            session()->flash('error', 'There was an error submitting your request. Please try again.');
        }
    }

    // Close the modal and reset state
    public function closeModal()
    {
        // Reset all form fields and states
        $this->reset([
            'subject', 
            'description', 
            'request_type', 
            'related_id', 
            'related_model',
            'showSuccess',
            'message'
        ]);
        
        // Reset valida ion errors
        $this->resetValidation();
        

    }

    public function render()
    {
        return view('livewire.support-requests.create-support-request-modal');
    }
}
