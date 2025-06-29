<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use App\Models\SurveyQuestion;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViewAllResponsesModal extends Component
{
    public SurveyQuestion $question;
    public $aiSummary = '';
    public $exactCounts = '';
    public $loading = false;


    public function mount()
    {
        $this->aiSummary = $this->question->ai_summary ?? '';
        // Generate exact counts on mount for immediate display
        $this->exactCounts = $this->generateExactCounts();
    }

    /**
     * Generate exact counts data locally for all question types
     */
    private function generateExactCounts()
    {
        switch ($this->question->question_type) {
            case 'multiple_choice':
                return $this->generateMultipleChoiceExactCounts();
            case 'radio':
                return $this->generateRadioExactCounts();
            case 'likert':
                return $this->generateLikertExactCounts();
            case 'rating':
                return $this->generateRatingExactCounts();
            case 'date':
                return $this->generateDateExactCounts();
            default:
                return '';
        }
    }

    /**
     * Generate exact counts for multiple choice questions
     */
    private function generateMultipleChoiceExactCounts()
    {
        $choices = $this->question->choices()->get()->pluck('choice_text', 'id')->toArray();
        $choiceCounts = [];
        $uniqueResponses = $this->question->answers->unique('response_id')->count();
        
        foreach ($this->question->answers as $answer) {
            try {
                $selectedChoices = json_decode($answer->answer, true);
                if (is_array($selectedChoices)) {
                    foreach ($selectedChoices as $choiceId) {
                        if (isset($choices[$choiceId])) {
                            $choiceText = $choices[$choiceId];
                            if (!isset($choiceCounts[$choiceText])) {
                                $choiceCounts[$choiceText] = 0;
                            }
                            $choiceCounts[$choiceText]++;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Invalid multiple choice response: " . $e->getMessage());
            }
        }
        
        $result = "Out of {$uniqueResponses} respondents, ";
        $statements = [];
        foreach ($choiceCounts as $choiceText => $count) {
            $percentage = $uniqueResponses > 0 ? round(($count / $uniqueResponses) * 100) : 0;
            $statements[] = "{$percentage}% ({$count}) selected \"{$choiceText}\"";
        }
        
        return $result . implode(', ', $statements) . '.';
    }

    /**
     * Generate exact counts for radio questions
     */
    private function generateRadioExactCounts()
    {
        $choices = $this->question->choices()->get()->pluck('choice_text', 'id')->toArray();
        $choiceCounts = array_fill_keys(array_values($choices), 0);
        $totalResponses = $this->question->answers->unique('response_id')->count();
        
        foreach ($this->question->answers as $answer) {
            $choiceId = $answer->answer;
            if (isset($choices[$choiceId])) {
                $choiceText = $choices[$choiceId];
                $choiceCounts[$choiceText]++;
            }
        }
        
        $result = "Out of {$totalResponses} respondents, ";
        $statements = [];
        foreach ($choiceCounts as $choiceText => $count) {
            if ($count > 0) {
                $percentage = round(($count / $totalResponses) * 100);
                $statements[] = "{$percentage}% ({$count}) selected \"{$choiceText}\"";
            }
        }
        
        return $result . implode(', ', $statements) . '.';
    }

    /**
     * Generate exact counts for rating questions
     */
    private function generateRatingExactCounts()
    {
        $stars = $this->question->stars ?? 5;
        $ratingCounts = array_fill(1, $stars, 0);
        $totalRatings = 0;
        $sum = 0;
        
        foreach ($this->question->answers as $answer) {
            $rating = intval($answer->answer);
            if ($rating >= 1 && $rating <= $stars) {
                $ratingCounts[$rating]++;
                $sum += $rating;
                $totalRatings++;
            }
        }
        
        $averageRating = $totalRatings > 0 ? round($sum / $totalRatings, 1) : 0;
        
        $result = "Based on feedback from {$totalRatings} respondents, the average rating was {$averageRating} out of {$stars} stars. ";
        $statements = [];
        
        foreach ($ratingCounts as $rating => $count) {
            $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100) : 0;
            $starText = $rating == 1 ? "star" : "stars";
            $statements[] = "{$percentage}% ({$count}) gave a rating of {$rating} {$starText}";
        }
        
        return $result . implode('. ', $statements) . '.';
    }

    /**
     * Generate exact counts for Likert questions
     */
    private function generateLikertExactCounts()
    {
        $likertRows = is_array($this->question->likert_rows) ? 
            $this->question->likert_rows : 
            json_decode($this->question->likert_rows ?? '[]', true);
            
        $likertColumns = is_array($this->question->likert_columns) ? 
            $this->question->likert_columns : 
            json_decode($this->question->likert_columns ?? '[]', true);
        
        if (empty($likertRows) || empty($likertColumns)) {
            return '';
        }
        
        $responseCounts = [];
        foreach ($likertRows as $rowIdx => $rowText) {
            $responseCounts[$rowIdx] = array_fill(0, count($likertColumns), 0);
        }
        
        $totalResponses = $this->question->answers->unique('response_id')->count();
        
        foreach ($this->question->answers as $answer) {
            try {
                $likertAnswers = json_decode($answer->answer, true);
                if (is_array($likertAnswers)) {
                    foreach ($likertAnswers as $rowIdx => $colIdx) {
                        if (isset($responseCounts[$rowIdx]) && isset($responseCounts[$rowIdx][$colIdx])) {
                            $responseCounts[$rowIdx][$colIdx]++;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Invalid Likert response: " . $e->getMessage());
            }
        }
        
        // Build result with each statement formatted separately
        $results = [];
        
        foreach ($likertRows as $rowIdx => $rowText) {
            $statementResult = "{$rowText}\n";
            
            $statements = [];
            foreach ($likertColumns as $colIdx => $colText) {
                $count = $responseCounts[$rowIdx][$colIdx];
                if ($count > 0) {
                    $percentage = round(($count / $totalResponses) * 100);
                    $statements[] = "{$percentage}% ({$count}) selected \"{$colText}\"";
                }
            }
            
            if (!empty($statements)) {
                $statementResult .= "Out of {$totalResponses} respondents, " . implode(', ', $statements) . ".";
                $results[] = $statementResult;
            }
        }
        
        return implode("\n\n", $results);
    }

    /**
     * Generate exact counts for date questions
     */
    private function generateDateExactCounts()
    {
        $dates = [];
        foreach ($this->question->answers as $answer) {
            try {
                if (strtotime($answer->answer)) {
                    $dates[] = $answer->answer;
                }
            } catch (\Exception $e) {
                // Skip invalid dates
            }
        }
        
        $totalDates = count($dates);
        if ($totalDates == 0) {
            return "No valid dates were provided by respondents.";
        }
        
        sort($dates);
        $earliestDate = $dates[0];
        $latestDate = $dates[$totalDates - 1];
        
        $dateGroups = [];
        foreach ($dates as $date) {
            $month = date('Y-m', strtotime($date));
            if (!isset($dateGroups[$month])) {
                $dateGroups[$month] = 0;
            }
            $dateGroups[$month]++;
        }
        
        $result = "Based on dates provided by {$totalDates} respondents, ranging from {$earliestDate} to {$latestDate}, ";
        $statements = [];
        
        foreach ($dateGroups as $month => $count) {
            $percentage = round(($count / $totalDates) * 100);
            $formattedMonth = date('F Y', strtotime($month . "-01"));
            $statements[] = "{$percentage}% ({$count}) selected dates in {$formattedMonth}";
        }
        
        return $result . implode(', ', $statements) . '.';
    }

    // A direct setter method for updateAiSummary
    public function updateAiSummary($value)
    {
        $this->aiSummary = $value;
    }

    public function generateSummary()
    {
        Log::info('Generating summary for question ID: ' . $this->question->id);
        $this->loading = true;

        // Generate local exact counts first (already available from mount)
        $exactCounts = $this->generateExactCounts();
        
        // Process answers and generate prompt based on question type
        $questionType = $this->question->question_type;
        $prompt = '';
        
        switch ($questionType) {
            case 'multiple_choice':
                $prompt = "Analyze the patterns and insights from the multiple choice question responses above and provide additional analysis in essay format (150-200 words).\n\n"
                    . "Question: \"{$this->question->question_text}\"\n\n"
                    . "The exact statistics have already been provided. Focus your analysis on:\n"
                    . "- What the distribution patterns might indicate about respondent preferences or behaviors\n"
                    . "- Compare the different selections and their significance\n"
                    . "- Identify any notable trends or unexpected results\n"
                    . "- Provide insights about what these choices suggest in the context of the question\n\n"
                    . "Write in plain text only (no markdown or formatting). Note that respondents could select multiple options.\n";
                break;
                
            case 'radio':
                $prompt = "Analyze the patterns and insights from the single choice question responses above and provide additional analysis in essay format (150-200 words).\n\n"
                    . "Question: \"{$this->question->question_text}\"\n\n"
                    . "The exact statistics have already been provided. Focus your analysis on:\n"
                    . "- What the distribution patterns might indicate about respondent preferences or behaviors\n"
                    . "- Compare the different selections and their significance\n"
                    . "- Identify any notable trends or unexpected results\n"
                    . "- Provide insights about what these choices suggest in the context of the question\n\n"
                    . "Write in plain text only (no markdown or formatting).\n";
                break;
                
            case 'likert':
                $likertRows = is_array($this->question->likert_rows) ? 
                    $this->question->likert_rows : 
                    json_decode($this->question->likert_rows ?? '[]', true);
                
                $prompt = "Analyze the Likert scale response statistics below and provide insights for each statement.\n\n"
                    . "Question: \"{$this->question->question_text}\"\n\n"
                    . "RESPONSE STATISTICS:\n"
                    . $exactCounts . "\n\n"
                    . "Based on the statistics above, provide a 2-3 sentence analysis for each of the " . count($likertRows) . " statements about what the response distribution reveals about respondent attitudes, satisfaction, or opinions. Focus on interpreting the patterns, trends, and what the percentages suggest.\n\n"
                    . "IMPORTANT: You must format your response exactly as follows:\n"
                    . "Statement 1: [2-3 sentence analysis of what the response pattern reveals]\n"
                    . "Continue for all " . count($likertRows) . " statements.\n\n"
                    . "Write in plain text only (no markdown or formatting). Focus on interpreting what the distribution of responses (percentages and counts) tells us about respondent opinions, satisfaction levels, or attitudes for each statement. Do not repeat the statistics or statement text.\n\n"
                    . "Example: 'Statement 1: This indicates that most respondents feel positively about this aspect, with the majority selecting agree or strongly agree options. However, the notable percentage of neutral responses suggests some uncertainty or mixed experiences among a portion of students. This pattern reveals a generally positive but not unanimous sentiment.'\n";
                break;
                
            case 'rating':
                $prompt = "Based on the rating question response statistics provided separately, analyze the patterns and insights in a single paragraph (100-150 words).\n\n"
                    . "Question: \"{$this->question->question_text}\"\n\n"
                    . "The exact statistics and average rating are displayed separately. Focus your analysis on:\n"
                    . "- What the rating distribution suggests about overall satisfaction or quality\n"
                    . "- Any surprising trends or insights from the rating patterns\n\n"
                    . "Write in plain text only (no markdown or formatting). Do not repeat the statistics.\n";
                break;
                
            case 'date':
                $prompt = "Based on the date question response statistics provided separately, analyze the patterns and insights in a single paragraph (100-150 words).\n\n"
                    . "Question: \"{$this->question->question_text}\"\n\n"
                    . "The exact statistics and date distributions are displayed separately. Focus your analysis on:\n"
                    . "- What the clustering of dates might indicate about respondent behavior, preferences, or experiences\n"
                    . "- Interpret the significance of these patterns in the specific context of the question\n"
                    . "- Consider seasonal trends, time periods, or events that might influence the date selection\n\n"
                    . "Write in plain text only (no markdown or formatting). Do not repeat the statistics.\n";
                break;
                
            case 'essay':
            case 'short_text':
                $responses = $this->question->answers->pluck('answer')->filter()->toArray();
                $totalResponses = count($responses);
                $responseText = implode("\n\n", $responses);
                
                $prompt = "Analyze the following " . strtoupper($this->question->question_type) . " question responses and provide a summary in a single paragraph (150-200 words).\n\n"
                    . "Question: \"{$this->question->question_text}\"\n"
                    . "Total responses: {$totalResponses}\n\n"
                    . "Write an insightful summary using plain text only (no markdown or formatting). Begin with \"After analyzing {$totalResponses} responses about [topic of question]...\" and include:\n"
                    . "- The main themes or sentiments that emerged from the responses\n"
                    . "- The most common theme or feeling expressed and general tone (positive, negative, mixed)\n"
                    . "- Contrasting views and less common but meaningful themes\n"
                    . "- Possible interpretations or implications of the response patterns\n"
                    . "- What these mixed responses might suggest about the topic\n\n"
                    . "Responses to analyze:\n" . $responseText;
                break;
                
            default:
                $responses = $this->question->answers->pluck('answer')->filter()->toArray();
                $totalResponses = count($responses);
                $responseText = implode("\n", $responses);
                
                $prompt = "Analyze the following survey question responses and provide a summary in essay format (200-250 words).\n\n"
                    . "Question: \"{$this->question->question_text}\"\n"
                    . "Question Type: {$this->question->question_type}\n"
                    . "Total responses: {$totalResponses}\n\n"
                    . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Begin with:\n\n"
                    . "\"Based on {$totalResponses} responses to this question...\"\n\n"
                    . "Identify patterns, trends, common themes, and notable outliers in the responses.\n\n"
                    . "Responses to analyze:\n" . $responseText;
        }

        $apiKey = env('GEMINI_API_KEY');
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 1000,
                'temperature' => 0.7,
            ]
        ]);

        $json = $response->json();
        $aiAnalysis = '';

        if (
            $response->ok() &&
            isset($json['candidates'][0]['content']['parts'][0]['text'])
        ) {
            $aiAnalysis = $json['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($json['error']['message'])) {
            $aiAnalysis = 'Gemini API error: ' . $json['error']['message'];
        } else {
            $aiAnalysis = 'Failed to generate summary. Please try again.';
        }

        // Special handling for Likert to combine stats with individual analyses
        if ($this->question->question_type === 'likert') {
            $this->aiSummary = $this->combineLikertStatsWithAnalysis($exactCounts, $aiAnalysis);
        } else {
            // For other question types, only store the AI analysis part
            $this->aiSummary = $aiAnalysis;
        }

        // Save only the AI analysis to DB (exact counts are generated dynamically)
        Log::info('API response received. Saving summary for question ID: ' . $this->question->id);
        $this->question->ai_summary = $this->aiSummary;
        $this->question->save();

        $this->loading = false;

        Log::info('Summary generated and saved. AI Summary value: ' . $this->aiSummary);
        
        // Forcefully refresh the component
        $this->dispatch('$refresh');
    }

    /**
     * Combine Likert statistics with AI analysis for each statement
     */
    private function combineLikertStatsWithAnalysis($exactCounts, $aiAnalysis)
    {
        $likertRows = is_array($this->question->likert_rows) ? 
            $this->question->likert_rows : 
            json_decode($this->question->likert_rows ?? '[]', true);
        
        // If AI analysis is empty, return a fallback message
        if (empty(trim($aiAnalysis))) {
            Log::warning("Empty AI analysis received for Likert question ID: " . $this->question->id);
            return "AI analysis could not be generated at this time. Please try again.";
        }
        
        // Parse AI analysis by statements
        $analysisLines = explode("\n", trim($aiAnalysis));
        $statementAnalyses = [];
        
        foreach ($analysisLines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Try to match "Statement X:" pattern
            if (preg_match('/^Statement\s+(\d+):\s*(.+)/', $line, $matches)) {
                $statementIndex = intval($matches[1]) - 1; // Convert to 0-based index
                $analysis = trim($matches[2]);
                if (!empty($analysis)) {
                    $statementAnalyses[$statementIndex] = $analysis;
                }
            }
        }
        
        // If no statements were parsed, return the original AI analysis
        if (empty($statementAnalyses)) {
            Log::warning("Could not parse statement-based analysis for Likert question ID: " . $this->question->id);
            return $aiAnalysis; // Return the raw AI response as fallback
        }
        
        // Build the final analysis results
        $analysisResults = [];
        for ($i = 0; $i < count($likertRows); $i++) {
            if (isset($statementAnalyses[$i])) {
                $analysisResults[] = "Statement " . ($i + 1) . ": " . $statementAnalyses[$i];
            } else {
                // Fallback for missing statement analysis
                $analysisResults[] = "Statement " . ($i + 1) . ": Analysis not available for this statement.";
            }
        }
        
        return implode("\n\n", $analysisResults);
    }

    public function render()
    {
        $this->question->loadMissing('answers.response.user');
        Log::info('Rendering ViewAllResponsesModal with aiSummary: ' . $this->aiSummary);
        return view('livewire.surveys.form-responses.modal.view-all-responses-modal');
    }
}

