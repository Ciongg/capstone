<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use App\Models\SurveyGenerationJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateSurveyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 2;
    
    protected $survey;
    protected $abstract;
    protected $maxPages;
    protected $maxQuestionsPerPage;
    protected $generationType;
    protected $selectedIsoCategories;
    protected $likertPoints;
    protected $jobId;

    /**
     * Create a new job instance.
     *
     * @param Survey $survey
     * @param string $abstract
     * @param int $maxPages
     * @param int $maxQuestionsPerPage
     * @param string $generationType
     * @param array $selectedIsoCategories
     * @param int $likertPoints
     * @param int $jobId
     */
    public function __construct(
        Survey $survey,
        string $abstract,
        int $maxPages,
        int $maxQuestionsPerPage,
        string $generationType,
        array $selectedIsoCategories = [],
        int $likertPoints = 5,
        int $jobId = null
    ) {
        $this->survey = $survey;
        $this->abstract = $abstract;
        $this->maxPages = $maxPages;
        $this->maxQuestionsPerPage = $maxQuestionsPerPage;
        $this->generationType = $generationType;
        $this->selectedIsoCategories = $selectedIsoCategories;
        $this->likertPoints = $likertPoints;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update job status to processing
        $job = SurveyGenerationJob::find($this->jobId);
        if ($job) {
            $job->status = 'processing';
            $job->save();
        }

        try {
            // Log the job execution
            Log::info('AI Survey Generation job started', [
                'survey_id' => $this->survey->id,
                'job_id' => $this->jobId,
                'generationType' => $this->generationType
            ]);
            
            // For ISO surveys, use the custom template
            if ($this->generationType === 'iso') {
                $this->deleteExistingSurveyContent();
                
                // Use the ISO template with selected categories and Likert points
                $createdItems = $this->createISOTemplate();
                
                if ($job) {
                    $job->status = 'completed';
                    $job->result = json_encode([
                        'success' => true,
                        'message' => "ISO25010 survey structure created with {$createdItems['pages']} pages and {$createdItems['questions']} questions.",
                        'stats' => $createdItems
                    ]);
                    $job->save();
                }
                
                return;
            }
            
            // For normal surveys, proceed with AI generation
            $result = $this->callAIService($this->generationType);
            
            if ($result) {
                // Delete all existing content before processing the AI result
                $this->deleteExistingSurveyContent();
                
                // Process the generated content and create the survey structure
                $createdItems = $this->processSurveyStructure($result);
                
                // Update job status
                if ($job) {
                    $job->status = 'completed';
                    $job->result = json_encode([
                        'success' => true,
                        'message' => "Survey generation complete! Created {$createdItems['pages']} pages with {$createdItems['questions']} questions.",
                        'stats' => $createdItems
                    ]);
                    $job->save();
                }
            } else {
                throw new \Exception('Failed to generate survey structure. Please try again with a different description.');
            }
            
        } catch (\Exception $e) {
            Log::error('Survey generation error in job: ' . $e->getMessage(), [
                'survey_id' => $this->survey->id,
                'job_id' => $this->jobId,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update job status to failed
            if ($job) {
                $job->status = 'failed';
                $job->result = json_encode([
                    'success' => false,
                    'message' => 'Failed to generate survey: ' . $e->getMessage()
                ]);
                $job->save();
            }
        }
    }
    
    /**
     * Delete all existing questions and pages for the survey
     */
    private function deleteExistingSurveyContent()
    {
        DB::transaction(function () {
            // Delete all questions and their choices
            SurveyQuestion::where('survey_id', $this->survey->id)->each(function ($question) {
                $question->choices()->delete();
                $question->delete();
            });

            // Delete all pages
            SurveyPage::where('survey_id', $this->survey->id)->delete();
            
            Log::info('Deleted existing survey content before AI generation', [
                'survey_id' => $this->survey->id,
                'job_id' => $this->jobId
            ]);
        });
    }
    
    /**
     * Call the AI service to generate survey structure
     */
    private function callAIService($type)
    {
        // Try DeepSeek first, then fall back to Gemini if it fails
        $result = $this->generateWithDeepSeek($type);
        
        if (!$result) {
            Log::info('DeepSeek failed, trying Gemini...', ['survey_id' => $this->survey->id]);
            $result = $this->generateWithGemini($type);
        }
        
        // Additional validation of the result structure
        if ($result) {
            if (!isset($result['pages']) || !is_array($result['pages']) || empty($result['pages'])) {
                Log::error('AI returned invalid survey structure', [
                    'survey_id' => $this->survey->id,
                    'result' => $result
                ]);
                
                // Create a basic valid structure if the AI failed to provide one
                return [
                    'pages' => [
                        [
                            'title' => 'Survey Page',
                            'subtitle' => 'Generated from your description',
                            'questions' => [
                                [
                                    'question_text' => 'How would you rate this?',
                                    'question_type' => 'rating',
                                    'required' => true,
                                    'stars' => 5
                                ],
                                [
                                    'question_text' => 'Any additional comments?',
                                    'question_type' => 'essay',
                                    'required' => false
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Generate survey structure using DeepSeek API
     * 
     * @param string $type The type of survey (normal or ISO)
     * @return array|null JSON structure for the survey or null on failure
     */
    private function generateWithDeepSeek($type)
    {
        $endpoint = rtrim(config('services.deepseek.endpoint'), '/');
        $apiKey = config('services.deepseek.api_key');
        $modelName = "DeepSeek-R1-0528";
        $apiVersion = "2024-05-01-preview";
        $apiUrl = "{$endpoint}/openai/deployments/{$modelName}/chat/completions?api-version={$apiVersion}";
        
        $prompt = $this->createSurveyPrompt($type);
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->timeout(120)->post($apiUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert survey designer.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 16000, // Increased from 8912 to 16000
                'temperature' => 0.7
            ]);
            
            if ($response->successful() && isset($response['choices'][0]['message']['content'])) {
                return $this->extractJsonFromResponse($response['choices'][0]['message']['content']);
            } else {
                Log::error('DeepSeek API error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('DeepSeek API call failed in job: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Generate survey structure using Gemini API
     * 
     * @param string $type The type of survey (normal or ISO)
     * @return array|null JSON structure for the survey or null on failure
     */
    private function generateWithGemini($type)
    {
        $apiKey = config('services.gemini.api_key');
        $prompt = $this->createSurveyPrompt($type);
        
        try {
            // Fix: Use correct model name - remove the version from the model name
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(120)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 16000,
                    'temperature' => 0.7,
                    'topP' => 0.95,
                    'topK' => 40,
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE']
                ]
            ]);
            
            if ($response->successful() && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                return $this->extractJsonFromResponse($response['candidates'][0]['content']['parts'][0]['text']);
            } else {
                $errorDetails = $response->json() ?? 'No details available';
                Log::error('Gemini API error: ' . json_encode($errorDetails));
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call failed in job: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Create a standard ISO25010 template with selected categories only
     * 
     * @return array Statistics about created items
     */
    private function createISOTemplate()
    {
        $stats = [
            'pages' => 0,
            'questions' => 0
        ];
        
        DB::beginTransaction();
        
        try {
            // Define the Likert columns based on the selected number of points
            $likertColumns = $this->getLikertColumns($this->likertPoints);
            
            // Build pages array from selected ISO categories
            $pages = [];
            
            // Define ISO categories
            $isoCategories = [
                'general' => ['title' => 'General Information', 'subtitle' => 'Basic information about the software being evaluated'],
                'functional' => ['title' => 'Functional Suitability', 'subtitle' => 'Does the software do what it is supposed to do accurately and sufficiently?'],
                'performance' => ['title' => 'Performance Efficiency', 'subtitle' => 'How fast and resource-friendly is the software under normal conditions?'],
                'compatibility' => ['title' => 'Compatibility', 'subtitle' => 'Can the software operate in diverse environments with other products?'],
                'usability' => ['title' => 'Usability', 'subtitle' => 'Is the system easy, efficient, and pleasant for users to interact with?'],
                'reliability' => ['title' => 'Reliability', 'subtitle' => 'How stable and fault-tolerant is the system in real-world usage?'],
                'security' => ['title' => 'Security', 'subtitle' => 'Does the software prevent unauthorized access and protect data integrity?'],
                'maintainability' => ['title' => 'Maintainability', 'subtitle' => 'How easy is it to update or fix issues in the system?'],
                'portability' => ['title' => 'Portability', 'subtitle' => 'Can the software run across different platforms or be easily installed?'],
                'overall' => ['title' => 'Overall Rating and Comments', 'subtitle' => 'Provide your overall assessment and feedback'],
            ];
            
            foreach ($isoCategories as $key => $category) {
                // Skip if this category is not selected
                if (empty($this->selectedIsoCategories[$key])) {
                    continue;
                }
                
                // Add the category to pages array
                $page = [
                    'title' => $category['title'],
                    'subtitle' => $category['subtitle'],
                    'questions' => []
                ];
                
                // Add appropriate questions based on category
                if ($key === 'general') {
                    $page['questions'] = [
                        [
                            'question_text' => 'Software/Product Name',
                            'question_type' => 'short_text',
                            'required' => true,
                        ],
                        [
                            'question_text' => 'Evaluator Name',
                            'question_type' => 'short_text',
                            'required' => true,
                        ]
                    ];
                } else if ($key === 'overall') {
                    $page['questions'] = [
                        [
                            'question_text' => 'Overall software quality rating',
                            'question_type' => 'rating',
                            'required' => true,
                            'stars' => 10
                        ],
                        [
                            'question_text' => 'Suggestions for improvement',
                            'question_type' => 'essay',
                            'required' => false
                        ]
                    ];
                } else {
                    // For all other categories, use likert questions
                    $likertRows = $this->getLikertRowsForCategory($key);
                    $page['questions'] = [
                        [
                            'question_text' => "Rate the following {$this->getCategoryNameFromKey($key)} aspects:",
                            'question_type' => 'likert',
                            'required' => true,
                            'likert_columns' => $likertColumns,
                            'likert_rows' => $likertRows
                        ]
                    ];
                }
                
                $pages[] = $page;
            }
            
            // Create the pages and questions
            foreach ($pages as $pageIndex => $pageData) {
                // Create the page
                $page = SurveyPage::create([
                    'survey_id' => $this->survey->id,
                    'page_number' => $pageIndex + 1,
                    'order' => $pageIndex + 1,
                    'title' => $pageData['title'],
                    'subtitle' => $pageData['subtitle'] ?? '',
                ]);
                
                $stats['pages']++;
                
                // Limit questions to maxQuestionsPerPage
                $questionCount = 0;
                foreach ($pageData['questions'] as $questionData) {
                    if ($questionCount >= $this->maxQuestionsPerPage) {
                        break;
                    }
                    
                    // Create the question
                    $question = SurveyQuestion::create([
                        'survey_id' => $this->survey->id,
                        'survey_page_id' => $page->id,
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'order' => $questionCount + 1,
                        'required' => $questionData['required'] ?? true,
                    ]);
                    
                    $stats['questions']++;
                    $questionCount++;
                    
                    // Handle specific question types
                    switch ($questionData['question_type']) {
                        case 'likert':
                            $columns = $questionData['likert_columns'] ?? $likertColumns;
                            $rows = $questionData['likert_rows'] ?? ['Statement 1', 'Statement 2', 'Statement 3'];
                            $question->likert_columns = json_encode($columns);
                            $question->likert_rows = json_encode($rows);
                            $question->save();
                            break;
                            
                        case 'rating':
                            $stars = $questionData['stars'] ?? 5;
                            $question->stars = $stars;
                            $question->save();
                            break;
                    }
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $stats;
    }
    
    /**
     * Get appropriate Likert columns based on the number of points
     * 
     * @param int $points Number of Likert points (3, 4, or 5)
     * @return array Array of column labels
     */
    private function getLikertColumns($points)
    {
        switch ($points) {
            case 3:
                return ['Disagree', 'Neutral', 'Agree'];
            case 4:
                return ['Strongly Disagree', 'Disagree', 'Agree', 'Strongly Agree'];
            case 5:
            default:
                return ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
        }
    }
    
    /**
     * Get appropriate Likert rows for a specific ISO category
     * 
     * @param string $category Category key
     * @return array Array of statements for the category
     */
    private function getLikertRowsForCategory($category)
    {
        switch ($category) {
            case 'functional':
                return [
                    'The software provides all necessary functions',
                    'Functions work correctly and accurately',
                    'Software meets specified requirements',
                    'All features perform as expected',
                    'The software is suitable for its intended purpose'
                ];
            case 'performance':
                return [
                    'Software responds quickly to user actions',
                    'System handles multiple tasks efficiently',
                    'Resource consumption is reasonable',
                    'Performance remains stable under load',
                    'Software scales well with increased usage'
                ];
            case 'compatibility':
                return [
                    'Software works well with other systems',
                    'Data can be exchanged with other applications',
                    'Software functions in different environments',
                    'Integration with existing tools is seamless',
                    'Software supports standard formats and protocols'
                ];
            case 'usability':
                return [
                    'Software is easy to learn and use',
                    'User interface is intuitive and clear',
                    'Help and documentation are accessible',
                    'Software prevents user errors effectively',
                    'Overall user experience is satisfying'
                ];
            case 'reliability':
                return [
                    'Software operates without failures',
                    'System recovers quickly from errors',
                    'Software maintains data integrity',
                    'System availability meets requirements',
                    'Software performs consistently over time'
                ];
            case 'security':
                return [
                    'Software protects against unauthorized access',
                    'Data is encrypted and secure',
                    'User authentication is robust',
                    'Software maintains audit trails',
                    'Privacy controls are adequate'
                ];
            case 'maintainability':
                return [
                    'Software is easy to modify and update',
                    'Issues can be diagnosed quickly',
                    'Code quality supports maintenance',
                    'Documentation aids in maintenance tasks',
                    'Testing of modifications is straightforward'
                ];
            case 'portability':
                return [
                    'Software can be easily installed',
                    'Software runs on different platforms',
                    'Software can be easily uninstalled',
                    'Configuration is flexible and adaptable',
                    'Migration to new environments is smooth'
                ];
            default:
                return ['Statement 1', 'Statement 2', 'Statement 3'];
        }
    }
    
    /**
     * Get the human-readable name for a category key
     * 
     * @param string $key The category key
     * @return string The human-readable category name
     */
    private function getCategoryNameFromKey($key)
    {
        $names = [
            'functional' => 'functional suitability',
            'performance' => 'performance efficiency',
            'compatibility' => 'compatibility',
            'usability' => 'usability',
            'reliability' => 'reliability',
            'security' => 'security',
            'maintainability' => 'maintainability',
            'portability' => 'portability',
        ];
        
        return $names[$key] ?? $key;
    }
    
    /**
     * Create prompt for AI to generate survey structure
     * 
     * @param string $type The type of survey (normal or ISO)
     * @return string The prompt for the AI
     */
    private function createSurveyPrompt($type)
    {
        // Process abstract to ensure it's not too long
        $processedAbstract = $this->getProcessedAbstract();
        
        $questionTypes = ['multiple_choice', 'radio', 'likert', 'essay', 'short_text', 'rating', 'date'];
        $questionTypesStr = implode(', ', $questionTypes);
        
        // Even shorter prompt to avoid thinking responses
        $prompt = "Survey: {$processedAbstract}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- {$this->maxPages} pages\n";
        $prompt .= "- {$this->maxQuestionsPerPage} questions per page\n";
        $prompt .= "- Types: {$questionTypesStr}\n";
        $prompt .= "- Multiple choice: 3-5 options\n";
        $prompt .= "- Likert: 3-5 columns/rows\n";
        $prompt .= "- Rating: 5/7/10 stars\n\n";
        
        if ($type === 'iso') {
            $prompt = "ISO25010 survey: {$processedAbstract}\n\n";
            $prompt .= "Requirements: {$this->maxPages} pages, {$this->maxQuestionsPerPage} questions each\n";
            $prompt .= "Categories: General, Functional, Performance, Usability, Reliability, Security\n";
            $prompt .= "Use likert for quality ratings\n\n";
        }
        
        $prompt .= "Return JSON only - no explanations:\n";
        $prompt .= '{"pages":[{"title":"","subtitle":"","questions":[{"question_text":"","question_type":"","required":true,"choices":[],"stars":5,"likert_columns":[],"likert_rows":[]}]}]}';
        
        return $prompt;
    }

    /**
     * Process abstract to ensure it's not too long for the AI models
     * 
     * @return string The processed abstract
     */
    private function getProcessedAbstract()
    {
        $abstract = trim($this->abstract);
        
        // Count words in the abstract
        $wordCount = str_word_count($abstract);
        
        // If abstract is already short enough, use as is
        if ($wordCount <= 150) {
            return $abstract;
        }
        
        // For longer abstracts, truncate to a reasonable length
        $words = str_word_count($abstract, 1);
        $truncatedWords = array_slice($words, 0, 150);
        $truncated = implode(' ', $truncatedWords);
        
        // Add ellipsis to indicate truncation
        Log::info('Abstract truncated for AI processing', [
            'original_word_count' => $wordCount,
            'truncated_word_count' => count($truncatedWords)
        ]);
        
        return $truncated . '...';
    }
    
    /**
     * Extract JSON from AI response text
     * 
     * @param string $response AI response text
     * @return array|null JSON data as array or null on failure
     */
    private function extractJsonFromResponse($response)
    {
        // Log the raw response for debugging
        Log::debug('AI response to extract JSON from', [
            'survey_id' => $this->survey->id,
            'response_excerpt' => substr($response, 0, 500) . (strlen($response) > 500 ? '...' : '')
        ]);
        
        // Remove any thinking tags that AI might include in response (more aggressive removal)
        $response = preg_replace('/<think>.*?<\/think>/is', '', $response);
        $response = preg_replace('/<\/think>/i', '', $response); // Remove orphaned closing tags
        $response = preg_replace('/<think>/i', '', $response); // Remove orphaned opening tags
        
        // Also remove any text before the first { and after the last }
        $firstBrace = strpos($response, '{');
        $lastBrace = strrpos($response, '}');
        
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $response = substr($response, $firstBrace, $lastBrace - $firstBrace + 1);
        }
        
        // Try to parse the response directly
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            if (isset($data['pages']) && is_array($data['pages'])) {
                return $data;
            }
        }
        
        // Try multiple extraction patterns
        $patterns = [
            // Extract JSON from within text
            '/\{(?:[^{}]|(?R))*\}/s',
            // Extract JSON from code blocks
            '/```(?:json)?\s*({.*?})\s*```/s',
            // Extract JSON with newlines
            '/\{\s*"[^"]+"\s*:/s'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $candidate = $matches[0];
                // If the match doesn't start with {, add it
                if (substr($candidate, 0, 1) !== '{') {
                    $candidate = '{' . $candidate;
                }
                // If the match doesn't end with }, add it
                if (substr($candidate, -1) !== '}') {
                    $candidate .= '}';
                }
                
                $data = json_decode($candidate, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    // Validate basic structure
                    if (isset($data['pages']) && is_array($data['pages'])) {
                        return $data;
                    }
                }
            }
        }
        
        // Try to find any array with pages inside the text - last resort
        if (preg_match('/"pages"\s*:\s*\[\s*\{/s', $response)) {
            // This looks like it might have a pages array - extract everything from this point
            $startPos = strpos($response, '"pages"');
            if ($startPos !== false) {
                $subset = '{' . substr($response, $startPos);
                // Find a reasonable ending
                $endPos = strpos($subset, '}]}');
                if ($endPos !== false) {
                    $subset = substr($subset, 0, $endPos + 3);
                    $data = json_decode($subset, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data) && isset($data['pages'])) {
                        return $data;
                    }
                }
            }
        }
        
        // If extraction failed, log details with more info
        Log::error('Failed to extract valid JSON from AI response', [
            'survey_id' => $this->survey->id,
            'response_length' => strlen($response),
            'json_error' => json_last_error_msg(),
            'cleaned_response_start' => substr($response, 0, 200)
        ]);
        
        // Return null to indicate failure
        return null;
    }
    
    /**
     * Process AI-generated survey structure and create pages and questions
     * 
     * @param array $structure The survey structure from AI
     * @return array Statistics about created items
     */
    private function processSurveyStructure($structure)
    {
        $stats = [
            'pages' => 0,
            'questions' => 0
        ];
        
        if (!isset($structure['pages']) || !is_array($structure['pages'])) {
            throw new \Exception('Invalid survey structure: missing pages array');
        }
        
        DB::beginTransaction();
        
        try {
            // Get the maximum page order to append new pages after existing ones
            $lastPageOrder = SurveyPage::where('survey_id', $this->survey->id)->max('order') ?? 0;
            
            // Process each page
            foreach ($structure['pages'] as $pageIndex => $pageData) {
                // Skip if we've reached the max pages limit
                if ($stats['pages'] >= $this->maxPages) {
                    break;
                }
                
                // Create the page
                $page = SurveyPage::create([
                    'survey_id' => $this->survey->id,
                    'page_number' => $lastPageOrder + $pageIndex + 1,
                    'order' => $lastPageOrder + $pageIndex + 1,
                    'title' => $pageData['title'] ?? 'Page ' . ($lastPageOrder + $pageIndex + 1),
                    'subtitle' => $pageData['subtitle'] ?? '',
                ]);
                
                $stats['pages']++;
                
                // Skip if no questions or not an array
                if (!isset($pageData['questions']) || !is_array($pageData['questions'])) {
                    continue;
                }
                
                // Process questions for this page
                $questionsAdded = $this->processPageQuestions($page, $pageData['questions']);
                $stats['questions'] += $questionsAdded;
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $stats;
    }
    
    /**
     * Process questions for a page
     * 
     * @param SurveyPage $page The page to add questions to
     * @param array $questions Question data from AI
     * @return int Number of questions added
     */
    private function processPageQuestions($page, $questions)
    {
        $questionsAdded = 0;
        $defaultQuestionTypes = ['multiple_choice', 'short_text', 'essay', 'rating'];
        
        // Process provided questions up to maxQuestionsPerPage
        foreach ($questions as $questionIndex => $questionData) {
            // Stop if we've reached the exact number
            if ($questionsAdded >= $this->maxQuestionsPerPage) {
                break;
            }
            
            // Skip if missing required fields and add a default question instead
            if (!isset($questionData['question_text']) || !isset($questionData['question_type'])) {
                $this->addDefaultQuestion($page, $questionsAdded, $defaultQuestionTypes);
                $questionsAdded++;
                continue;
            }
            
            // Check if question type is valid, use a default if not
            $questionType = $questionData['question_type'];
            if (!in_array($questionType, ['multiple_choice', 'radio', 'likert', 'essay', 'short_text', 'rating', 'date'])) {
                $this->addDefaultQuestion($page, $questionsAdded, $defaultQuestionTypes);
                $questionsAdded++;
                continue;
            }
            
            // Create the question
            $question = SurveyQuestion::create([
                'survey_id' => $this->survey->id,
                'survey_page_id' => $page->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionType,
                'order' => $questionsAdded + 1,
                'required' => $questionData['required'] ?? true,
            ]);
            
            // Handle specific question types
            switch ($questionType) {
                case 'multiple_choice':
                case 'radio':
                    $this->processChoiceOptions($question, $questionData);
                    break;
                    
                case 'likert':
                    $this->processLikertQuestion($question, $questionData);
                    break;
                    
                case 'rating':
                    $this->processRatingQuestion($question, $questionData);
                    break;
            }
            
            $questionsAdded++;
        }
        
        // Add additional default questions if needed to reach exactly maxQuestionsPerPage
        while ($questionsAdded < $this->maxQuestionsPerPage) {
            $this->addDefaultQuestion($page, $questionsAdded, $defaultQuestionTypes);
            $questionsAdded++;
        }
        
        return $questionsAdded;
    }
    
    /**
     * Add a default question to a page
     *
     * @param SurveyPage $page The page to add a question to
     * @param int $order The order of the question
     * @param array $questionTypes Array of available question types
     */
    private function addDefaultQuestion($page, $order, $questionTypes)
    {
        // Rotate through question types for variety
        $type = $questionTypes[$order % count($questionTypes)];
        
        // Create the question
        $question = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question " . ($order + 1),
            'question_type' => $type,
            'order' => $order + 1,
            'required' => true,
        ]);
        
        // Set up the question based on type
        switch ($type) {
            case 'multiple_choice':
                SurveyChoice::create([
                    'survey_question_id' => $question->id,
                    'choice_text' => 'Option 1',
                    'order' => 1,
                ]);
                SurveyChoice::create([
                    'survey_question_id' => $question->id,
                    'choice_text' => 'Option 2',
                    'order' => 2,
                ]);
                SurveyChoice::create([
                    'survey_question_id' => $question->id,
                    'choice_text' => 'Option 3',
                    'order' => 3,
                ]);
                break;
                
            case 'rating':
                $question->stars = 5;
                $question->save();
                break;
                
            case 'likert':
                $columns = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
                $rows = ['Statement 1', 'Statement 2', 'Statement 3'];
                $question->likert_columns = json_encode($columns);
                $question->likert_rows = json_encode($rows);
                $question->save();
                break;
        }
    }
    
    /**
     * Process choice options for multiple_choice and radio questions
     * 
     * @param SurveyQuestion $question The question to add choices to
     * @param array $questionData Question data from AI
     */
    private function processChoiceOptions($question, $questionData)
    {
        // Skip if no choices or not an array
        if (!isset($questionData['choices']) || !is_array($questionData['choices'])) {
            // Add default choices
            for ($i = 1; $i <= 3; $i++) {
                SurveyChoice::create([
                    'survey_question_id' => $question->id,
                    'choice_text' => 'Option ' . $i,
                    'order' => $i,
                    'is_other' => false,
                ]);
            }
            return;
        }
        
        // Add each choice
        foreach ($questionData['choices'] as $choiceIndex => $choiceText) {
            SurveyChoice::create([
                'survey_question_id' => $question->id,
                'choice_text' => $choiceText,
                'order' => $choiceIndex + 1,
                'is_other' => false,
            ]);
        }
        
        // Add "Other" option with 20% probability for multiple_choice
        if ($question->question_type === 'multiple_choice' && mt_rand(1, 5) === 1) {
            SurveyChoice::create([
                'survey_question_id' => $question->id,
                'choice_text' => 'Other',
                'order' => count($questionData['choices']) + 1,
                'is_other' => true,
            ]);
        }
    }

    /**
     * Process likert question
     * 
     * @param SurveyQuestion $question The question to configure
     * @param array $questionData Question data from AI
     */
    private function processLikertQuestion($question, $questionData)
    {
        // Default likert columns if not provided or invalid
        $columns = isset($questionData['likert_columns']) && is_array($questionData['likert_columns']) 
            ? $questionData['likert_columns'] 
            : ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
        
        // Default likert rows if not provided or invalid
        $rows = isset($questionData['likert_rows']) && is_array($questionData['likert_rows'])
            ? $questionData['likert_rows']
            : ['Statement 1', 'Statement 2', 'Statement 3'];
        
        // Save to question
        $question->likert_columns = json_encode($columns);
        $question->likert_rows = json_encode($rows);
        $question->save();
    }

    /**
     * Process rating question
     * 
     * @param SurveyQuestion $question The question to configure
     * @param array $questionData Question data from AI
     */
    private function processRatingQuestion($question, $questionData)
    {
        // Default to 5 stars if not provided or invalid
        $stars = isset($questionData['stars']) && in_array($questionData['stars'], [5, 7, 10]) 
            ? $questionData['stars'] 
            : 5;
        
        // Save to question
        $question->stars = $stars;
        $question->save();
    }
}
