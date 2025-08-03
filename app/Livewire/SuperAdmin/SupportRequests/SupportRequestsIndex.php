<?php

namespace App\Livewire\SuperAdmin\SupportRequests;

use Livewire\Component;
use App\Models\SupportRequest;
use Livewire\WithPagination;

class SupportRequestsIndex extends Component
{
    use WithPagination;
    
    public $searchTerm = '';
    public $statusFilter = 'all';
    public $requestTypeFilter = 'all';
    public $selectedRequestId = null;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'requestTypeFilter' => ['except' => 'all'],
    ];

    protected $listeners = ['refreshSupportRequests' => '$refresh'];

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function filterByType($type)
    {
        $this->requestTypeFilter = $type;
        $this->resetPage();
    }

    public function render()
    {
        $query = SupportRequest::query()
            ->with('user');

        // Apply search filter - only search by subject or user UUID
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('subject', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('user', function($userQuery) {
                      $userQuery->where('uuid', 'like', '%' . $this->searchTerm . '%');
                  });
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply type filter
        if ($this->requestTypeFilter !== 'all') {
            $query->where('request_type', $this->requestTypeFilter);
        }

        // Get count for each status type
        $pendingCount = SupportRequest::where('status', 'pending')->count();
        $inProgressCount = SupportRequest::where('status', 'in_progress')->count();
        $resolvedCount = SupportRequest::where('status', 'resolved')->count();
        $rejectedCount = SupportRequest::where('status', 'rejected')->count();

        // Get count for each request type
        $lockAppealCount = SupportRequest::where('request_type', 'survey_lock_appeal')->count();
        $reportAppealCount = SupportRequest::where('request_type', 'report_appeal')->count();
        $accountIssueCount = SupportRequest::where('request_type', 'account_issue')->count();
        $surveyQuestionCount = SupportRequest::where('request_type', 'survey_question')->count();
        $otherCount = SupportRequest::where('request_type', 'other')->count();

        $supportRequests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.super-admin.support-requests.support-requests-index', [
            'supportRequests' => $supportRequests,
            'pendingCount' => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'resolvedCount' => $resolvedCount,
            'rejectedCount' => $rejectedCount,
            'lockAppealCount' => $lockAppealCount,
            'reportAppealCount' => $reportAppealCount,
            'accountIssueCount' => $accountIssueCount,
            'surveyQuestionCount' => $surveyQuestionCount,
            'otherCount' => $otherCount,
        ]);
    }
}
