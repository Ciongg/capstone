<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use Livewire\Component;
use App\Livewire\Surveys\FormBuilder\FormBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Support\Facades\DB;

class SurveyGeneratorModal extends Component
{
    public $survey;
    public $abstract = '';
    public $maxPages = 3;
    public $maxQuestionsPerPage = 5;
    public $generationType = 'normal';
    public $isGenerating = false;
    
    // New properties for ISO category selection
    public $isoCategories = [
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
    
    public $selectedIsoCategories = [
        'general' => true,
        'functional' => true,
        'performance' => true,
        'usability' => true,
        'compatibility' => true
    ];
    
    // Property for Likert scale points
    public $likertPoints = 5;
    
    public function mount($survey)
    {
        $this->survey = $survey;
    }
    
    // Add an updated method to handle generationType changes
    public function updatedGenerationType()
    {
        // Reset ISO category selection when switching between types
        if ($this->generationType === 'iso') {
            $this->selectedIsoCategories = [
                'general' => true,
                'functional' => true,
                'performance' => true,
                'usability' => true,
                'compatibility' => true
            ];
        }
    }
    
    public function generateSurvey()
    {

        set_time_limit(60);

        // Validate differently based on survey type
        if ($this->generationType === 'normal') {
            $this->validate([
                'abstract' => [
                    'required', 
                    'string',
                    'min:10',
                    function ($attribute, $value, $fail) {
                        $wordCount = str_word_count($value);
                        if ($wordCount > 200) {
                            $fail('The abstract may not be greater than 200 words.');
                        }
                    }
                ],
                'maxPages' => 'required|integer|min:1|max:10',
                'maxQuestionsPerPage' => 'required|integer|min:1|max:20',
                'generationType' => 'required|in:normal,iso',
            ]);
        } else {
            // Validate ISO-specific fields
            $this->validate([
                'abstract' => [
                    'required', 
                    'string',
                    'min:10',
                    function ($attribute, $value, $fail) {
                        $wordCount = str_word_count($value);
                        if ($wordCount > 200) {
                            $fail('The abstract may not be greater than 200 words.');
                        }
                    }
                ],
                'maxQuestionsPerPage' => 'required|integer|min:1|max:20',
                'generationType' => 'required|in:normal,iso',
                'likertPoints' => 'required|in:3,4,5',
            ]);
            
            // Ensure at least one ISO category is selected
            if (!collect($this->selectedIsoCategories)->contains(true)) {
                $this->addError('selectedIsoCategories', 'Please select at least one ISO category.');
                return;
            }
        }
        
        $this->isGenerating = true;
        
        try {
            // Log the request
            Log::info('AI Survey Generation requested', [
                'survey_id' => $this->survey->id,
                'abstract' => $this->abstract,
                'maxPages' => $this->generationType === 'normal' ? $this->maxPages : count(array_filter($this->selectedIsoCategories)),
                'maxQuestionsPerPage' => $this->maxQuestionsPerPage,
                'generationType' => $this->generationType,
                'likertPoints' => $this->likertPoints,
                'selectedIsoCategories' => $this->generationType === 'iso' ? $this->selectedIsoCategories : null,
            ]);
            
            // For ISO surveys, use the custom template
            if ($this->generationType === 'iso') {
                $this->deleteExistingSurveyContent();
                
                // Use the ISO template with selected categories and Likert points
                $createdItems = $this->createISOTemplate();
                
                // Show success message
                $this->dispatch('showSuccessAlert', [
                    'message' => "ISO25010 survey structure created with {$createdItems['pages']} pages and {$createdItems['questions']} questions."
                ]);
                
                // Refresh the parent component to show the new survey structure
                $this->dispatch('surveyStructureUpdated')->to('surveys.form-builder.form-builder');
                
                // Close modal
                $this->dispatch('close-modal', ['name' => 'survey-generator-modal-' . $this->survey->id]);
                
                return;
            }
            
            // For normal surveys, proceed with AI generation as before
            $result = $this->callAIService($this->generationType);
            
            if ($result) {
                // Delete all existing content before processing the AI result
                $this->deleteExistingSurveyContent();
                
                // Process the generated content and create the survey structure
                $createdItems = $this->processSurveyStructure($result);
                
                // Show success message
                $this->dispatch('showSuccessAlert', [
                    'message' => "Survey generation complete! Created {$createdItems['pages']} pages with {$createdItems['questions']} questions."
                ]);
                
                // Refresh the parent component to show the new survey structure
                $this->dispatch('surveyStructureUpdated')->to('surveys.form-builder.form-builder');
                
                // Close modal
                $this->dispatch('close-modal', ['name' => 'survey-generator-modal-' . $this->survey->id]);
            } else {
                throw new \Exception('Failed to generate survey structure. Please try again with a different description.');
            }
            
        } catch (\Exception $e) {
            Log::error('Survey generation error: ' . $e->getMessage(), [
                'survey_id' => $this->survey->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('showErrorAlert', [
                'message' => 'Failed to generate survey: ' . $e->getMessage()
            ]);
        } finally {
            $this->isGenerating = false;
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
                'survey_id' => $this->survey->id
            ]);
        });
    }
    
    /**
     * Call the AI service to generate survey structure
     * 
     * @param string $type The type of survey (normal or ISO)
     * @return array|null JSON structure for the survey or null on failure
     */
    private function callAIService($type)
    {
        // Try DeepSeek first, then fall back to Gemini if it fails
        $result = $this->generateWithDeepSeek($type);
        
        if (!$result) {
            Log::info('DeepSeek failed, trying Gemini...');
            $result = $this->generateWithGemini($type);
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
            ])->timeout(30)->post($apiUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert survey designer.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 8912,
                'temperature' => 0.7
            ]);
            
            if ($response->successful() && isset($response['choices'][0]['message']['content'])) {
                return $this->extractJsonFromResponse($response['choices'][0]['message']['content']);
            } else {
                Log::error('DeepSeek API error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('DeepSeek API call failed: ' . $e->getMessage());
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
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 8912,
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
            Log::error('Gemini API call failed: ' . $e->getMessage());
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
            
            foreach ($this->isoCategories as $key => $category) {
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
        // Preprocess the abstract to extract key points
        $processedAbstract = $this->preprocessAbstract($this->abstract);
        
        $questionTypes = ['multiple_choice', 'radio', 'likert', 'essay', 'short_text', 'rating', 'date'];
        $questionTypesStr = implode(', ', $questionTypes);
        
        $prompt = "Create a structured survey based on the following description:\n\n";
        $prompt .= "DESCRIPTION: {$processedAbstract}\n\n";
        $prompt .= "MAX PAGES: {$this->maxPages}\n";
        $prompt .= "MAX QUESTIONS PER PAGE: {$this->maxQuestionsPerPage}\n";
        $prompt .= "AVAILABLE QUESTION TYPES: {$questionTypesStr}\n\n";
        
        $prompt .= "INSTRUCTIONS:\n";
        $prompt .= "1. Create a survey structure with pages and questions matching the description.\n";
        $prompt .= "2. Use a variety of question types appropriate for the content.\n";
        $prompt .= "3. For multiple_choice and radio questions, provide 3-7 options.\n";
        $prompt .= "4. For likert questions, provide 3-5 columns and 3-5 statements.\n";
        $prompt .= "5. For rating questions, specify stars (5, 7, or 10).\n";
        $prompt .= "6. Ensure questions are clear, unbiased, and appropriate for the survey topic.\n";
        
        // Different structure guidance based on type
        if ($type === 'iso') {
            $prompt = "Create an ISO25010 software quality assessment survey based on the following description:\n\n";
            $prompt .= "DESCRIPTION: {$this->abstract}\n\n";
            $prompt .= "MAX PAGES: {$this->maxPages}\n";
            $prompt .= "MAX QUESTIONS PER PAGE: {$this->maxQuestionsPerPage}\n\n";
            
            $prompt .= "INSTRUCTIONS:\n";
            $prompt .= "1. Follow the ISO25010 software quality model structure exactly.\n";
            $prompt .= "2. Use primarily likert questions for quality attribute assessments.\n";
            $prompt .= "3. Create pages with these exact titles in this order (up to the max pages limit):\n";
            $prompt .= "   - General Information\n";
            $prompt .= "   - Functional Suitability\n";
            $prompt .= "   - Performance Efficiency\n";
            $prompt .= "   - Compatibility\n";
            $prompt .= "   - Usability\n";
            $prompt .= "   - Reliability\n";
            $prompt .= "   - Security\n";
            $prompt .= "   - Maintainability\n";
            $prompt .= "   - Portability\n";
            $prompt .= "   - Overall Rating and Comments\n";
            $prompt .= "4. For likert questions, use columns: ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']\n";
            $prompt .= "5. For each quality attribute, assess 3-5 specific criteria using likert rows.\n";
            $prompt .= "6. The General Information page should collect basic information using short_text fields.\n";
            $prompt .= "7. The Overall Rating page should include a rating (1-10) and an essay question for comments.\n";
        } else {
            $prompt .= "7. Create a logical flow from general to specific questions.\n";
            $prompt .= "8. Include a mix of quantitative and qualitative questions.\n";
        }
        
        $prompt .= "\nRESPONSE FORMAT:\n";
        $prompt .= "Return ONLY a valid JSON object with the survey structure in this format:\n";
        $prompt .= "```json\n";
        $prompt .= "{\n";
        $prompt .= '  "pages": [\n';
        $prompt .= "    {\n";
        $prompt .= '      "title": "Page Title",\n';
        $prompt .= '      "subtitle": "Optional page subtitle",\n';
        $prompt .= '      "questions": [\n';
        $prompt .= "        {\n";
        $prompt .= '          "question_text": "Question text here?",\n';
        $prompt .= '          "question_type": "multiple_choice|radio|likert|essay|short_text|rating|date",\n';
        $prompt .= '          "required": true,\n';
        $prompt .= '          "choices": ["Option 1", "Option 2", "Option 3"],  // Only for multiple_choice and radio\n';
        $prompt .= '          "stars": 5,  // Only for rating (5, 7, or 10)\n';
        $prompt .= '          "likert_columns": ["Strongly Disagree", "Disagree", "Neutral", "Agree", "Strongly Agree"],  // Only for likert\n';
        $prompt .= '          "likert_rows": ["Statement 1", "Statement 2", "Statement 3"]  // Only for likert\n';
        $prompt .= "        }\n";
        $prompt .= "      ]\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n";
        $prompt .= "```\n\n";
        
        $prompt .= "IMPORTANT: Your response MUST contain valid JSON only. Do not add ANY explanations, markdown formatting, or text before or after the JSON.\n";
        
        return $prompt;
    }
    
    /**
     * Preprocess abstract to make it more concise for AI processing
     */
    private function preprocessAbstract($abstract) 
    {
        // If abstract is under 100 words, use it as is
        $wordCount = str_word_count($abstract);
        if ($wordCount <= 100) {
            return $abstract;
        }
        
        // For longer abstracts, extract key sentences or summarize
        $sentences = preg_split('/(?<=[.!?])\s+/', $abstract, -1, PREG_SPLIT_NO_EMPTY);
        
        // Take first sentence, a middle sentence, and last sentence
        $summary = [];
        if (count($sentences) > 0) {
            $summary[] = $sentences[0]; // First sentence
        }
        if (count($sentences) > 2) {
            $summary[] = $sentences[intval(count($sentences)/2)]; // Middle sentence
        }
        if (count($sentences) > 1) {
            $summary[] = $sentences[count($sentences)-1]; // Last sentence
        }
        
        return implode(' ', $summary);
    }
    
    /**
     * Extract JSON from AI response text
     * 
     * @param string $response AI response text
     * @return array|null JSON data as array or null on failure
     */
    private function extractJsonFromResponse($response)
    {
        // Try to parse the response directly
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
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
                    return $data;
                }
            }
        }
        
        // Remove any markdown formatting and try again
        $cleaned = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $response);
        $data = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
        
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
        
        foreach ($questions as $questionIndex => $questionData) {
            // Skip if we've reached the max questions per page
            if ($questionsAdded >= $this->maxQuestionsPerPage) {
                break;
            }
            
            // Skip if missing required fields
            if (!isset($questionData['question_text']) || !isset($questionData['question_type'])) {
                continue;
            }
            
            // Check if question type is valid
            $questionType = $questionData['question_type'];
            if (!in_array($questionType, ['multiple_choice', 'radio', 'likert', 'essay', 'short_text', 'rating', 'date'])) {
                continue;
            }
            
            // Create the question
            $question = SurveyQuestion::create([
                'survey_id' => $this->survey->id,
                'survey_page_id' => $page->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionType,
                'order' => $questionIndex + 1,
                'required' => $questionData['required'] ?? true,
            ]);
            
            $questionsAdded++;
            
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
        }
        
        return $questionsAdded;
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
    
    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-generator-modal');
    }
}
