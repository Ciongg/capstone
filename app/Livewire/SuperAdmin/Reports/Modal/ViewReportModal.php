<?php

namespace App\Livewire\SuperAdmin\Reports\Modal;

use Livewire\Component;
use App\Models\Report;
use App\Services\AuditLogService;

class ViewReportModal extends Component
{
    public $reportId;
    public $report;
    public $questionAnswer = null;
    public $processedAnswer = null;

    public function mount($reportId)
    {
        $this->reportId = $reportId;
        $this->loadReport();

        // Audit log when a report is viewed (for sensitive content tracking)
        if ($this->report) {
            AuditLogService::log(
                eventType: 'view',
                message: "Viewed report #{$this->report->uuid} for survey '{$this->report->survey->title}'",
                resourceType: 'Report',
                resourceId: $this->report->id,
                meta: [
                    'report_reason' => $this->report->reason,
                    'report_status' => $this->report->status,
                    'respondent_id' => $this->report->respondent_id,
                    'reporter_id' => $this->report->reporter_id,
                ]
            );
        }
    }

    public function loadReport()
    {
        $this->report = Report::with([
            'survey',
            'response.user',
            'response.answers',
            'reporter',
            'respondent',
            'question.choices'
        ])->find($this->reportId);

        if ($this->report && $this->report->question) {
            $this->loadQuestionAnswer();
        }
    }

    protected function loadQuestionAnswer()
    {
        // Get the specific answer for the reported question
        $this->questionAnswer = $this->report->response->answers
            ->where('survey_question_id', $this->report->question_id)
            ->first();

        if ($this->questionAnswer) {
            $this->processAnswer();
        }
    }

    protected function processAnswer()
    {
        $question = $this->report->question;
        $answerDataString = $this->questionAnswer->answer;
        $decodedAnswer = $answerDataString ? json_decode($answerDataString, true) : [];

        $this->processedAnswer = [
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'raw_answer' => $answerDataString,
            'display_answer' => 'No answer provided'
        ];

        if (in_array($question->question_type, ['multiple_choice', 'radio'])) {
            $selectedChoices = [];
            $otherText = null;

            foreach ($question->choices as $choice) {
                $isSelected = false;
                
                if ($question->question_type === 'multiple_choice' && is_array($decodedAnswer)) {
                    $isSelected = in_array($choice->id, $decodedAnswer);
                } elseif ($question->question_type === 'radio' && !is_array($decodedAnswer)) {
                    $isSelected = (int)$decodedAnswer === $choice->id;
                }

                if ($isSelected) {
                    $selectedChoices[] = $choice->choice_text;
                    
                    if ($choice->is_other && $this->questionAnswer->other_text) {
                        $otherText = $this->questionAnswer->other_text;
                    }
                }
            }

            if (!empty($selectedChoices)) {
                $this->processedAnswer['display_answer'] = implode(', ', $selectedChoices);
                if ($otherText) {
                    $this->processedAnswer['other_text'] = $otherText;
                }
            }
        } elseif ($question->question_type === 'likert') {
            $likertColumns = is_array($question->likert_columns) ? 
                $question->likert_columns : 
                json_decode($question->likert_columns, true);
            $likertRows = is_array($question->likert_rows) ? 
                $question->likert_rows : 
                json_decode($question->likert_rows, true);

            if ($decodedAnswer && $likertColumns && $likertRows) {
                $responses = [];
                foreach ($decodedAnswer as $rowIndex => $columnIndex) {
                    if (isset($likertRows[$rowIndex]) && isset($likertColumns[$columnIndex])) {
                        $responses[] = $likertRows[$rowIndex] . ': ' . $likertColumns[$columnIndex];
                    }
                }
                // Use newlines to separate each response for better formatting
                $this->processedAnswer['display_answer'] = implode("\n", $responses);
                $this->processedAnswer['is_likert'] = true; // Flag for special handling in view
            }
        } elseif ($question->question_type === 'rating') {
            $stars = $question->stars ?? 5;
            $rating = $answerDataString ?? '0';
            $this->processedAnswer['display_answer'] = "{$rating} out of {$stars} stars";
        } else {
            // Text-based answers (essay, short_text, date)
            $this->processedAnswer['display_answer'] = $answerDataString ?: 'No answer provided';
        }
    }

    public function render()
    {
        return view('livewire.super-admin.reports.modal.view-report-modal');
    }
}
