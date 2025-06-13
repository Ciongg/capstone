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

        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('details', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('survey', function($sq) {
                      $sq->where('title', 'like', '%' . $this->searchTerm . '%');
                  })
                  ->orWhereHas('reporter', function($rq) {
                      $rq->where('name', 'like', '%' . $this->searchTerm . '%');
                  });
            });
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.super-admin.reports.report-index', [
            'reports' => $this->reports
        ]);
    }
}
