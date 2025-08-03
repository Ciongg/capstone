<?php

namespace App\Livewire\SuperAdmin\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Report;

class ReportIndex extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $reasonFilter = 'all';
    public $selectedReportId = null;


    public function filterByReason($reason)
    {
        $this->reasonFilter = $reason;
        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function getReportsProperty()
    {
        $query = Report::with(['survey', 'response.user', 'reporter', 'respondent', 'question'])
            ->orderBy('created_at', 'desc');

        // Apply reason filter
        if ($this->reasonFilter !== 'all') {
            $query->where('reason', $this->reasonFilter);
        }

        // Apply search filter - search by survey title or respondent UUID
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->whereHas('survey', function($sq) {
                    $sq->where('title', 'like', '%' . $this->searchTerm . '%');
                })
                ->orWhereHas('respondent', function($rq) {
                    $rq->where('uuid', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }

        return $query->paginate(15);
    }

    public function render()
    {
        // Get count for each reason type
        $inappropriateCount = Report::where('reason', 'inappropriate_content')->count();
        $spamCount = Report::where('reason', 'spam')->count();
        $offensiveCount = Report::where('reason', 'offensive')->count();
        $suspiciousCount = Report::where('reason', 'suspicious')->count();
        $duplicateCount = Report::where('reason', 'duplicate')->count();
        $otherCount = Report::where('reason', 'other')->count();

        return view('livewire.super-admin.reports.report-index', [
            'reports' => $this->reports,
            'inappropriateCount' => $inappropriateCount,
            'spamCount' => $spamCount,
            'offensiveCount' => $offensiveCount,
            'suspiciousCount' => $suspiciousCount,
            'duplicateCount' => $duplicateCount,
            'otherCount' => $otherCount,
        ]);
    }
}
