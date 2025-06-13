<?php

namespace App\Livewire\SuperAdmin\SupportRequests\Modal;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Models\Survey;
use App\Models\Report;
use App\Models\InboxMessage;

class SupportRequestViewModal extends Component
{
    public $requestId;
    public $supportRequest;
    public $adminNotes;
    public $status;
    public $relatedItem = null;
    public $relatedItemTitle = null;

    protected $rules = [
        'adminNotes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,resolved,rejected',
    ];

    public function mount($requestId)
    {
        $this->requestId = $requestId;
        $this->loadSupportRequest();
    }

    public function loadSupportRequest()
    {
        $this->supportRequest = SupportRequest::with(['user', 'admin'])
            ->findOrFail($this->requestId);
            
        $this->adminNotes = $this->supportRequest->admin_notes;
        $this->status = $this->supportRequest->status;
        
        // Load related item based on request type
        if ($this->supportRequest->related_id && $this->supportRequest->related_model) {
            if ($this->supportRequest->request_type === 'survey_lock_appeal' && $this->supportRequest->related_model === 'Survey') {
                $this->relatedItem = Survey::find($this->supportRequest->related_id);
                $this->relatedItemTitle = $this->relatedItem ? $this->relatedItem->title : 'Survey not found';
            } elseif ($this->supportRequest->request_type === 'report_appeal' && $this->supportRequest->related_model === 'Report') {
                $this->relatedItem = Report::with(['survey', 'reporter', 'respondent'])->find($this->supportRequest->related_id);
                $this->relatedItemTitle = $this->relatedItem ? ($this->relatedItem->survey->title ?? 'Unknown Survey') : 'Report not found';
            }
        }
    }

    public function updateRequest()
    {
        $this->validate();
        
        $statusChanged = $this->supportRequest->status !== $this->status;
        $wasResolved = $this->status === 'resolved' && $this->supportRequest->status !== 'resolved';
        $previousStatus = $this->supportRequest->status;
        
        $this->supportRequest->admin_notes = $this->adminNotes;
        $this->supportRequest->status = $this->status;
        $this->supportRequest->admin_id = auth()->id();
        
        if ($wasResolved) {
            $this->supportRequest->resolved_at = now();
        }
        
        $this->supportRequest->save();
        
        // Send inbox notification if status changed
        if ($statusChanged && $this->supportRequest->user_id) {
            $this->sendStatusUpdateNotification($previousStatus);
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
    
    private function sendStatusUpdateNotification($previousStatus)
    {
        $requestTypeText = ucfirst(str_replace('_', ' ', $this->supportRequest->request_type));
        $newStatusText = ucfirst(str_replace('_', ' ', $this->status));
        
        $adminNotesText = !empty($this->adminNotes) 
            ? $this->adminNotes 
            : 'No admin comment';
        
        InboxMessage::create([
            'recipient_id' => $this->supportRequest->user_id,
            'subject' => "Support Request Status Update - {$requestTypeText} #{$this->supportRequest->id} {$newStatusText}" ,
            'message' => "Your Support Request for {$requestTypeText} has been updated to: {$newStatusText}\n\nAdmin Notes:\n{$adminNotesText}",
            'read_at' => null
        ]);
    }

    public function render()
    {
        return view('livewire.super-admin.support-requests.modal.support-request-view-modal');
    }
}
