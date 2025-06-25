<?php

namespace App\Livewire\Surveys\AnswerSurvey;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SurveyQuestion;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AnswerSurvey extends Component
{
    /**
     * The survey being answered
     * @var Survey
     */
    public Survey $survey;
    
    /**
     * Stores all user answers indexed by question ID
     * @var array
     */
    public $answers = [];
    
    /**
     * Stores text inputs for "Other" options in multiple choice/radio questions
     * @var array
     */
    public $otherTexts = [];
    
    /**
     * Current page index in multi-page surveys (zero-based)
     * @var int
     */
    public $currentPage = 0;
    
    /**
     * Navigation action: 'next' moves to the next page, 'submit' finalizes the survey
     * @var string
     */
    public $navAction = 'submit';
    
    /**
     * Whether this is a preview (no data saved)
     * @var bool
     */
    public $isPreview = false;

    /**
     * Store translated question text
     * @var array
     */
    public $translatedQuestions = [];
    
    /**
     * Store translated choice text
     * @var array
     */
    public $translatedChoices = [];
    
    /**
     * Track questions that are currently being translated
     * @var array
     */
    public $translatingQuestions = [];

    /**
     * Global loading state for translation
     * @var bool
     */
    public $isLoading = false;

    /**
     * Listener for component events
     */
    protected $listeners = ['translateQuestion' => 'translateQuestion', '$refresh'];

    /**
     * Initialize the component with a survey
     * 
     * @param Survey $survey The survey to answer
     * @param bool $isPreview Whether this is just a preview
     * @return void
     */
    public function mount(Survey $survey, $isPreview = false)
    {
        // Load survey with its pages, questions, and choices, all properly ordered
        $this->survey = $survey->load([
            'pages' => function ($query) {
                $query->orderBy('order')
                      ->with(['questions' => function ($qQuery) {
                          $qQuery->orderBy('order')
                                 ->with(['choices' => function ($cQuery) {
                                     $cQuery->orderBy('order');
                                 }]);
                      }]);
            }
        ]);
        
        $this->isPreview = (bool) $isPreview;

        // Check if survey is available to answer
        if (!$this->isPreview && !in_array($this->survey->status, ['published', 'ongoing'])) {
            abort(404, 'Survey not available.');
        }

        // Start at the first page
        $this->currentPage = 0;

        // Set up initial answer structure
        $this->initializeAnswers();
    }

    /**
     * Set up the initial answer structure based on question types
     */
    protected function initializeAnswers()
    {
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions as $question) {
                switch ($question->question_type) {
                    case 'multiple_choice':
                        // For multiple choice: initialize an array with false for each choice
                        $this->answers[$question->id] = [];
                        $this->otherTexts[$question->id] = null;
                        
                        foreach ($question->choices as $choice) {
                            $this->answers[$question->id][$choice->id] = false;
                        }
                        break;
                        
                    case 'radio':
                        // For radio buttons: initialize as null (no selection)
                        $this->answers[$question->id] = null;
                        $this->otherTexts[$question->id] = null;
                        break;
                        
                    case 'likert':
                        // For likert scales: initialize array with null for each row
                        $likertRows = $this->getLikertRows($question);
                        $this->answers[$question->id] = array_fill(0, count($likertRows), null);
                        break;
                        
                    default:
                        // For text, essay, date, etc.: initialize as null
                        $this->answers[$question->id] = null;
                }
            }
        }
    }
    
    /**
     * Helper function to safely get likert rows from a question
     * 
     * @param SurveyQuestion $question
     * @return array
     */
    protected function getLikertRows($question)
    {
        if (is_array($question->likert_rows)) {
            return $question->likert_rows;
        }
        
        return json_decode($question->likert_rows, true) ?: [];
    }

    /**
     * Gets validation rules for questions on the current page only.
     * Used when navigating between pages to validate only the current page's content.
     * 
     * @return array Validation rules array formatted for Laravel validator
     */
    protected function getValidationRules() //used on submit
    {
        $rules = [];
        
        // Safety check: make sure the current page index exists
        if (!$this->survey->pages->has($this->currentPage)) {
            return $rules;
        }
        
        // Get questions for current page only
        $currentPageQuestions = $this->survey->pages[$this->currentPage]->questions;

        // Build validation rules for each question on this page
        foreach ($currentPageQuestions as $question) {
            $rules = array_merge($rules, $this->getRulesForQuestion($question)); //appends to the existing rules variable the new set of rules for each question.
        }
        
        return $rules;
    }

    /**
     * Gets validation messages for questions on the current page.
     * These messages are displayed when validation fails.
     * 
     * Calculates proper question numbers across pages, so error messages
     * show consistent question numbering matching what the user sees on screen.
     * 
     * @return array Validation messages keyed by field and rule
     */
    protected function getValidationMessages() //used on submit
    {
         // Safety check: make sure the current page index exists
        if (!$this->survey->pages->has($this->currentPage)) {
            return [];
        }
        
        // Calculate global question numbering across all pages
        // This ensures error messages reference the correct question number
        $questionNumber = 1;
        for ($i = 0; $i < $this->currentPage; $i++) {
            if ($this->survey->pages->has($i)) {
                $questionNumber += $this->survey->pages[$i]->questions->count();
            }
        }
        
        $messages = [];
        $currentPageQuestions = $this->survey->pages[$this->currentPage]->questions;
        
        // Generate messages for each question on this page
        foreach ($currentPageQuestions as $question) {
            // Use the current question number BEFORE incrementing it
            $messages = array_merge(
                $messages, 
                $this->getMessagesForQuestion($question, $questionNumber)
            );
            // Then increment the question number for the next iteration
            $questionNumber++;
        }
        
        return $messages;
    }

    /**
     * Get validation rules for all questions in the survey
     * 
     * @return array
     */
    protected function getAllValidationRules()
    {
        $rules = [];
        
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions as $question) {
                $rules = array_merge($rules, $this->getRulesForQuestion($question));
            }
        }
        
        return $rules;
    }

    /**
     * Gets validation messages for ALL questions in the entire survey.
     * Used for final submission when showing validation errors.
     * 
     * Maintains consistent global question numbering for error messages.
     * 
     * @return array Validation messages for all questions
     */
    protected function getAllValidationMessages()
    {
        $messages = [];
        $questionNumber = 1;
        
        // Generate messages for all questions with consistent numbering
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions as $question) {
                // Use the current question number BEFORE incrementing it
                $messages = array_merge(
                    $messages, 
                    $this->getMessagesForQuestion($question, $questionNumber)
                );
                // Then increment the question number for the next iteration
                $questionNumber++;
            }
        }
        
        return $messages;
    }

    /**
     * Generate validation rules for a specific question
     * 
     * @param SurveyQuestion $question
     * @return array
     */
    protected function getRulesForQuestion(SurveyQuestion $question)
    {
        $rules = [];
        $questionId = $question->id;
        $isRequired = $question->required;

        // Different rules based on question type
        switch ($question->question_type) {
            case 'multiple_choice':
                $rules['answers.' . $questionId] = [
                    'array',
                    function ($attribute, $value, $fail) use ($question, $isRequired) {
                        $selectedCount = collect($value ?? [])->filter(fn($v) => $v === true)->count();

                        // Required validation - at least one option must be selected
                        if ($isRequired && $selectedCount < 1) {
                            $fail("Please select at least one option.");
                            return;
                        }

                        // Check answer limits if configured
                        $limitCondition = $question->limit_condition;
                        $maxAnswers = $question->max_answers;

                        if ($limitCondition && $maxAnswers > 0) {
                            if ($limitCondition === 'equal_to' && $selectedCount != $maxAnswers) {
                                if ($isRequired || $selectedCount > 0) {
                                    $fail("Please select exactly {$maxAnswers} options.");
                                }
                            } elseif ($limitCondition === 'at_most' && $selectedCount > $maxAnswers) {
                                $fail("Please select no more than {$maxAnswers} options.");
                            }
                        }
                    }
                ];
                
                // Validate "Other" text if an "Other" choice exists and is selected
                $otherChoice = $question->choices->firstWhere('is_other', true);
                if ($otherChoice) {
                    $rules['otherTexts.' . $questionId] = [
                        Rule::requiredIf(function () use ($questionId, $otherChoice) {
                            return isset($this->answers[$questionId][$otherChoice->id]) && 
                                   $this->answers[$questionId][$otherChoice->id] === true;
                        }),
                        'nullable', 'string', 'max:255'
                    ];
                }
                
                // Each choice must be a boolean
                $rules['answers.' . $questionId . '.*'] = ['boolean'];
                break;
                
            case 'radio':
                // Radio buttons - required if question is required
                $rules['answers.' . $questionId] = $isRequired ? ['required'] : ['nullable'];
                
                // Validate "Other" text if an "Other" choice exists and is selected
                $otherChoice = $question->choices->firstWhere('is_other', true);
                if ($otherChoice) {
                    $rules['otherTexts.' . $questionId] = [
                        Rule::requiredIf(fn() => ($this->answers[$questionId] ?? null) == $otherChoice->id),
                        'nullable', 'string', 'max:255'
                    ];
                }
                break;
                
            case 'likert':
                // Likert scale - array of answers, each row required if question is required
                $rules['answers.' . $questionId] = $isRequired ? ['required', 'array'] : ['nullable', 'array'];
                
                $likertRows = $this->getLikertRows($question);
                if ($isRequired) {
                    foreach (array_keys($likertRows) as $rowIndex) {
                        $rules['answers.' . $questionId . '.' . $rowIndex] = ['required'];
                    }
                } else {
                    foreach (array_keys($likertRows) as $rowIndex) {
                        $rules['answers.' . $questionId . '.' . $rowIndex] = ['nullable'];
                    }
                }
                break;
                
            default:
                // Text, essay, date, etc. - required if question is required
                $rules['answers.' . $questionId] = $isRequired ? ['required'] : ['nullable'];
        }
        
        return $rules;
    }

    /**
     * Generate validation messages for a specific question
     * 
     * @param SurveyQuestion $question
     * @param int $qNum The question number (for display)
     * @return array
     */
    protected function getMessagesForQuestion(SurveyQuestion $question, int $qNum)
    {
        $messages = [];
        $questionId = $question->id;

        // Standard error message for required questions
        if (!in_array($question->question_type, ['multiple_choice', 'likert'])) {
            $messages['answers.' . $questionId . '.required'] = "Question {$qNum} is required.";
        }

        // Custom messages for likert scales
        if ($question->question_type === 'likert') {
            $messages['answers.' . $questionId . '.required'] = "Please answer all parts of question {$qNum}.";

            $likertRows = $this->getLikertRows($question);
            foreach (array_keys($likertRows) as $rowIndex) {
                $rowText = $likertRows[$rowIndex] ?? 'Row ' . ($rowIndex + 1);
                $messages['answers.' . $questionId . '.' . $rowIndex . '.required'] = 
                    "Please select an option for '{$rowText}'";
            }
        }

        // Message for "Other" text field
        $messages['otherTexts.' . $questionId . '.required'] = "Please specify your 'Other' answer for question {$qNum}.";

        return $messages;
    }

    /**
     * Process form submission - handles both page navigation and final submission
     */
    public function submit()
    {
        // Handle navigation to next page
        if ($this->navAction === 'next') {
            // Check if current page has questions before validating
            if ($this->survey->pages->has($this->currentPage) && 
                $this->survey->pages[$this->currentPage]->questions->count() > 0) {
                // Validate only the current page
                $this->validate($this->getValidationRules(), $this->getValidationMessages());
            }

            // If there are more pages, advance to the next one
            if ($this->currentPage < $this->survey->pages->count() - 1) {
                $this->currentPage++;
                $this->dispatch('pageChanged');
            } else {
                // If we're on the last page, change to submit mode
                $this->navAction = 'submit';
                $this->submit();
            }
            return;
        }
        
        // Handle final submission
        if ($this->navAction === 'submit') {
            // Validate all questions in the survey
            $this->validate($this->getAllValidationRules(), $this->getAllValidationMessages());

            // In preview mode, don't save anything, just show success message
            if ($this->isPreview) {
                session()->flash('success', 'Preview submitted successfully! (No data saved)');
                
                // If user is a super admin, redirect to surveys index
                if (Auth::check() && Auth::user()->isSuperAdmin()) {
                    return redirect()->route('surveys.index');
                }
                
                // Otherwise redirect to form builder
                return redirect()->route('surveys.create', ['survey' => $this->survey->id]);
            }

            // In normal mode, save the responses
            $this->saveResponses();
            
            // Show SweetAlert2 success message with redirect
            // The actual alert is triggered via a dispatched browser event
            $this->dispatch('surveySubmitted', [
                'title' => 'Survey Completed!',
                'message' => 'Thank you for completing the survey.',
                'points' => $this->survey->points_allocated,
                'surveyName' => $this->survey->title
            ]);
        }
    }
    
    /**
     * Save survey responses to the database
     */
    protected function saveResponses()
    {
        DB::transaction(function () {
            $user = Auth::user();
            
            // Update survey status if it's the first response
            if ($this->survey->status === 'published') {
                $this->survey->status = 'ongoing';
                $this->survey->save();
            }

            // Create a new response record
            $response = Response::create([
                'survey_id' => $this->survey->id,
                'user_id' => $user?->id,
            ]);

            // Save user snapshot data if user is authenticated
            if ($user) {
                // Create demographic tags JSON
                $demographicTags = [];
                if ($user->tags) {
                    foreach ($user->tags as $tag) {
                        $demographicTags[] = [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'category_id' => $tag->tag_category_id
                        ];
                    }
                }
                
                // Calculate completion time
                $completionTime = null;
                // You may add logic here to calculate actual completion time

                // Create the snapshot record
                $response->snapshot()->create([
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'trust_score' => $user->trust_score ?? 100,
                    'points' => $user->points ?? 0,
                    'account_level' => $user->account_level ?? 0,
                    'experience_points' => $user->experience_points ?? 0,
                    'rank' => $user->rank ?? 'silver',
                    'title' => $user->title ?? null,
                    'started_at' => now()->subMinutes(5), // Example - you might want to track actual start time
                    'completed_at' => now(),
                    'completion_time_seconds' => $completionTime ?? rand(60, 300), // Example - replace with actual tracking
                    'demographic_tags' => json_encode($demographicTags)
                ]);
            }

            // Process answers for each question
            foreach ($this->answers as $questionId => $answerValue) {
                $question = SurveyQuestion::find($questionId);
                if (!$question) continue;

                $this->saveAnswerForQuestion($response, $question, $answerValue);
            }

            // Award points to user if configured
            if ($user && $this->survey->points_allocated > 0) {
                $user->points = ($user->points ?? 0) + $this->survey->points_allocated;
                if ($user instanceof User) {
                    try {
                        $user->save();
                    } catch (\Exception $e) {
                        Log::error("Error saving user points for user ID: {$user->id}. Error: " . $e->getMessage());
                    }
                }
            }
        });
    }
    
    /**
     * Save an answer for a specific question
     * 
     * @param Response $response The survey response record
     * @param SurveyQuestion $question The question being answered
     * @param mixed $answerValue The answer value
     */
    protected function saveAnswerForQuestion($response, $question, $answerValue)
    {
        $answerData = [
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => null,
            'other_text' => null,
        ];

        switch ($question->question_type) {
            case 'multiple_choice':
                // For multiple choice, save selected choice IDs as JSON by encoding
                $selectedChoiceIds = collect($answerValue)
                    ->filter(fn($v) => $v === true)
                    ->keys()
                    ->toArray();
                    
                if (!empty($selectedChoiceIds)) {
                    $answerData['answer'] = json_encode($selectedChoiceIds);
                    
                    // Save "Other" text if applicable
                    $otherChoice = $question->choices->firstWhere('is_other', true);
                    if ($otherChoice && in_array($otherChoice->id, $selectedChoiceIds)) {
                        $answerData['other_text'] = $this->otherTexts[$question->id] ?? null;
                    }
                    
                    Answer::create($answerData);
                }
                break;
                
            case 'radio':
                // For radio buttons, save the selected choice ID
                if ($answerValue !== null) {
                    $answerData['answer'] = $answerValue;
                    
                    // Save "Other" text if applicable
                    $otherChoice = $question->choices->firstWhere('is_other', true);
                    if ($otherChoice && $answerValue == $otherChoice->id) {
                        $answerData['other_text'] = $this->otherTexts[$question->id] ?? null;
                    }
                    
                    Answer::create($answerData);
                }
                break;
                
            case 'likert':
                // For likert scales, save responses as JSON
                $filteredLikert = array_filter($answerValue ?? [], fn($v) => $v !== null);
                if (!empty($filteredLikert)) {
                    $answerData['answer'] = json_encode($answerValue);
                    Answer::create($answerData);
                }
                break;
                
            default:
                // For text, essay, date inputs, etc.
                if ($answerValue !== null && $answerValue !== '') {
                    $answerData['answer'] = $answerValue;
                    Answer::create($answerData);
                } elseif (!$question->required) {
                    // For non-required questions with empty answers, save a placeholder
                    $answerData['answer'] = '-';
                    Answer::create($answerData);
                }
        }
    }
    

  
  /**
     * Translate question using DeepSeek API
     * 
     * @param string $textToTranslate
     * @param string $targetLanguage
     * @return string|null
     */
    protected function translateWithDeepSeek($textToTranslate, $targetLanguage)
    {
        $deepseekPrompt = "You are a professional translator. Translate ONLY from English to {$targetLanguage}.\n\n"
            . "CRITICAL RULES:\n"
            . "1. Use ONLY {$targetLanguage} characters and words\n"
            . "2. NO mixing with other languages (Russian, English, etc.)\n"
            . "3. NO transliteration - use native {$targetLanguage} script only\n"
            . "4. Keep prefixes (Q:, C0:, etc.) exactly as shown\n"
            . "5. One translation per line\n"
            . "6. If uncertain, use simple {$targetLanguage} equivalent\n\n"
            . "REJECT: Mixed language text like 'тьюторингサービス'\n"
            . "ACCEPT: Pure {$targetLanguage} only\n\n"
            . "TEXT TO TRANSLATE:\n{$textToTranslate}\n\n"
            . "Provide PURE {$targetLanguage} translation (no mixed languages):";

        $endpoint = rtrim(env('AZURE_DEEPSEEK_ENDPOINT'), '/');
        $apiKey = env('AZURE_DEEPSEEK_KEY');
        $modelName = "DeepSeek-R1-0528";
        $apiVersion = "2024-05-01-preview";

        $apiUrl = "{$endpoint}/openai/deployments/{$modelName}/chat/completions?api-version={$apiVersion}";

        Log::info("Calling DeepSeek at: {$apiUrl}");

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'api-key' => $apiKey,
        ])->timeout(30)->post($apiUrl, [
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional translator.'],
                ['role' => 'user', 'content' => $deepseekPrompt]
            ],
            'max_tokens' => 2048,
            'temperature' => 0.1
        ]);

        if ($response->successful() && isset($response['choices'][0]['message']['content'])) {
            Log::info('DeepSeek translation successful.');
            return $response['choices'][0]['message']['content'];
        } else {
            Log::error('DeepSeek translation failed: ' . $response->body());
            return null;
        }
    }

    /**
     * Translate question using Gemini API
     * 
     * @param string $textToTranslate
     * @param string $targetLanguage
     * @return string|null
     */
    protected function translateWithGemini($textToTranslate, $targetLanguage)
    {
        $apiKey = env('GEMINI_API_KEY');
        $geminiPrompt = "Translate this survey content from English to {$targetLanguage}. Follow these rules:\n\n"
            . "FORMAT: Keep exact prefixes (Q:, C0:, C1:, etc.) - one item per line\n"
            . "STYLE: Use formal, clear language for survey respondents\n"
            . "ACCURACY: Double-check by mentally back-translating to English\n"
            . "OUTPUT: Only translated text with prefixes - no explanations\n\n"
            . "TEXT:\n{$textToTranslate}\n\n"
            . "Provide verified {$targetLanguage} translation:";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $geminiPrompt]]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 300,
                'temperature' => 0.1,
                'topP' => 0.9,
                'topK' => 40,
            ],
        ]);

        if ($response->successful()) {
            Log::info('Gemini translation successful.');
            return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } else {
            Log::error('Gemini translation failed: ' . $response->body());
            return null;
        }
    }

    public function translateQuestion($questionId = null, $language = null)
    {
        if (is_array($questionId)) {
            $params = $questionId;
            $questionId = $params['questionId'] ?? null;
            $language = $params['language'] ?? null;
        }

        if (!$questionId || !$language) return;

        $this->translatingQuestions[$questionId] = true;
        $this->isLoading = true;
        $this->dispatch('$refresh');

        // Locate the question
        $question = null;
        foreach ($this->survey->pages as $page) {
            $foundQuestion = $page->questions->firstWhere('id', $questionId);
            if ($foundQuestion) {
                $question = $foundQuestion;
                break;
            }
        }

        if (!$question) {
            $this->translatingQuestions[$questionId] = false;
            $this->isLoading = false;
            $this->dispatch('$refresh');
            return;
        }

        if ($language === 'en') {
            $this->translatedQuestions[$questionId] = null;
            $this->translatedChoices[$questionId] = null;
            $this->translatingQuestions[$questionId] = false;
            $this->isLoading = false;
            $this->dispatch('$refresh');
            return;
        }

        $originalText = $question->question_text;
        $textsToTranslate = ["Q: {$originalText}"];
        $choiceMapping = [];
        $likertMapping = [];
        $hasChoices = false;
        $hasLikert = false;

        // Handle multiple choice and radio questions
        if (in_array($question->question_type, ['multiple_choice', 'radio']) && $question->choices->count() > 0) {
            $hasChoices = true;
            foreach ($question->choices as $index => $choice) {
                $choiceKey = "C{$index}";
                $textsToTranslate[] = "{$choiceKey}: {$choice->choice_text}";
                $choiceMapping[$choiceKey] = $choice->id;
            }
        }

        // Handle Likert scale questions
        if ($question->question_type === 'likert') {
            $hasLikert = true;
            
            // Get Likert rows (statements)
            $likertRows = $this->getLikertRows($question);
            if (!empty($likertRows)) {
                foreach ($likertRows as $rowIndex => $rowText) {
                    $rowKey = "R{$rowIndex}";
                    $textsToTranslate[] = "{$rowKey}: {$rowText}";
                    $likertMapping['rows'][$rowKey] = $rowIndex;
                }
            }
            
            // Get Likert columns (scale options)
            $likertColumns = [];
            if (is_array($question->likert_columns)) {
                $likertColumns = $question->likert_columns;
            } else {
                $likertColumns = json_decode($question->likert_columns, true) ?: [];
            }
            
            if (!empty($likertColumns)) {
                foreach ($likertColumns as $colIndex => $colText) {
                    $colKey = "COL{$colIndex}";
                    $textsToTranslate[] = "{$colKey}: {$colText}";
                    $likertMapping['columns'][$colKey] = $colIndex;
                }
            }
        }

        $textToTranslate = implode("\n", $textsToTranslate);

        $languageNames = [
            'tl' => 'Filipino',
            'zh-CN' => 'Simplified Chinese',
            'zh-TW' => 'Traditional Chinese',
            'ar' => 'Arabic',
            'ja' => 'Japanese',
            'vi' => 'Vietnamese',
            'th' => 'Thai',
            'ms' => 'Malay',
        ];
        $targetLanguage = $languageNames[$language] ?? $language;

        $translatedText = null;

        // Try DeepSeek first
        try {
            $translatedText = $this->translateWithDeepSeek($textToTranslate, $targetLanguage);
        } catch (\Exception $e) {
            Log::error('DeepSeek exception: ' . $e->getMessage());
        }

        // Fallback to Gemini if DeepSeek failed
        if (!$translatedText) {
            try {
                $translatedText = $this->translateWithGemini($textToTranslate, $targetLanguage);
            } catch (\Exception $e) {
                Log::error('Gemini exception: ' . $e->getMessage());
            }
        }

        // Process output
        $translatedQuestion = null;
        $translatedChoicesData = [];
        $translatedLikertData = [];

        if ($translatedText) {
            foreach (explode("\n", $translatedText) as $line) {
                if (!is_string($line)) continue;
                $line = trim($line);

                if (str_starts_with($line, 'Q:')) {
                    $translatedQuestion = trim(substr($line, 2));
                } 
                // Handle multiple choice/radio choices
                elseif ($hasChoices && preg_match('/^C(\d+):\s*(.+)$/', $line, $matches)) {
                    $choiceKey = "C" . $matches[1];
                    $choiceText = trim($matches[2]);
                    if (isset($choiceMapping[$choiceKey])) {
                        $choiceId = $choiceMapping[$choiceKey];
                        $translatedChoicesData[$choiceId] = $choiceText;
                    }
                }
                // Handle Likert rows
                elseif ($hasLikert && preg_match('/^R(\d+):\s*(.+)$/', $line, $matches)) {
                    $rowKey = "R" . $matches[1];
                    $rowText = trim($matches[2]);
                    if (isset($likertMapping['rows'][$rowKey])) {
                        $rowIndex = $likertMapping['rows'][$rowKey];
                        $translatedLikertData['rows'][$rowIndex] = $rowText;
                    }
                }
                // Handle Likert columns
                elseif ($hasLikert && preg_match('/^COL(\d+):\s*(.+)$/', $line, $matches)) {
                    $colKey = "COL" . $matches[1];
                    $colText = trim($matches[2]);
                    if (isset($likertMapping['columns'][$colKey])) {
                        $colIndex = $likertMapping['columns'][$colKey];
                        $translatedLikertData['columns'][$colIndex] = $colText;
                    }
                }
            }
        }

        // Store results
        $this->translatedQuestions[$questionId] = $translatedQuestion ?? $originalText . ' (Translation incomplete)';
        
        if ($hasChoices) {
            $this->translatedChoices[$questionId] = $translatedChoicesData;
        }
        
        if ($hasLikert) {
            $this->translatedChoices[$questionId] = $translatedLikertData;
        }

        $this->translatingQuestions[$questionId] = false;
        $this->isLoading = false;
        $this->dispatch('$refresh');
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.surveys.answer-survey.answer-survey', [
            'survey' => $this->survey,
            'answers' => $this->answers,
            'translatedQuestions' => $this->translatedQuestions,
            'translatedChoices' => $this->translatedChoices,
            'isLoading' => $this->isLoading,
        ]);
    }
}
