<?php

namespace App\Livewire\SuperAdmin\SupportRequests\Modal;

use Livewire\Component;
use App\Models\SupportRequest;

class SupportRequestViewModal extends Component
{
    public $requestId;
    public $supportRequest;
    public $adminNotes;
    public $status;
    public $relatedItem = null;

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
        
        // Try to load related item if exists
        if ($this->supportRequest->related_id && $this->supportRequest->related_model) {
            try {
                $modelClass = $this->supportRequest->related_model;
                if (class_exists($modelClass)) {
                    $this->relatedItem = $modelClass::find($this->supportRequest->related_id);
                }
            } catch (\Exception $e) {
                // Related model couldn't be loaded - that's okay
            }
        }
    }

    public function updateRequest()
    {
        $this->validate();
        
        $statusChanged = $this->supportRequest->status !== $this->status;
        $wasResolved = $this->status === 'resolved' && $this->supportRequest->status !== 'resolved';
        
        $this->supportRequest->admin_notes = $this->adminNotes;
        $this->supportRequest->status = $this->status;
        $this->supportRequest->admin_id = auth()->id();
        
        if ($wasResolved) {
            $this->supportRequest->resolved_at = now();
        }
        
        $this->supportRequest->save();
        
        $this->dispatch('supportRequestUpdated', $this->supportRequest->id);
        
        $statusName = str_replace('_', ' ', ucfirst($this->status));
        $this->dispatch('notify', [
            'message' => "Support request updated successfully. Status: {$statusName}",
            'type' => 'success',
        ]);
        
        // Refresh the request data
        $this->loadSupportRequest();
    }

    public function render()
    {
        return view('livewire.super-admin.support-requests.modal.support-request-view-modal');
    }
}
