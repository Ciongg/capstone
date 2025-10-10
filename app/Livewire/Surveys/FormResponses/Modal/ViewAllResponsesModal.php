<?php

namespace App\Livewire\Surveys\FormResponses\Modal;

use App\Models\SurveyQuestion;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ViewAllResponsesModal extends Component
{
    public SurveyQuestion $question;
    public $aiSummary = '';
    public $exactCounts = '';
    public $loading = false;
    public $selectedModel = 'deepseek';

    public function mount($question)
    {
        $this->question = $question;
        $this->aiSummary = $this->question->ai_summary ?? '';
        $this->exactCounts = $this->generateExactCounts();
    }
    
    // Reload question data on every render to ensure fresh data
    public function render()
    {
        // Refresh the question with all relationships
        $this->question->loadMissing(['answers.response.user', 'choices', 'answers.response']);
        
        // Regenerate exact counts with fresh data
        if (empty($this->exactCounts) || $this->exactCounts === '') {
            $this->exactCounts = $this->generateExactCounts();
        }
        
        Log::info('Rendering ViewAllResponsesModal with aiSummary: ' . $this->aiSummary);
        return view('livewire.surveys.form-responses.modal.view-all-responses-modal');
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
            $month = Carbon::parse($date)->format('Y-m');
            if (!isset($dateGroups[$month])) {
                $dateGroups[$month] = 0;
            }
            $dateGroups[$month]++;
        }
        
        $result = "Based on dates provided by {$totalDates} respondents, ranging from {$earliestDate} to {$latestDate}, ";
        $statements = [];
        
        foreach ($dateGroups as $month => $count) {
            $percentage = round(($count / $totalDates) * 100);
            $formattedMonth = Carbon::parse($month . "-01")->format('F Y');
            $statements[] = "{$percentage}% ({$count}) selected dates in {$formattedMonth}";
        }
        
        return $result . implode(', ', $statements) . '.';
    }

    // A direct setter method for updateAiSummary
    public function updateAiSummary($value)
    {
        $this->aiSummary = $value;
    }

    /**
     * Generate summary using DeepSeek API
     * 
     * @param string $prompt
     * @return string|null
     */
    protected function generateSummaryWithDeepSeek($prompt)
    {
        $deepseekPrompt = "You are an expert survey data analyst. Provide ONLY your final analysis without showing your thinking process or reasoning steps.\n\n"
            . "ACCEPT: Direct analysis only\n\n"
            . "ANALYSIS REQUEST:\n{$prompt}\n\n"
            . "Provide DIRECT analysis (no thinking process):";

        $endpoint = rtrim(config('services.deepseek.endpoint'), '/');
        $apiKey = config('services.deepseek.api_key');
        $modelName = "DeepSeek-R1-0528";
        $apiVersion = "2024-05-01-preview";

        $apiUrl = "{$endpoint}/openai/deployments/{$modelName}/chat/completions?api-version={$apiVersion}";

        Log::info("Calling DeepSeek for summary generation at: {$apiUrl}");

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->timeout(30)->post($apiUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert survey data analyst. Provide only direct analysis without any thinking process.'],
                    ['role' => 'user', 'content' => $deepseekPrompt]
                ],
                'max_tokens' => 1000,
                'temperature' => 1,
            ]);

            if ($response->successful() && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                
                // Parse the response to extract only the final analysis, similar to translation parsing
                $content = $this->parseDeepSeekAnalysisResponse($content);
                
                Log::info('DeepSeek summary generation successful.');
                Log::info('Generated analysis: ' . $content);
                return $content;
            } else {
                Log::error('DeepSeek summary generation failed: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('DeepSeek summary generation exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse DeepSeek response to extract only the final analysis content
     * Similar to how translation parsing works in AnswerSurvey
     * 
     * @param string $content
     * @return string
     */
    private function parseDeepSeekAnalysisResponse($content)
    {
        // Remove <think> tags and their content completely
        $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
        
        // Split by lines and look for analysis patterns
        $lines = explode("\n", $content);
        $analysisLines = [];
        $inThinking = false;
        $foundAnalysis = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip obvious thinking indicators
            if (preg_match('/^(Let me|I need to|First,|Looking at|The data shows that)/i', $line) ||
                preg_match('/^(Thinking|Analysis process|Reasoning):/i', $line) ||
                str_contains(strtolower($line), 'thinking process') ||
                str_contains(strtolower($line), 'let me analyze')) {
                $inThinking = true;
                continue;
            }
            
            // Look for content that starts the actual analysis
            if (preg_match('/^(The|This|Based|Overall|Out of|After analyzing)/i', $line) && strlen($line) > 20) {
                $inThinking = false;
                $foundAnalysis = true;
            }
            
            // For Likert questions, look for "Statement X:" pattern
            if (preg_match('/^Statement\s+\d+:/i', $line)) {
                $inThinking = false;
                $foundAnalysis = true;
            }
            
            // Collect analysis lines
            if (!$inThinking && ($foundAnalysis || strlen($line) > 30)) {
                $analysisLines[] = $line;
            }
        }
        
        // If we found structured analysis, return it
        if (!empty($analysisLines)) {
            $result = implode("\n", $analysisLines);
            return trim($result);
        }
        
        // Fallback: use the cleaned content
        $cleanedContent = $this->cleanDeepSeekResponse($content);
        return !empty($cleanedContent) ? $cleanedContent : $content;
    }

    /**
     * Clean DeepSeek response to remove thinking process
     * 
     * @param string $content
     * @return string
     */
    private function cleanDeepSeekResponse($content)
    {
        // Remove <think> tags and their content
        $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
        
        // Remove any content between thinking markers
        $content = preg_replace('/\*\*Thinking:\*\*.*?(?=\*\*Analysis:\*\*|\*\*Response:\*\*|$)/s', '', $content);
        $content = preg_replace('/\*\*Analysis:\*\*\s*/s', '', $content);
        $content = preg_replace('/\*\*Response:\*\*\s*/s', '', $content);
        
        // Remove lines that start with common thinking indicators
        $lines = explode("\n", $content);
        $cleanLines = [];
        $skipThinking = false;
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Skip lines that indicate thinking process
            if (preg_match('/^(Let me|I need to|First,|Looking at|The data shows that)/i', $trimmedLine) ||
                preg_match('/^(Thinking|Analysis|Reasoning):/i', $trimmedLine) ||
                str_contains($trimmedLine, 'thinking process') ||
                str_contains($trimmedLine, 'mental') && str_contains($trimmedLine, 'analysis')) {
                $skipThinking = true;
                continue;
            }
            
            // If we find content that looks like final analysis, start including
            if (preg_match('/^(The|This|Based|Overall|In conclusion)/i', $trimmedLine) && strlen($trimmedLine) > 20) {
                $skipThinking = false;
            }
            
            if (!$skipThinking && !empty($trimmedLine)) {
                $cleanLines[] = $line;
            }
        }
        
        $cleanedContent = implode("\n", $cleanLines);
        
        // Final cleanup - trim and ensure we have content
        $cleanedContent = trim($cleanedContent);
        
        return !empty($cleanedContent) ? $cleanedContent : $content;
    }

    /**
     * Generate summary using Gemini API
     * 
     * @param string $prompt
     * @return string|null
     */
    protected function generateSummaryWithGemini($prompt)
    {
        $apiKey = config('services.gemini.api_key');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
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

            if ($response->successful() && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                Log::info('Gemini summary generation successful.');
                return $response['candidates'][0]['content']['parts'][0]['text'];
            } else {
                Log::error('Gemini summary generation failed: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini summary generation exception: ' . $e->getMessage());
            return null;
        }
    }

    public function generateSummary()
    {
        $questionTitle = $this->question->question_text;
        $choices = $this->question->choices()->get();
        $answers = $this->question->answers;
        $totalRespondents = $answers->unique('response_id')->count();
        $choiceCounts = [];
        $choiceTexts = [];

        if ($this->question->question_type === 'likert') {
            // Build per-statement distributions
            $likertRows = is_array($this->question->likert_rows) ? $this->question->likert_rows : (json_decode($this->question->likert_rows ?? '[]', true) ?: []);
            $likertColumns = is_array($this->question->likert_columns) ? $this->question->likert_columns : (json_decode($this->question->likert_columns ?? '[]', true) ?: []);
            $groupedAnswers = $answers->groupBy('response_id');
            $rowDistributions = [];
            $rowCounts = [];
            foreach ($likertRows as $rowIdx => $rowText) {
                $rowCounts[$rowIdx] = array_fill(0, count($likertColumns), 0);
            }
            foreach ($groupedAnswers as $responseId => $respAnswers) {
                $likertAnswerData = json_decode($respAnswers->first()?->answer, true);
                if (is_array($likertAnswerData)) {
                    foreach ($likertRows as $rowIdx => $rowText) {
                        $colIdx = $likertAnswerData[$rowIdx] ?? null;
                        if ($colIdx !== null && isset($rowCounts[$rowIdx][$colIdx])) {
                            $rowCounts[$rowIdx][$colIdx]++;
                        }
                    }
                }
            }
            // Build distribution strings
            foreach ($likertRows as $rowIdx => $rowText) {
                $distParts = [];
                foreach ($likertColumns as $colIdx => $colText) {
                    $count = $rowCounts[$rowIdx][$colIdx];
                    $percentage = $totalRespondents > 0 ? round(($count / $totalRespondents) * 100) : 0;
                    $distParts[] = $percentage . "% (" . $count . ") selected \"" . $colText . "\"";
                }
                $rowDistributions[] = $rowText . "\nOut of {$totalRespondents} respondents, " . implode(", ", $distParts) . ".";
            }
            $likertBlock = implode("\n\n", $rowDistributions);
            $prompt = "For each statement below, write a detailed, direct essay-style insight (3-4 sentences, 60-120 words) about what the response distribution reveals about respondent attitudes, satisfaction, or opinions. Each insight should be as thorough as the summary for a multiple choice question, interpreting trends, implications, and what the results suggest. Do NOT repeat the statistics. Do NOT include any thinking process or reasoning steps. Only provide the interpretation/insight. Do NOT use any Markdown, asterisks, or bold formatting—return only raw text.\n\nFormat your response as follows:\n[Statement text]\n[Insight]\n\nContinue for each statement.\n\nStatements and distributions:\n" . $likertBlock;
            // Use the selected model, fallback to the other if it fails
            $insight = null;
            if ($this->selectedModel === 'deepseek') {
                $insight = $this->generateSummaryWithDeepSeek($prompt);
                if ($insight) {
                    // Post-process DeepSeek output for newlines between pairs
                    $lines = preg_split('/\r\n|\r|\n/', $insight);
                    $formatted = [];
                    foreach ($lines as $i => $line) {
                        $formatted[] = $line;
                        // Insert blank line after every insight (every odd line)
                        if ($i % 2 === 1 && trim($line) !== '') {
                            $formatted[] = '';
                        }
                    }
                    $insight = implode(PHP_EOL, $formatted);
                }
                if (!$insight) {
                    $insight = $this->generateSummaryWithGemini($prompt);
                }
            } else {
                $insight = $this->generateSummaryWithGemini($prompt);
                if (!$insight) {
                    $insight = $this->generateSummaryWithDeepSeek($prompt);
                }
            }
            if (!$insight) {
                $insight = 'Data summarization incomplete. Please try again later or contact support if the issue persists.';
            }
            $this->aiSummary = $insight;
            $this->question->ai_summary = $insight;
            $this->question->save();
            $this->dispatch('$refresh');
            return;
        }

        // Count answers for each choice
        foreach ($choices as $choice) {
            $choiceTexts[$choice->id] = $choice->choice_text;
            $choiceCounts[$choice->id] = 0;
        }
        foreach ($answers as $answer) {
            $decoded = json_decode($answer->answer, true);
            if (is_array($decoded)) {
                foreach ($decoded as $choiceId) {
                    if (isset($choiceCounts[$choiceId])) {
                        $choiceCounts[$choiceId]++;
                    }
                }
            } else {
                if (isset($choiceCounts[$answer->answer])) {
                    $choiceCounts[$answer->answer]++;
                }
            }
        }

        // Build the distribution string for the prompt
        $distributionParts = [];
        foreach ($choiceCounts as $choiceId => $count) {
            $distributionParts[] = $choiceTexts[$choiceId] . ": " . $count;
        }
        $distributionString = implode(", ", $distributionParts);

        // Compose the prompt for the LLM (insight only)
        $statsBlock = "Question: {$questionTitle}\nTotal respondents: {$totalRespondents}\nDistribution: {$distributionString}";
        $prompt = "Based on the following survey question and answer distribution, write a concise, direct essay-style insight (3-4 sentences, 60-120 words) about what the distribution reveals about respondent preferences, trends, or implications. Do NOT repeat the distribution or statistics. Do NOT include any thinking process or reasoning steps. Only provide the interpretation/insight. Do NOT use any Markdown, asterisks, or bold formatting—return only raw text.\n\n" . $statsBlock;

        // Use the selected model, fallback to the other if it fails
        $insight = null;
        if ($this->selectedModel === 'deepseek') {
            $insight = $this->generateSummaryWithDeepSeek($prompt);
            if (!$insight) {
                $insight = $this->generateSummaryWithGemini($prompt);
            }
        } else {
            $insight = $this->generateSummaryWithGemini($prompt);
            if (!$insight) {
                $insight = $this->generateSummaryWithDeepSeek($prompt);
            }
        }
        if (!$insight) {
            $insight = 'Data summarization incomplete. Please try again later or contact support if the issue persists.';
        }

        // Only use the LLM-generated insight as the summary
        $this->aiSummary = $insight;
        $this->question->ai_summary = $insight;
        $this->question->save();
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
}


