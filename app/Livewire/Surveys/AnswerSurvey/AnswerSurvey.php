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
use Illuminate\Support\Str;
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
        
        // Check if user is guest and survey doesn't allow guest responses
        if (!Auth::check() && !$this->survey->is_guest_allowed && !$this->isPreview) {
            abort(403, 'This survey requires you to be logged in to respond.');
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
     * Gets validation rules for either the current page or all questions
     * 
     * @param bool $allPages Whether to get rules for all pages (true) or just current page (false)
     * @return array Validation rules array formatted for Laravel validator
     */
    protected function getValidationRules($allPages = false)
    {
        $rules = [];
        
        if ($allPages) {
            // Get rules for all questions in the survey
            foreach ($this->survey->pages as $page) {
                foreach ($page->questions as $question) {
                    $rules = array_merge($rules, $this->getRulesForQuestion($question));
                }
            }
        } else {
            // Get rules for current page only
            if (!$this->survey->pages->has($this->currentPage)) {
                return $rules;
            }
            
            $currentPageQuestions = $this->survey->pages[$this->currentPage]->questions;
            foreach ($currentPageQuestions as $question) {
                $rules = array_merge($rules, $this->getRulesForQuestion($question));
            }
        }
        
        return $rules;
    }

    /**
     * Gets validation messages for either the current page or all questions
     * 
     * @param bool $allPages Whether to get messages for all pages (true) or just current page (false)
     * @return array Validation messages keyed by field and rule
     */
    protected function getValidationMessages($allPages = false)
    {
        $messages = [];
        
        if ($allPages) {
            // Get messages for all questions
            $questionNumber = 1;
            foreach ($this->survey->pages as $page) {
                foreach ($page->questions as $question) {
                    $messages = array_merge(
                        $messages, 
                        $this->getMessagesForQuestion($question, $questionNumber)
                    );
                    $questionNumber++;
                }
            }
        } else {
            // Get messages for current page only
            if (!$this->survey->pages->has($this->currentPage)) {
                return $messages;
            }
            
            // Calculate question numbering across pages
            $questionNumber = 1;
            for ($i = 0; $i < $this->currentPage; $i++) {
                if ($this->survey->pages->has($i)) {
                    $questionNumber += $this->survey->pages[$i]->questions->count();
                }
            }
            
            $currentPageQuestions = $this->survey->pages[$this->currentPage]->questions;
            foreach ($currentPageQuestions as $question) {
                $messages = array_merge(
                    $messages, 
                    $this->getMessagesForQuestion($question, $questionNumber)
                );
                $questionNumber++;
            }
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
     * Process form submission - handles both page navigation and final submission
     */
    public function submit()
    {
        // Handle navigation to next page
        if ($this->navAction === 'next') {
            try {
                // Check if current page has questions before validating
                if ($this->survey->pages->has($this->currentPage) && 
                    $this->survey->pages[$this->currentPage]->questions->count() > 0) {
                    // Validate only the current page
                    // handles the error message through the rules for each question
                    $this->validate($this->getValidationRules(false), $this->getValidationMessages(false));
               
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
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Dispatch event for SweetAlert notification
                $this->dispatch('showValidationAlert');
                
                // Re-throw to show inline errors
                throw $e;
            }
            return;
        }



        
        // Handle final submission
        if ($this->navAction === 'submit') {
            try {
                // In preview mode, skip all validations and just show success
                if ($this->isPreview) {
                    // Validate form data for preview
                    $this->validate($this->getValidationRules(true), $this->getValidationMessages(true));
                    
                    session()->flash('success', 'Preview submitted successfully! (No data saved)');
                    
                    // If user is a super admin, redirect to admin surveys index
                    if (Auth::check() && Auth::user()->type === 'super_admin') {
                        return redirect()->route('admin.surveys.index');
                    }
                    
                    // Otherwise redirect to form builder
                    return redirect()->route('surveys.create', ['survey' => $this->survey->uuid]);
                }

                

                // Validate survey availability (end date, response limits, etc.)
                $availabilityError = $this->validateSurveyAvailability();
                if ($availabilityError) {
                    $this->dispatch('surveySubmissionError', $availabilityError);
                    return;
                }

                // Validate all questions in the survey
                $this->validate($this->getValidationRules(true), $this->getValidationMessages(true));

                // Save the responses
                $this->saveResponses();
                
                // Show success message
                $this->dispatch('surveySubmitted', [
                    'title' => 'Survey Completed!',
                    'message' => 'Thank you for completing the survey.',
                    'points' => $this->survey->points_allocated,
                    'surveyName' => $this->survey->title,
                    'xp' => 100 
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                // Dispatch event for SweetAlert notification
                $this->dispatch('showValidationAlert');
                
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
        try {
            DB::beginTransaction();
            
            $user = Auth::user(); // This will be null for guest users
            
            // Update survey status if it's the first response
            if ($this->survey->status === 'published') {
                $this->survey->status = 'ongoing';
                $this->survey->save();
                
                // Create a survey snapshot if this is the first response
                $this->createSurveySnapshot();
            }

            // Get started_at from component property and completed_at from current time
            $startedAt = $this->startedAt ?? TestTimeService::now();
            $completedAt = TestTimeService::now();
            
            // Calculate completion time in seconds
            $completionTimeSeconds = $startedAt->diffInSeconds($completedAt);

            // Explicitly set UUID for PostgreSQL
            $response = Response::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'survey_id' => $this->survey->id,
                'user_id' => $user?->id, // Will be null for guest users
                'reported' => false,
            ]);

            if (!$response) {
                Log::error('Failed to create response record');
                DB::rollBack();
                throw new \Exception('Failed to create response record');
            }
            
            Log::info('Response saved successfully', ['response_id' => $response->id]);
            
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

                // Create the snapshot record - cast completion_time_seconds to integer
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
                    'completion_time_seconds' => (int)$completionTimeSeconds,
                    'demographic_tags' => json_encode($demographicTags)
                ]);
            } else {
                // Create a minimal snapshot for guest users
                $response->snapshot()->create([
                    'first_name' => 'Guest',
                    'last_name' => 'User',
                    'trust_score' => 100,
                    'points' => 0,
                    'account_level' => 0,
                    'experience_points' => 0,
                    'rank' => 'silver',
                    'title' => null,
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                    'completion_time_seconds' => (int)$completionTimeSeconds,
                    'demographic_tags' => json_encode([])
                ]);
            }

            // Process answers for each question
            foreach ($this->answers as $questionId => $answerValue) {
                $question = SurveyQuestion::find($questionId);
                if (!$question) continue;

                $this->saveAnswerForQuestion($response, $question, $answerValue);
            }

            // Track if points were awarded
            $pointsAwarded = false;
            
            // Award points to user if authenticated, has sufficient trust score, and points are configured
            if ($user && $this->survey->points_allocated > 0) {
                // Only award points if trust score is above 70
                if ($user->trust_score > 70) {
                    $user->points = ($user->points ?? 0) + $this->survey->points_allocated;
                    $pointsAwarded = true;
                    if ($user instanceof User) {
                        try {
                            $user->save();
                        } catch (\Exception $e) {
                            Log::error("Error saving user points for user ID: {$user->id}. Error: " . $e->getMessage());
                        }
                    }
                }
            }
            
            // Award +1 trust score to authenticated users
            if ($user) {
                try {
                    $user->trust_score = ($user->trust_score ?? 0) + 1;
                    $user->save();
                    Log::info("Increased trust score by 1 for user ID: {$user->id}");
                } catch (\Exception $e) {
                    Log::error("Error updating trust score for user ID: {$user->id}. Error: " . $e->getMessage());
                }
            }

            // Award 100 XP to user after answering survey (only if authenticated)
            if ($user) {
                $xpResult = $user->addExperiencePoints(100); // always 100 XP per survey
                Log::info("Awarded 100 XP to user ID: {$user->id}", ['xpResult' => $xpResult]);
                
                // Check if user leveled up and dispatch event for animation
                if (isset($xpResult['leveled_up']) && $xpResult['leveled_up']) {
                    Log::info("User leveled up! Dispatching level-up event", [
                        'user_id' => $user->id,
                        'new_level' => $xpResult['new_level'],
                        'new_rank' => $xpResult['new_rank'],
                        'old_rank' => $xpResult['old_rank']
                    ]);
                    
                    $this->dispatch('level-up', [
                        'level' => $xpResult['new_level'],
                        'rank' => $xpResult['new_rank'],
                        'old_rank' => $xpResult['old_rank']
                    ]);
                } else {
                    Log::info("User did not level up", [
                        'user_id' => $user->id,
                        'leveled_up' => $xpResult['leveled_up'] ?? false,
                        'current_level' => $xpResult['new_level'] ?? 'unknown'
                    ]);
                }
            }

            // Notify survey creator if response limit is reached (and only once)
            if ($this->survey->target_respondents) {
                $currentResponseCount = Response::where('survey_id', $this->survey->id)->count();
                if ($currentResponseCount == $this->survey->target_respondents) {
                    // Update survey status to finished when target is reached
                    if (in_array($this->survey->status, ['published', 'ongoing'])) {
                        $this->survey->status = 'finished';
                        $this->survey->save();
                    }
                    
                    // Only send if not already notified (check for existing message)
                    $creatorId = $this->survey->user_id;
                    $existing = \App\Models\InboxMessage::where('recipient_id', $creatorId)
                        ->where('subject', 'Survey Completed')
                        ->where('url', url('/surveys/create/' . $this->survey->id))
                        ->first();
                    if (!$existing) {
                        \App\Models\InboxMessage::create([
                            'recipient_id' => $creatorId,
                            'subject' => 'Survey Completed',
                            'message' => "Your survey '{$this->survey->title}' has reached its response limit of {$this->survey->target_respondents} respondents and is now complete.",
                            'url' => url('/surveys/create/' . $this->survey->id),
                        ]);
                    }
                }
            }

            DB::commit();

            // Set different success messages based on user state and trust score
            if (!Auth::check()) {
                $this->dispatch('surveySubmitted', [
                    'title' => 'Survey Completed!',
                    'message' => 'Thank you for completing the survey as a guest. Sign up to earn rewards for future surveys!',
                    'points' => 0,
                    'surveyName' => $this->survey->title,
                    'xp' => 0,
                    'isGuest' => true
                ]);
            } else if ($user->trust_score <= 70) {
                $this->dispatch('surveySubmitted', [
                    'title' => 'Survey Completed!',
                    'message' => 'Thank you for completing the survey. No points were awarded due to your current trust score being 70 or below.',
                    'points' => 0,
                    'surveyName' => $this->survey->title,
                    'xp' => 100,
                    'isGuest' => false,
                    'lowTrustScore' => true  // Add explicit flag
                ]);
            } else {
                $this->dispatch('surveySubmitted', [
                    'title' => 'Survey Completed!',
                    'message' => 'Thank you for completing the survey.',
                    'points' => $this->survey->points_allocated,
                    'surveyName' => $this->survey->title,
                    'xp' => 100,
                    'isGuest' => false
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Survey submission error in saveResponses: ' . $e->getMessage(), [
                'survey_id' => $this->survey->id,
                'user_id' => Auth::id(),
                'error_class' => get_class($e),
                'error' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Create a snapshot of the survey's current state including demographic tags
     */
    protected function createSurveySnapshot()
    {
        // Check if a snapshot already exists
        if ($this->survey->snapshot()->exists()) {
            return;
        }
        
        try {
            // Load tags and topic if not already loaded
            if (!$this->survey->relationLoaded('tags')) {
                $this->survey->load('tags');
            }
            
            // Load the topic relation if it exists
            if (!$this->survey->relationLoaded('topic')) {
                $this->survey->load('topic');
            }
            
            // Create demographic tags JSON
            $demographicTags = [];
            foreach ($this->survey->tags as $tag) {
                $demographicTags[] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'category_id' => $tag->tag_category_id,
                    'category_name' => $tag->category ? $tag->category->name : null
                ];
            }
            
            // Get survey topic information
            $topicData = null;
            if ($this->survey->topic) {
                $topicData = [
                    'id' => $this->survey->topic->id,
                    'name' => $this->survey->topic->name
                ];
            }
            
            // Create additional metadata
            $metadata = [
                'type' => $this->survey->type,
                'is_institution_only' => $this->survey->is_institution_only,
                'start_date' => $this->survey->start_date ? $this->survey->start_date->toDateTimeString() : null,
                'end_date' => $this->survey->end_date ? $this->survey->end_date->toDateTimeString() : null,
                'survey_topic' => $topicData,
            ];
            
            // Create the snapshot
            $this->survey->snapshot()->create([
                'title' => $this->survey->title,
                'description' => $this->survey->description,
                'target_respondents' => $this->survey->target_respondents,
                'points_allocated' => $this->survey->points_allocated,
                'demographic_tags' => $demographicTags,
                'first_response_at' => TestTimeService::now(),
                'metadata' => $metadata
            ]);
            
            Log::info('Created survey snapshot', ['survey_id' => $this->survey->id]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create survey snapshot: ' . $e->getMessage(), [
                'survey_id' => $this->survey->id,
                'error' => $e->getTraceAsString()
            ]);
            // Don't throw the exception - continue with response creation
        }
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
        try {
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
                        $answerData['answer'] = json_encode($selectedChoiceIds, JSON_THROW_ON_ERROR);
                        
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
                        $answerData['answer'] = json_encode($answerValue, JSON_THROW_ON_ERROR);
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
        } catch (\Exception $e) {
            Log::error('Error saving answer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'response_id' => $response->id, 
                'error' => $e->getTraceAsString()
            ]);
            throw $e;  // Re-throw to be caught by parent method
        }
    }



























    
    /**
     * Navigate to the previous page
     */
    public function goToPreviousPage()
    {
        if ($this->currentPage > 0) {
            $this->currentPage--;
            $this->dispatch('pageChanged');
        }
    }

    

  






































    /**
     * Translate content using AI (DeepSeek or Gemini)
     * 
     * @param array $content The content to translate (question, choices, etc.)
     * @param string $targetLanguage The target language
     * @param string $provider Which AI provider to use ('deepseek' or 'gemini')
     * @return array|null Translated content array or null on failure
     */
    protected function translateWithAI($content, $targetLanguage, $provider = 'deepseek')
    {
        $inputJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        // Create appropriate prompt based on provider
        $prompt = "Translate the following JSON object from English to {$targetLanguage}. You MUST return ONLY a valid JSON object with NO additional text.\n\n" .
            "CRITICAL RULES:\n" .
            "1. Return ONLY valid parseable JSON\n" .
            "2. Keep the exact same JSON structure and property names\n" .
            "3. Only translate the text values, not the keys\n" .
            "4. Do not add ANY explanations, markdown formatting, or code blocks\n" .
            "5. If the input is already in {$targetLanguage}, return it unchanged\n\n" .
            "INPUT JSON:\n{$inputJson}\n\n" .
            "ONLY RETURN THE TRANSLATED JSON:";
        
        try {
            Log::info("[Translation] Using AI: " . ucfirst($provider));
            Log::info("[Translation] Input: " . mb_substr($inputJson, 0, 500) . (strlen($inputJson) > 500 ? '...' : ''));
            
            $response = null;
            
            // Call the appropriate AI service
            if ($provider === 'deepseek') {
                $response = $this->callDeepSeekAPI($prompt);
            } else if ($provider === 'gemini') {
                $response = $this->callGeminiAPI($prompt, $targetLanguage);
            }
            
            if ($response) {
                // Extract and parse JSON from the response
                $json = $this->extractJsonFromResponse($response);
                
                if ($json) {
                    Log::info('[Translation] Successfully parsed JSON response');
                    return $json;
                }
            }
        } catch (\Exception $e) {
            Log::error($provider . ' translation error: ' . $e->getMessage());
        }
        
        Log::error($provider . ' translation failed or returned invalid JSON.');
        return null;
    }




    
    /**
     * Call DeepSeek API
     * 
     * @param string $prompt The prompt to send
     * @return string|null Raw response content or null on failure
     */
    private function callDeepSeekAPI($prompt)
    {
        $endpoint = rtrim(config('services.deepseek.endpoint'), '/');
        $apiKey = config('services.deepseek.api_key');
        $modelName = "DeepSeek-R1-0528";
        $apiVersion = "2024-05-01-preview";
        $apiUrl = "{$endpoint}/openai/deployments/{$modelName}/chat/completions?api-version={$apiVersion}";
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->timeout(20)->post($apiUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional translator.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 8912,
                'temperature' => 0.1
            ]);
            
            if ($response->successful() && isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            } else {
                Log::error('DeepSeek API error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('DeepSeek API call failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Call Gemini API
     * 
     * @param string $prompt The prompt to send
     * @param string $targetLanguage The target language
     * @return string|null Raw response content or null on failure
     */
    private function callGeminiAPI($prompt, $targetLanguage)
    {
        $apiKey = config('services.gemini.api_key');
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 8912,
                    'temperature' => 0.1,
                    'topP' => 0.9,
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
                return $response['candidates'][0]['content']['parts'][0]['text'];
            } else {
                $errorDetails = $response->json() ?? 'No details available';
                Log::error('[Translation] Gemini API error: ' . json_encode($errorDetails));
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call failed: ' . $e->getMessage());
        }
        
        return null;
    }




    
    /**
     * Extract JSON object from AI response text
     *
     * @param string $response
     * @return array|null
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
     * Livewire method to translate a question (and its choices/likert) and store the result.
     *
     * @param int $questionId
     * @param string $language
     */
    public function translateQuestion($questionId = null, $language = null)
    {
        set_time_limit(60);
        
        // Handle array parameter format
        if (is_array($questionId)) {
            $params = $questionId;
            $questionId = $params['questionId'] ?? null;
            $language = $params['language'] ?? null;
        }
        
        // Exit if missing required parameters
        if (!$questionId || !$language) return;
        
        // Set loading states
        $this->translatingQuestions[$questionId] = true;
        $this->isLoading = true;
        $this->dispatch('$refresh');
        
        // Find the question in survey pages
        $question = null;
        foreach ($this->survey->pages as $page) {
            $foundQuestion = $page->questions->firstWhere('id', $questionId);
            if ($foundQuestion) {
                $question = $foundQuestion;
                break;
            }
        }
        
        // Exit if question not found
        if (!$question) {
            $this->translatingQuestions[$questionId] = false;
            $this->isLoading = false;
            $this->dispatch('$refresh');
            return;
        }
        
        // Prepare data for translation
        $content = ['question' => $question->question_text];
        
        // Add choices if applicable
        if (in_array($question->question_type, ['multiple_choice', 'radio']) && $question->choices->count() > 0) {
            $content['choices'] = $question->choices->pluck('choice_text')->values()->toArray();
        }
        
        // Add likert data if applicable
        if ($question->question_type === 'likert') {
            $content['likert_rows'] = array_values($this->getLikertRows($question));
            $content['likert_columns'] = array_values(is_array($question->likert_columns) 
                ? $question->likert_columns 
                : (json_decode($question->likert_columns, true) ?: []));
        }
        
        // Map language codes to full language names
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
        
        // Skip translation if target is English
        if ($language === 'en' || $targetLanguage === 'English') {
            $this->translatedQuestions[$questionId] = null;
            $this->translatedChoices[$questionId] = null;
            $this->translatingQuestions[$questionId] = false;
            $this->isLoading = false;
            $this->dispatch('$refresh');
            return;
        }
        
        // Try translation with Gemini first, then DeepSeek as fallback
        $result = $this->translateWithAI($content, $targetLanguage, 'gemini');
        if (!$result) {
            $result = $this->translateWithAI($content, $targetLanguage, 'deepseek');
        }
        
        if ($result) {
            // Store translated question text
            $this->translatedQuestions[$questionId] = $result['question'] ?? null;
            
            // Store translated choices if applicable
            if (isset($content['choices']) && !empty($content['choices']) && 
                isset($result['choices']) && is_array($result['choices'])) {
                
                $translatedChoices = [];
                foreach ($question->choices as $idx => $choice) {
                    $translatedChoices[$choice->id] = $result['choices'][$idx] ?? $choice->choice_text;
                }
                $this->translatedChoices[$questionId] = $translatedChoices;
            }
            
            // Store translated likert data if applicable
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
            // Mark translation as failed
            $this->translatedQuestions[$questionId] = $question->question_text . ' (Translation failed)';
            $this->translatedChoices[$questionId] = null;
        }
        
        // Add a short delay before allowing the next translation
        sleep(1);
        
        // Reset loading states
        $this->translatingQuestions[$questionId] = false;
        $this->isLoading = false;
        $this->dispatch('$refresh');
    }














    /**
     * Revert translation for a question
     */
    public function revertTranslation($questionId)
    {
        $this->translatedQuestions[$questionId] = null;
        $this->translatedChoices[$questionId] = null;
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
