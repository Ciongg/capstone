<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;
use Exception;
use App\Exports\SimpleCsvExporter;
use Illuminate\Support\Facades\Log;

class FormResponses extends Component
{
    public $survey;
    public $averageTime = null;
    public $points = null;
    
    public function mount($surveyId)
    {
        $this->survey = Survey::with('pages.questions.answers', 'responses.snapshot')->findOrFail($surveyId);

        $snapshots = $this->survey->responses->pluck('snapshot')->filter();
        $totalSeconds = $snapshots->sum(function ($snapshot) {
            return $snapshot?->completion_time_seconds ?? 0;
        });
        $count = $snapshots->count();
        $this->averageTime = $count > 0 ? round($totalSeconds / $count) : null;

        $this->points = $this->survey->points ?? null;
    }

    public function clearAllAISummaries()
    {
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions as $question) {
                $question->ai_summary = null;
                $question->save();
            }
        }
    }

    public function exportToCsv()
    {
        try {
            // Load survey with fresh data but don't update component state
            $survey = Survey::with([
                'pages.questions.choices',
                'responses.answers',
                'responses.snapshot',
                'responses.user'
            ])->findOrFail($this->survey->id);
            
            $filename = 'survey_responses_' . $this->survey->id . '_' . date('Y-m-d_His') . '.csv';
            $exporter = new SimpleCsvExporter($survey);
            
            $csvContent = $exporter->download();
            
            if (empty($csvContent) || substr($csvContent, 0, 5) === "Error") {
                $this->dispatch('export-error', message: $csvContent ?: 'Error generating CSV: Empty content');
                return;
            }
            
            // Return download response without triggering component refresh
            return response()->streamDownload(function() use ($csvContent) {
                echo $csvContent;
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
            
        } catch (Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->dispatch('export-error', message: 'Error exporting to CSV: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-responses.form-responses', [
            'survey' => $this->survey,
            'averageTime' => $this->averageTime,
            'points' => $this->points,
        ]);
    }
}
    
          