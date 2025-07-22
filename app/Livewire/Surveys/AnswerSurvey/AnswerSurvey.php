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
use App\Services\TestTimeService;
use Carbon\Carbon;

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
     * Track when the survey was started
     * @var Carbon
     */
    public $startedAt;

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

        // Track survey start time when component is mounted (not in preview mode)
        if (!$this->isPreview) {
            $this->startedAt = TestTimeService::now();
        }
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
     * Validate if survey is still accepting responses
     * 
     * @return array|null Returns error details if validation fails, null if valid
     */
    protected function validateSurveyAvailability()
    {
        $now = TestTimeService::now();
        
        // Check if survey has ended
        if ($this->survey->end_date && $now->gt($this->survey->end_date)) {
            return [
                'type' => 'expired',
                'title' => 'Survey Expired',
                'message' => 'This survey has ended and is no longer accepting responses.',
                'icon' => 'error'
            ];
        }
        
        // Check if survey has reached response limit
        if ($this->survey->target_respondents) {
            $currentResponseCount = Response::where('survey_id', $this->survey->id)->count();
            if ($currentResponseCount >= $this->survey->target_respondents) {
                return [
                    'type' => 'limit_reached',
                    'title' => 'Response Limit Reached',
                    'message' => 'This survey has reached its maximum number of responses and is no longer accepting new submissions.',
                    'icon' => 'warning'
                ];
            }
        }
        
        // Check if survey is locked
        if ($this->survey->is_locked) {
            return [
                'type' => 'locked',
                'title' => 'Survey Unavailable',
                'message' => $this->survey->lock_reason ?? 'This survey is currently unavailable.',
                'icon' => 'error'
            ];
        }
        
        // Check if survey status allows responses
        if (!in_array($this->survey->status, ['published', 'ongoing'])) {
            return [
                'type' => 'unavailable',
                'title' => 'Survey Not Available',
                'message' => 'This survey is not currently accepting responses.',
                'icon' => 'error'
            ];
        }
        
        return null; // All validations passed
    }

    /**
     * Check if user has already responded to this survey
     * 
     * @return array|null Returns error details if user already responded, null if valid
     */
    protected function validateUserResponse()
    {
        // Temporarily commented out to allow multiple responses
        /*
        $user = Auth::user();
        if (!$user) {
            return null; // Allow anonymous responses if not authenticated
        }
        
        $existingResponse = Response::where('survey_id', $this->survey->id)
                                  ->where('user_id', $user->id)
                                  ->exists();
        
        if ($existingResponse) {
            return [
                'type' => 'already_responded',
                'title' => 'Already Submitted',
                'message' => 'You have already submitted a response to this survey.',
                'icon' => 'info'
            ];
        }
        */
        
        return null;
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
            try {
                // In preview mode, skip all validations and just show success
                if ($this->isPreview) {
                    // Validate form data for preview
                    $this->validate($this->getAllValidationRules(), $this->getAllValidationMessages());
                    
                    session()->flash('success', 'Preview submitted successfully! (No data saved)');
                    
                    // If user is a super admin, redirect to surveys index
                    if (Auth::check() && Auth::user()->isSuperAdmin()) {
                        return redirect()->route('surveys.index');
                    }
                    
                    // Otherwise redirect to form builder
                    return redirect()->route('surveys.create', ['survey' => $this->survey->id]);
                }

                // Validate survey availability (end date, response limits, etc.)
                $availabilityError = $this->validateSurveyAvailability();
                if ($availabilityError) {
                    $this->dispatch('surveySubmissionError', $availabilityError);
                    return;
                }

                // Validate user hasn't already responded - COMMENTED OUT
                /*
                $userResponseError = $this->validateUserResponse();
                if ($userResponseError) {
                    $this->dispatch('surveySubmissionError', $userResponseError);
                    return;
                }
                */

                // Validate all questions in the survey
                $this->validate($this->getAllValidationRules(), $this->getAllValidationMessages());

                // Save the responses
                $this->saveResponses();
                
                // Show success message
                $this->dispatch('surveySubmitted', [
                    'title' => 'Survey Completed!',
                    'message' => 'Thank you for completing the survey.',
                    'points' => $this->survey->points_allocated,
                    'surveyName' => $this->survey->title,
                    'xp' => 100 // always 100 XP per survey
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                // Re-throw validation exceptions to show form errors
                throw $e;
            } catch (\Exception $e) {
                // Log the error for debugging
                Log::error('Survey submission error: ' . $e->getMessage(), [
                    'survey_id' => $this->survey->id,
                    'user_id' => Auth::id(),
                    'error' => $e->getTraceAsString()
                ]);

                // Show generic error message
                $this->dispatch('surveySubmissionError', [
                    'type' => 'system_error',
                    'title' => 'Submission Failed',
                    'message' => 'An unexpected error occurred while submitting your response. Please try again or contact support if the problem persists.',
                    'icon' => 'error'
                ]);
            }
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

            // Get started_at from component property and completed_at from current time
            $startedAt = $this->startedAt ?? TestTimeService::now();
            $completedAt = TestTimeService::now();
            
            // Calculate completion time in seconds
            $completionTimeSeconds = $startedAt->diffInSeconds($completedAt);

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
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                    'completion_time_seconds' => $completionTimeSeconds,
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

            // Award 100 XP to user after answering survey
            if ($user) {
                $xpResult = $user->addExperiencePoints(100); // always 100 XP per survey
                Log::info("Awarded 100 XP to user ID: {$user->id}", ['xpResult' => $xpResult]);
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

        try {
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
                $translatedText = $response['choices'][0]['message']['content'];
                Log::info('DeepSeek translation successful.');
                Log::info('Translated text: ' . $translatedText);
                return $translatedText;
            } else {
                Log::error('DeepSeek translation failed: ' . $response->body());
                return null;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepSeek timeout or connection error: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('DeepSeek unexpected error: ' . $e->getMessage());
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

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $geminiPrompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 8912,
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini timeout or connection error: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Gemini unexpected error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Translate a question and its choices/likert using DeepSeek API.
     *
     * @param string $questionText
     * @param array $choices (optional)
     * @param array $likertRows (optional)
     * @param array $likertColumns (optional)
     * @param string $targetLanguage
     * @return array|null [ 'question' => ..., 'choices' => [...], 'likert_rows' => [...], 'likert_columns' => [...] ]
     */
    protected function translateQuestionWithAI($questionText, $choices = [], $likertRows = [], $likertColumns = [], $targetLanguage = 'Filipino')
    {
        $input = [
            'question' => $questionText,
        ];
        if (!empty($choices)) {
            $input['choices'] = array_values($choices);
        }
        if (!empty($likertRows)) {
            $input['likert_rows'] = array_values($likertRows);
        }
        if (!empty($likertColumns)) {
            $input['likert_columns'] = array_values($likertColumns);
        }
        $inputJson = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt = "Translate the following question and its options to {$targetLanguage}. If the input text is already in the target language, return the input JSON unchanged. Return ONLY a valid JSON object with the same structure as the input, but with all text translated. Do NOT include any explanations or extra text.\n\nINPUT:\n{$inputJson}\n\nOUTPUT:";
        $endpoint = rtrim(env('AZURE_DEEPSEEK_ENDPOINT'), '/');
        $apiKey = env('AZURE_DEEPSEEK_KEY');
        $modelName = "DeepSeek-R1-0528";
        $apiVersion = "2024-05-01-preview";
        $apiUrl = "{$endpoint}/openai/deployments/{$modelName}/chat/completions?api-version={$apiVersion}";
        try {
            Log::info('[Translation] Using AI: DeepSeek');
            Log::info('[Translation] Q: ' . $questionText);
            if (!empty($choices)) {
                Log::info('[Translation] C: ' . json_encode($choices, JSON_UNESCAPED_UNICODE));
            }
            if (!empty($likertRows)) {
                Log::info('[Translation] Likert Rows: ' . json_encode($likertRows, JSON_UNESCAPED_UNICODE));
            }
            if (!empty($likertColumns)) {
                Log::info('[Translation] Likert Columns: ' . json_encode($likertColumns, JSON_UNESCAPED_UNICODE));
            }
            Log::info('[Translation] Translating...');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->timeout(15)->post($apiUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional translator.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 8912,
                'temperature' => 0.1
            ]);
            if ($response->successful() && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                Log::info('[Translation] Raw AI response: ' . mb_substr($content, 0, 1000));
                // Try to extract JSON from the response
                if (!empty($content)) {
                    $json = null;
                    // Try direct decode
                    $json = json_decode($content, true);
                    if (!$json) {
                        // Try to extract first JSON object from text
                        if (preg_match('/\{.*\}/s', $content, $matches)) {
                            $json = json_decode($matches[0], true);
                        }
                    }
                    if (is_array($json) && isset($json['question'])) {
                        Log::info('[Translation] Parsed JSON: ' . json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                        return $json;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('DeepSeek translation error: ' . $e->getMessage());
        }
        Log::error('DeepSeek translation failed or returned invalid JSON.');
        return null;
    }

    /**
     * Translate a question and its choices/likert using Gemini API (fallback).
     *
     * @param string $questionText
     * @param array $choices (optional)
     * @param array $likertRows (optional)
     * @param array $likertColumns (optional)
     * @param string $targetLanguage
     * @return array|null [ 'question' => ..., 'choices' => [...], 'likert_rows' => [...], 'likert_columns' => [...] ]
     */
    protected function translateQuestionWithGemini($questionText, $choices = [], $likertRows = [], $likertColumns = [], $targetLanguage = 'Filipino')
    {
        $input = [
            'question' => $questionText,
        ];
        if (!empty($choices)) {
            $input['choices'] = array_values($choices);
        }
        if (!empty($likertRows)) {
            $input['likert_rows'] = array_values($likertRows);
        }
        if (!empty($likertColumns)) {
            $input['likert_columns'] = array_values($likertColumns);
        }
        $inputJson = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt = "Translate the following question and its options to {$targetLanguage}. If the input text is already in the target language, return the input JSON unchanged. Return ONLY a valid JSON object with the same structure as the input, but with all text translated. Do NOT include any explanations or extra text.\n\nINPUT:\n{$inputJson}\n\nOUTPUT:";
        $apiKey = env('GEMINI_API_KEY');
        try {
            Log::info('[Translation] Using AI: Gemini');
            Log::info('[Translation] Q: ' . $questionText);
            if (!empty($choices)) {
                Log::info('[Translation] C: ' . json_encode($choices, JSON_UNESCAPED_UNICODE));
            }
            if (!empty($likertRows)) {
                Log::info('[Translation] Likert Rows: ' . json_encode($likertRows, JSON_UNESCAPED_UNICODE));
            }
            if (!empty($likertColumns)) {
                Log::info('[Translation] Likert Columns: ' . json_encode($likertColumns, JSON_UNESCAPED_UNICODE));
            }
            Log::info('[Translation] Translating...');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 8912,
                    'temperature' => 0.1,
                    'topP' => 0.9,
                    'topK' => 40,
                ],
            ]);
            if ($response->successful()) {
                $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
                Log::info('[Translation] Raw AI response: ' . mb_substr($content, 0, 1000));
                if (!empty($content)) {
                    $json = null;
                    $json = json_decode($content, true);
                    if (!$json) {
                        if (preg_match('/\{.*\}/s', $content, $matches)) {
                            $json = json_decode($matches[0], true);
                        }
                    }
                    if (is_array($json) && isset($json['question'])) {
                        Log::info('[Translation] Parsed JSON: ' . json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                        return $json;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini translation error: ' . $e->getMessage());
        }
        Log::error('Gemini translation failed or returned invalid JSON.');
        return null;
    }

    /**
     * Livewire method to translate a question (and its choices/likert) and store the result.
     *
     * @param int $questionId
     * @param string $language
     */
    public function translateQuestion($questionId = null, $language = null)
    {
        set_time_limit(60);
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
        // Prepare variables for translation
        $choices = [];
        $likertRows = [];
        $likertColumns = [];
        if (in_array($question->question_type, ['multiple_choice', 'radio']) && $question->choices->count() > 0) {
            foreach ($question->choices as $choice) {
                $choices[] = $choice->choice_text;
            }
        }
        if ($question->question_type === 'likert') {
            $likertRows = $this->getLikertRows($question);
            $likertColumns = is_array($question->likert_columns)
                ? $question->likert_columns
                : (json_decode($question->likert_columns, true) ?: []);
        }
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
        // If target language is English and question is in English, skip translation
        if (($language === 'en' || $targetLanguage === 'English')) {
            $this->translatedQuestions[$questionId] = null;
            $this->translatedChoices[$questionId] = null;
            $this->translatingQuestions[$questionId] = false;
            $this->isLoading = false;
            $this->dispatch('$refresh');
            return;
        }
        // Call DeepSeek translation first
        $result = $this->translateQuestionWithAI($question->question_text, $choices, $likertRows, $likertColumns, $targetLanguage);
        // If DeepSeek fails, try Gemini
        if (!$result) {
            $result = $this->translateQuestionWithGemini($question->question_text, $choices, $likertRows, $likertColumns, $targetLanguage);
        }
        if ($result) {
            $this->translatedQuestions[$questionId] = $result['question'] ?? null;
            if (!empty($choices) && isset($result['choices']) && is_array($result['choices'])) {
                // Map back to choice IDs
                $translatedChoices = [];
                foreach ($question->choices as $idx => $choice) {
                    $translatedChoices[$choice->id] = $result['choices'][$idx] ?? $choice->choice_text;
                }
                $this->translatedChoices[$questionId] = $translatedChoices;
            }
            if ($question->question_type === 'likert') {
                $translatedLikert = [
                    'rows' => [],
                    'columns' => [],
                ];
                if (isset($result['likert_rows']) && is_array($result['likert_rows'])) {
                    foreach ($result['likert_rows'] as $idx => $rowText) {
                        $translatedLikert['rows'][$idx] = $rowText;
                    }
                }
                if (isset($result['likert_columns']) && is_array($result['likert_columns'])) {
                    foreach ($result['likert_columns'] as $idx => $colText) {
                        $translatedLikert['columns'][$idx] = $colText;
                    }
                }
                $this->translatedChoices[$questionId] = $translatedLikert;
            }
        } else {
            $this->translatedQuestions[$questionId] = $question->question_text . ' (Translation failed)';
            $this->translatedChoices[$questionId] = null;
        }
        // Add a 1-second delay before allowing the next translation
        sleep(1);
        $this->translatingQuestions[$questionId] = false;
        $this->isLoading = false;
        $this->dispatch('$refresh');
    }

    // Add a method to revert translation for a question
    public function revertTranslation($questionId)
    {
        $this->translatedQuestions[$questionId] = null;
        $this->translatedChoices[$questionId] = null;
        $this->dispatch('$refresh');
    }
    
    /**
     * Helper method to save a translated section with multi-line support
     */
    private function saveTranslatedSection($sectionKey, $content, &$translatedQuestion, &$translatedChoicesData, &$translatedLikertData, $choiceMapping, $likertMapping, $hasChoices, $hasLikert)
    {
        // Join content with newlines to preserve line breaks
        $fullContent = implode("\n", $content);
        
        if ($sectionKey === 'Q') {
            // For questions, preserve the newlines for proper display
            $translatedQuestion = $fullContent;
        } 
        // Handle multiple choice/radio choices
        elseif ($hasChoices && isset($choiceMapping[$sectionKey])) {
            $choiceId = $choiceMapping[$sectionKey];
            $translatedChoicesData[$choiceId] = $fullContent;
        }
        // Handle Likert rows
        elseif ($hasLikert && isset($likertMapping['rows'][$sectionKey])) {
            $rowIndex = $likertMapping['rows'][$sectionKey];
            $translatedLikertData['rows'][$rowIndex] = $fullContent;
        }
        // Handle Likert columns
        elseif ($hasLikert && isset($likertMapping['columns'][$sectionKey])) {
            $colIndex = $likertMapping['columns'][$sectionKey];
            $translatedLikertData['columns'][$colIndex] = $fullContent;
        }
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
