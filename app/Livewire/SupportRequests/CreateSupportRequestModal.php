<?php

namespace App\Livewire\SupportRequests;

use Livewire\Component;
use App\Models\SupportRequest;

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
        return [
            'subject' => 'required|min:5|max:255',
            'description' => 'required|min:20',
            'request_type' => 'required|in:survey_lock_appeal,report_appeal,account_issue,survey_question,other',
        ];
    }

    // Submit the support request
    public function submitRequest()
    {
        $this->validate();
        
        try {
            SupportRequest::create([
                'user_id' => auth()->id(),
                'subject' => $this->subject,
                'description' => $this->description,
                'request_type' => $this->request_type,
                'status' => $this->status,
                'related_id' => $this->related_id,
                'related_model' => $this->related_model,
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
