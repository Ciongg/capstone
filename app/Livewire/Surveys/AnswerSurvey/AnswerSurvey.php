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
            // Validate only the current page
            $this->validate($this->getValidationRules(), $this->getValidationMessages());

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
     * Render the component
     */
    public function render()
    {
        return view('livewire.surveys.answer-survey.answer-survey', [
            'survey' => $this->survey,
            'answers' => $this->answers,
        ]);
    }
}
