<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;

class FormResponses extends Component
{
    public $survey;
    public $averageTime = null; // in seconds
    public $points = null;

    public function mount($surveyId)
    {
        $this->survey = Survey::with('pages.questions.answers', 'responses.snapshot')->findOrFail($surveyId);

        // Calculate average time from response snapshots
        $snapshots = $this->survey->responses->pluck('snapshot')->filter();
        $totalSeconds = $snapshots->sum(function ($snapshot) {
            return $snapshot?->completion_time_seconds ?? 0;
        });
        $count = $snapshots->count();
        $this->averageTime = $count > 0 ? round($totalSeconds / $count) : null;

        // Points allocated for this survey
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

    public function render()
    {
        return view('livewire.surveys.form-responses.form-responses', [
            'survey' => $this->survey,
            'averageTime' => $this->averageTime,
            'points' => $this->points,
        ]);
    }
}
