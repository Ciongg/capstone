<?php

namespace App\Exports;

use App\Models\Survey;
use Illuminate\Support\Facades\Log;

class SimpleCsvExporter
{
    protected $survey;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
    }

    public function download()
    {
        try {
            // Load all questions and order them correctly
            $questions = [];
            $questionNumber = 1;
            
            foreach ($this->survey->pages()->orderBy('order')->get() as $page) {
                foreach ($page->questions()->orderBy('order')->get() as $question) {
                    $question->question_number = $questionNumber;
                    $questions[] = $question;
                    $questionNumber++;
                }
            }

            $output = fopen('php://temp', 'r+');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Create CSV headers - removed User ID
            $headers = ['Respondent No.', 'Submitted At', 'Trust Score', 
                       'Completion Time (seconds)', 'Started At', 'Completed At'];
            
            // Add question headers with numbering
            foreach ($questions as $question) {
                if ($question->question_type === 'likert') {
                    // For likert, add separate columns for each row
                    $likertRows = is_array($question->likert_rows) ? 
                        $question->likert_rows : 
                        json_decode($question->likert_rows, true);
                    
                    if (is_array($likertRows)) {
                        foreach ($likertRows as $rowIdx => $rowText) {
                            $subQuestionLetter = chr(97 + $rowIdx); // a, b, c, etc.
                            $headers[] = "Q{$question->question_number}{$subQuestionLetter}: {$rowText}";
                        }
                    }
                } else {
                    $headers[] = "Q{$question->question_number}: {$question->question_text}";
                }
            }
            
            fputcsv($output, $headers);
            
            // Add response data - eagerly load all the data we need
            $responses = $this->survey->responses()
                ->with(['answers', 'snapshot', 'user'])
                ->orderBy('created_at')
                ->get();

            $respondentNumber = 1; // Counter for sequential numbering of respondents

            foreach ($responses as $response) {
                // Safely handle potentially null snapshot
                $snapshot = $response->snapshot;
                
                $row = [
                    $respondentNumber++, // Sequential numbering instead of ID
                    $response->created_at->format('d/m/Y h:i A'), // Changed date format
                ];
                
                // Add snapshot data with null checks
                if ($snapshot) {
                    $row[] = $snapshot->trust_score ?? 'N/A';
                    $row[] = $snapshot->completion_time_seconds ?? 'N/A';
                    $row[] = $snapshot->started_at ? date('d/m/Y h:i A', strtotime($snapshot->started_at)) : 'N/A';
                    $row[] = $snapshot->completed_at ? date('d/m/Y h:i A', strtotime($snapshot->completed_at)) : 'N/A';
                } else {
                    // Add placeholder values if snapshot is null
                    $row[] = 'N/A';
                    $row[] = 'N/A';
                    $row[] = 'N/A';
                    $row[] = 'N/A';
                }
                
                // Group answers by question_id for easy lookup
                $answersByQuestionId = [];
                foreach ($response->answers as $answer) {
                    $answersByQuestionId[$answer->survey_question_id] = $answer;
                }
                
                // Add each question's answer to the row
                foreach ($questions as $question) {
                    $answer = $answersByQuestionId[$question->id] ?? null;
                    
                    if ($question->question_type === 'likert') {
                        // For likert, add separate columns for each row
                        $this->addLikertAnswersToRow($row, $answer, $question);
                    } else {
                        $row[] = $this->formatAnswer($answer ? $answer->answer : null, $question);
                    }
                }
                
                fputcsv($output, $row);
            }
            
            // Reset pointer
            rewind($output);
            // Get contents
            $csv = stream_get_contents($output);
            fclose($output);
            
            return $csv;
        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return "Error generating CSV: " . $e->getMessage();
        }
    }

    private function addLikertAnswersToRow(&$row, $answer, $question)
    {
        $likertRows = is_array($question->likert_rows) ? 
            $question->likert_rows : 
            json_decode($question->likert_rows, true);
            
        $likertColumns = is_array($question->likert_columns) ? 
            $question->likert_columns : 
            json_decode($question->likert_columns, true);
        
        if (!is_array($likertRows)) {
            $row[] = 'N/A';
            return;
        }
        
        // Initialize all rows with "Not answered"
        $likertAnswers = array_fill(0, count($likertRows), 'Not answered');
        
        if ($answer && $answer->answer) {
            try {
                $decoded = json_decode($answer->answer, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $rowIdx => $colIdx) {
                        if (isset($likertRows[$rowIdx]) && isset($likertColumns[$colIdx])) {
                            // Just show the column option selected (e.g., "Strongly Agree")
                            $likertAnswers[$rowIdx] = $likertColumns[$colIdx];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Keep as "Not answered"
            }
        }
        
        // Add each likert row answer as a separate column
        foreach ($likertAnswers as $likertAnswer) {
            $row[] = $likertAnswer;
        }
    }

    private function formatAnswer($answer, $question)
    {
        if ($answer === null) {
            return 'Not answered';
        }

        switch ($question->question_type) {
            case 'multiple_choice':
                // For multiple choice, convert choice IDs to actual choice texts
                try {
                    $choiceIds = json_decode($answer, true);
                    if (is_array($choiceIds)) {
                        $choiceTexts = [];
                        foreach ($choiceIds as $choiceId) {
                            $choice = $question->choices->firstWhere('id', $choiceId);
                            if ($choice) {
                                $choiceTexts[] = $choice->choice_text;
                            } else {
                                $choiceTexts[] = "Choice ID: $choiceId";
                            }
                        }
                        return implode('; ', $choiceTexts);
                    }
                } catch (\Exception $e) {
                    // Fall through to default
                }
                return $answer;
                
            case 'radio':
                // For radio buttons (single choice), convert choice ID to actual choice text
                try {
                    $choiceId = json_decode($answer, true);
                    if ($choiceId) {
                        $choice = $question->choices->firstWhere('id', $choiceId);
                        if ($choice) {
                            return $choice->choice_text;
                        }
                    }
                } catch (\Exception $e) {
                    // Fall through to default
                }
                return $answer;
                
            case 'rating':
                // For ratings, return the numeric value
                return is_numeric($answer) ? "$answer stars" : $answer;
                
            default:
                return $answer;
        }
    }
}
