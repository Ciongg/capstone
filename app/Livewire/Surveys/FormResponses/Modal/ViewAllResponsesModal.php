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
    public $loading = false;


    public function mount()
    {
        $this->aiSummary = $this->question->ai_summary ?? '';
    }

    public function generateSummary()
    {
        Log::info('Generating summary for question ID: ' . $this->question->id);
        $this->loading = true;

        // Process answers and generate prompt based on question type
        $questionType = $this->question->question_type;
        $prompt = '';
        
        switch ($questionType) {
            case 'multiple_choice':
                $prompt = $this->generateMultipleChoicePrompt();
                break;
                
            case 'radio':
                $prompt = $this->generateRadioPrompt();
                break;
                
            case 'likert':
                $prompt = $this->generateLikertPrompt();
                break;
                
            case 'rating':
                $prompt = $this->generateRatingPrompt();
                break;
                
            case 'date':
                $prompt = $this->generateDatePrompt();
                break;
                
            case 'essay':
            case 'short_text':
                $prompt = $this->generateTextPrompt();
                break;
                
            default:
                // Fallback for any other question types
                $prompt = $this->generateGenericPrompt();
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
                'maxOutputTokens' => 500,
                'temperature' => 0.7,
            ]
        ]);

        $json = $response->json();
        $summary = '';

        if (
            $response->ok() &&
            isset($json['candidates'][0]['content']['parts'][0]['text'])
        ) {
            $summary = $json['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($json['error']['message'])) {
            $summary = 'Gemini API error: ' . $json['error']['message'];
        } else {
            $summary = 'Failed to generate summary. Please try again.';
        }

        // Save to DB first
        Log::info('API response received. Saving summary for question ID: ' . $this->question->id);
        $this->question->ai_summary = $summary;
        $this->question->save();

        // Set the property locally
        $this->aiSummary = $summary;
        $this->loading = false;

        Log::info('Summary generated and saved. AI Summary value: ' . $this->aiSummary);
        
        // Forcefully refresh the component
        $this->dispatch('$refresh');
    }

    /**
     * Generate prompt for multiple choice questions
     */
    private function generateMultipleChoicePrompt()
    {
        // Get all choices for this question
        $choices = $this->question->choices()->get()->pluck('choice_text', 'id')->toArray();
        
        // Process and count the selections
        $choiceCounts = [];
        $totalResponses = 0;
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
                    $totalResponses++;
                }
            } catch (\Exception $e) {
                // Skip invalid responses
                Log::warning("Invalid multiple choice response: " . $e->getMessage());
            }
        }
        
        // Prepare exact counts data
        $exactCountsData = "EXACT COUNTS (DO NOT MODIFY THESE):\n";
        $exactCountsData .= "Total unique respondents: {$uniqueResponses}\n";
        foreach ($choiceCounts as $choiceText => $count) {
            $percentage = $totalResponses > 0 ? round(($count / $uniqueResponses) * 100) : 0;
            $exactCountsData .= "\"{$choiceText}\": EXACTLY {$count} selections ({$percentage}%)\n";
        }
        
        // Get original answers for reference
        $rawAnswers = $this->question->answers->map(function($answer) use ($choices) {
            try {
                $selectedChoices = json_decode($answer->answer, true);
                if (is_array($selectedChoices)) {
                    return array_map(function($id) use ($choices) {
                        return $choices[$id] ?? "Choice ID $id";
                    }, $selectedChoices);
                }
            } catch (\Exception $e) {
                return ["Error parsing answer"];
            }
            return [];
        })->filter()->toArray();
        
        // Build the prompt with instructions specific to multiple choice
        return "Analyze the following MULTIPLE CHOICE question responses and provide a summary in essay format (200-250 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n\n"
            . $exactCountsData . "\n"
            . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Format as follows:\n\n"
            . "FIRST PARAGRAPH: Begin with \"Out of {$uniqueResponses} respondents\" and include all statistical information. "
            . "Write all statements about percentages and counts in this exact format: XX% (Y) selected [choice]. "
            . "Include at least the top 3 choices (if applicable) in this first paragraph.\n\n"
            . "SECOND PARAGRAPH: After presenting all the statistics, start a new paragraph to analyze patterns and provide insights. "
            . "Compare the different selections and explain what the distribution might indicate about respondent preferences or behaviors.\n\n"
            . "IMPORTANT: Always follow this exact format for statistics: XX% (Y) where XX is the percentage and Y is the exact count.\n"
            . "NEVER use the format (XX%, Y) or combine percentage and count in the same parentheses.\n\n"
            . "Note that respondents could select multiple options, so percentages may add up to more than 100%.\n";
    }

    /**
     * Generate prompt for radio questions (single choice)
     */
    private function generateRadioPrompt()
    {
        // Get all choices for this question
        $choices = $this->question->choices()->get()->pluck('choice_text', 'id')->toArray();
        
        // Process and count the selections
        $choiceCounts = array_fill_keys(array_values($choices), 0);
        $totalResponses = $this->question->answers->unique('response_id')->count();
        
        foreach ($this->question->answers as $answer) {
            $choiceId = $answer->answer;
            if (isset($choices[$choiceId])) {
                $choiceText = $choices[$choiceId];
                $choiceCounts[$choiceText]++;
            }
        }
        
        // Prepare exact counts data
        $exactCountsData = "EXACT COUNTS (DO NOT MODIFY THESE):\n";
        $exactCountsData .= "Total respondents: {$totalResponses}\n";
        foreach ($choiceCounts as $choiceText => $count) {
            $percentage = $totalResponses > 0 ? round(($count / $totalResponses) * 100) : 0;
            $exactCountsData .= "\"{$choiceText}\": EXACTLY {$count} responses ({$percentage}%)\n";
        }
        
        // Build the prompt with instructions specific to radio/single-choice
        return "Analyze the following SINGLE CHOICE question responses and provide a summary in essay format (200-250 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n\n"
            . $exactCountsData . "\n"
            . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Format as follows:\n\n"
            . "FIRST PARAGRAPH: Begin with \"Out of {$totalResponses} respondents\" and include all statistical information. "
            . "Write all statements about percentages and counts in this exact format: XX% (Y) selected [choice]. "
            . "Include all choices that received responses in this first paragraph.\n\n"
            . "SECOND PARAGRAPH: After presenting all the statistics, start a new paragraph to analyze patterns and provide insights. "
            . "Compare the different selections and explain what the distribution might indicate about respondent preferences or behaviors.\n\n"
            . "IMPORTANT: Always follow this exact format for statistics: XX% (Y) where XX is the percentage and Y is the exact count.\n"
            . "NEVER use the format (XX%, Y) or combine percentage and count in the same parentheses.\n";
    }

    /**
     * Generate prompt for Likert scale questions
     */
    private function generateLikertPrompt()
    {
        // Get Likert scale rows and columns
        $likertRows = is_array($this->question->likert_rows) ? 
            $this->question->likert_rows : 
            json_decode($this->question->likert_rows ?? '[]', true);
            
        $likertColumns = is_array($this->question->likert_columns) ? 
            $this->question->likert_columns : 
            json_decode($this->question->likert_columns ?? '[]', true);
        
        if (empty($likertRows) || empty($likertColumns)) {
            return $this->generateGenericPrompt(); // Fallback if Likert data is missing
        }
        
        // Create a matrix to count responses for each row/column combination
        $responseCounts = [];
        foreach ($likertRows as $rowIdx => $rowText) {
            $responseCounts[$rowIdx] = array_fill(0, count($likertColumns), 0);
        }
        
        // Process answers
        $totalResponses = $this->question->answers->unique('response_id')->count();
        $processedResponses = 0;
        
        foreach ($this->question->answers as $answer) {
            try {
                $likertAnswers = json_decode($answer->answer, true);
                if (is_array($likertAnswers)) {
                    foreach ($likertAnswers as $rowIdx => $colIdx) {
                        if (isset($responseCounts[$rowIdx]) && isset($responseCounts[$rowIdx][$colIdx])) {
                            $responseCounts[$rowIdx][$colIdx]++;
                        }
                    }
                    $processedResponses++;
                }
            } catch (\Exception $e) {
                // Skip invalid responses
                Log::warning("Invalid Likert response: " . $e->getMessage());
            }
        }
        
        // Prepare exact counts data with percentage breakdowns per row
        $exactCountsData = "EXACT COUNTS PER STATEMENT (DO NOT MODIFY THESE):\n";
        $exactCountsData .= "Total respondents: {$totalResponses}\n\n";
        
        foreach ($likertRows as $rowIdx => $rowText) {
            $exactCountsData .= "Statement: \"{$rowText}\"\n";
            
            foreach ($likertColumns as $colIdx => $colText) {
                $count = $responseCounts[$rowIdx][$colIdx];
                $percentage = $totalResponses > 0 ? round(($count / $totalResponses) * 100) : 0;
                $exactCountsData .= "- \"{$colText}\": {$percentage}% ({$count})\n";
            }
            $exactCountsData .= "\n";
        }
        
        // Extract the topic from the question text or use a default
        $questionTopic = $this->extractTopicFromQuestion($this->question->question_text, $likertRows);
        
        // Build a prompt specific to Likert analysis with format matching multiple choice/radio
        return "Analyze the following LIKERT SCALE question responses and provide a detailed summary in essay format (MAX 300 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n\n"
            . $exactCountsData 
            . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Format as follows:\n\n"
            . "FIRST PARAGRAPH: Begin with \"Based on responses from {$totalResponses} participants regarding {$questionTopic},\" and briefly introduce the overall sentiment (positive/negative/mixed).\n\n"
            . "STATISTICAL PARAGRAPHS: Create one paragraph for EACH statement. For each statement:\n"
            . "\"The statement [statement text] reveals [observation], with XX% (Y) selecting [response option] and XX% (Y) selecting [response option]...\" Include all relevant response options with their exact percentages and counts.\n\n" 
            . "CONCLUSION PARAGRAPH: After presenting all the statistics for all statements, add a final paragraph that analyzes patterns across statements and what these results suggest about {$questionTopic} overall.\n\n"
            . "IMPORTANT:\n"
            . "1. Always write percentages as XX% followed by the exact count in parentheses like: 27% (8).\n"
            . "2. NEVER use the format (XX%, Y) or put both percentage and count in the same parentheses.\n"
            . "3. Ensure your analysis stays within a maximum of 300 words total.\n"
            . "4. Make sure to analyze ALL statements before concluding.\n";
    }
    
    /**
     * Helper method to extract a topic from the question text or likert rows
     */
    private function extractTopicFromQuestion($questionText, $likertRows)
    {
        // Try to extract topic from question text (after "about" or "regarding" or similar phrases)
        $topicPhrases = ['about', 'regarding', 'concerning', 'related to', 'on', 'with'];
        
        foreach ($topicPhrases as $phrase) {
            if (stripos($questionText, $phrase . ' ') !== false) {
                $parts = explode($phrase . ' ', strtolower($questionText), 2);
                if (isset($parts[1])) {
                    // Clean up the extracted topic
                    $topic = trim($parts[1]);
                    // Remove trailing punctuation
                    $topic = rtrim($topic, '.,:;');
                    return $topic;
                }
            }
        }
        
        // If no topic found from question, try to infer from first likert row
        if (!empty($likertRows)) {
            $firstRow = reset($likertRows);
            if (strpos($firstRow, 'advisor') !== false) {
                return 'academic advising';
            } elseif (strpos($firstRow, 'professor') !== false || strpos($firstRow, 'faculty') !== false) {
                return 'faculty performance';
            } elseif (strpos($firstRow, 'course') !== false) {
                return 'course quality';
            }
        }
        
        // Default topic if nothing else works
        return 'this topic';
    }

    /**
     * Generate prompt for rating questions
     */
    private function generateRatingPrompt()
    {
        // Get the number of stars for this question
        $stars = $this->question->stars ?? 5;
        
        // Count ratings
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
        
        // Calculate average rating
        $averageRating = $totalRatings > 0 ? round($sum / $totalRatings, 1) : 0;
        
        // Prepare exact counts data
        $exactCountsData = "EXACT COUNTS (DO NOT MODIFY THESE):\n";
        $exactCountsData .= "Total respondents: {$totalRatings}\n";
        $exactCountsData .= "Average rating: {$averageRating} out of {$stars}\n\n";
        
        foreach ($ratingCounts as $rating => $count) {
            $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100) : 0;
            $exactCountsData .= "{$rating} star" . ($rating != 1 ? "s" : "") . ": {$count} responses ({$percentage}%)\n";
        }
        
        // Build the prompt specifically for rating analysis with two-paragraph structure
        return "Analyze the following RATING question responses and provide a summary in essay format (200-250 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n"
            . "Rating Scale: 1 to {$stars} stars\n\n"
            . $exactCountsData . "\n"
            . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Format as follows:\n\n"
            . "FIRST PARAGRAPH: Begin with \"Based on feedback from {$totalRatings} respondents, the average rating was {$averageRating} out of {$stars} stars.\" Then include all statistical information including the distribution of ratings. Write all statements about percentages and counts in this exact format: XX% (Y) gave a rating of [rating]. Include the breakdown of all ratings (1-{$stars} stars).\n\n"
            . "SECOND PARAGRAPH: After presenting all the statistics, analyze patterns and provide insights. Compare the different rating levels and explain what the distribution might indicate. Discuss what percentage gave positive ratings (4-{$stars}) versus negative ratings (1-2), and interpret what this suggests about overall satisfaction or quality.\n\n"
            . "IMPORTANT: Always follow this exact format for statistics: XX% (Y) where XX is the percentage and Y is the exact count.\n"
            . "NEVER use the format (XX%, Y) or combine percentage and count in the same parentheses.\n";
    }

    /**
     * Generate prompt for date questions
     */
    private function generateDatePrompt()
    {
        // Extract dates from answers
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
        
        // Analyze date patterns
        $totalDates = count($dates);
        if ($totalDates > 0) {
            sort($dates);
            $earliestDate = $dates[0];
            $latestDate = $dates[$totalDates - 1];
            
            // Group dates by month/year
            $dateGroups = [];
            foreach ($dates as $date) {
                $month = date('Y-m', strtotime($date));
                if (!isset($dateGroups[$month])) {
                    $dateGroups[$month] = 0;
                }
                $dateGroups[$month]++;
            }
            
            // Prepare date distribution data
            $dateDistribution = "Date distribution:\n";
            foreach ($dateGroups as $month => $count) {
                $percentage = round(($count / $totalDates) * 100);
                $formattedMonth = date('F Y', strtotime($month . "-01"));
                $dateDistribution .= "{$formattedMonth}: {$count} responses ({$percentage}%)\n";
            }
        } else {
            $earliestDate = "N/A";
            $latestDate = "N/A";
            $dateDistribution = "No valid dates provided.";
        }
        
        // Prepare data for the prompt
        $exactCountsData = "EXACT COUNTS (DO NOT MODIFY THESE):\n";
        $exactCountsData .= "Total respondents: {$totalDates}\n";
        $exactCountsData .= "Earliest date: {$earliestDate}\n";
        $exactCountsData .= "Latest date: {$latestDate}\n\n";
        $exactCountsData .= $dateDistribution;
        
        // Build a prompt for date analysis with a clear two-paragraph structure
        return "Analyze the following DATE question responses and provide a summary in essay format (200-250 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n\n"
            . $exactCountsData . "\n"
            . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Format as follows:\n\n"
            . "FIRST PARAGRAPH: Begin with \"Based on dates provided by {$totalDates} respondents, ranging from {$earliestDate} to {$latestDate}...\" Then include all date distribution statistics. Identify which months/periods have the highest concentration of responses and highlight any patterns in the date selection. Include specific counts and percentages for key date ranges.\n\n"
            . "SECOND PARAGRAPH: After presenting the date statistics, provide analysis and insights about what these date patterns suggest in relation to the question asked. Consider what the clustering of dates might indicate about respondent behavior, preferences, or experiences. Interpret the significance of these patterns in the specific context of the question.\n\n"
            . "IMPORTANT: Always write percentages as XX% followed by the exact count in parentheses like: 27% (8).\n"
            . "NEVER use the format (XX%, Y) or combine percentage and count in the same parentheses.\n";
    }

    /**
     * Generate prompt for essay and short text questions
     */
    private function generateTextPrompt()
    {
        // Collect all text responses
        $responses = $this->question->answers->pluck('answer')->filter()->toArray();
        $totalResponses = count($responses);
        
        // Get the full text of responses for analysis
        $responseText = implode("\n\n", $responses);
        
        // Build a prompt for text analysis with specific instructions for each paragraph
        return "Analyze the following " . strtoupper($this->question->question_type) . " question responses and provide a summary in essay format strictly (200-250 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n"
            . "Total responses: {$totalResponses}\n\n"
            . "Write an insightful two-paragraph summary using plain text only (no markdown or formatting).\n\n"
            . "PARAGRAPH 1 - INTRODUCTION AND OVERVIEW: Begin with \"After analyzing {$totalResponses} responses about [topic of question]...\" and introduce the main themes or sentiments that emerged from the responses. Include the total number of respondents, what the open-ended question was about, the most common theme or feeling expressed, and the general tone of responses (positive, negative, mixed, etc.).\n\n"
            . "PARAGRAPH 2 - SUPPORTING THEMES, CONTRASTS & DEEPER INTERPRETATION: Dig deeper into the variety of responses, highlighting contrasting views and offering possible interpretations or implications. Include examples of less common but meaningful themes, contrasting opinions (e.g., positive vs. negative, hopeful vs. concerned), what these mixed responses might imply, and possible reasons behind certain emotional trends or patterns observed.\n\n"
            . "Responses to analyze:\n" . $responseText;
    }

    /**
     * Generate a generic prompt for any other question type
     */
    private function generateGenericPrompt()
    {
        // Collect all responses
        $responses = $this->question->answers->pluck('answer')->filter()->toArray();
        $totalResponses = count($responses);
        
        // Get the full text of responses
        $responseText = implode("\n", $responses);
        
        // Build a generic prompt
        return "Analyze the following survey question responses and provide a summary in essay format (200-250 words).\n\n"
            . "Question: \"{$this->question->question_text}\"\n"
            . "Question Type: {$this->question->question_type}\n"
            . "Total responses: {$totalResponses}\n\n"
            . "Write an insightful essay-style summary using plain text only (no markdown or formatting). Begin with:\n\n"
            . "\"Based on {$totalResponses} responses to this question...\"\n\n"
            . "Identify patterns, trends, common themes, and notable outliers in the responses.\n\n"
            . "Responses to analyze:\n" . $responseText;
    }

    // A direct setter method for updateAiSummary
    public function updateAiSummary($value)
    {
        $this->aiSummary = $value;
    }

    public function render()
    {
        $this->question->loadMissing('answers.response.user');
        Log::info('Rendering ViewAllResponsesModal with aiSummary: ' . $this->aiSummary);
        return view('livewire.surveys.form-responses.modal.view-all-responses-modal');
    }
}

