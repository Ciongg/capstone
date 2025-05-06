<?php

namespace App\Livewire\Surveys\AnswerSurvey;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Optional: For debugging
use Illuminate\Contracts\View\View; // Import View contract

class AnswerSurvey extends Component
{
    public Survey $survey;
    public $answers = [];
    public $otherTexts = []; // Add property to store 'Other' text inputs
    public $currentPage = 0;
    public $navAction = 'submit'; // 'next' or 'submit'
    public $isPreview = false; // Flag for preview mode

    public function mount(Survey $survey, $isPreview = false)
    {
        // Load choices with is_other
        $this->survey = $survey->load(['pages.questions' => function ($q) {
            $q->with(['choices' => function ($c) {
                $c->orderBy('order'); // Ensure choices are ordered
            }])->orderBy('order');
        }]);
        $this->isPreview = (bool) $isPreview;

        // Check survey status only if not in preview mode
        if (!$this->isPreview && !in_array($this->survey->status, ['published', 'ongoing'])) {
            abort(404, 'Survey not available.');
        }

        $this->currentPage = 0;

        // Initialize answers structure
        foreach ($this->survey->pages as $page) {
            foreach ($page->questions as $question) {
                if ($question->question_type === 'multiple_choice') {
                    $this->answers[$question->id] = [];
                    $this->otherTexts[$question->id] = null; // Initialize other text for MC
                    foreach ($question->choices as $choice) {
                        $this->answers[$question->id][$choice->id] = false;
                    }
                } elseif ($question->question_type === 'radio') {
                    $this->answers[$question->id] = null;
                    $this->otherTexts[$question->id] = null; // Initialize other text for Radio
                } elseif ($question->question_type === 'likert') {
                    $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                    $this->answers[$question->id] = array_fill(0, count($likertRows), null);
                } else {
                    $this->answers[$question->id] = null;
                }
            }
        }
    }

    public function updatedAnswers($value, $key)
    {
        // $key will be like "12.5" for question_id 12, choice_id 5
        [$questionId, $choiceId] = explode('.', $key) + [null, null];

        // Find the question
        $question = $this->survey->pages
            ->flatMap->questions
            ->firstWhere('id', $questionId);

        if ($question && $question->question_type === 'multiple_choice' && $question->limit_answers && $question->max_answers) {
            // Count checked choices
            $checked = collect($this->answers[$questionId] ?? [])->filter()->count();
            if ($checked > $question->max_answers) {
                // Uncheck the last one (the one just checked)
                $this->answers[$questionId][$choiceId] = false;
                $this->addError('answers.' . $questionId, "You can only select up to {$question->max_answers} options.");
            } else {
                $this->resetErrorBag('answers.' . $questionId);
            }
        }
    }

    public function submit()
    {
        $rules = ['answers' => 'array', 'otherTexts' => 'array']; // Add otherTexts to rules
        $messages = [];

        // Only validate questions on the current page if Next, otherwise all
        $pages = $this->survey->pages;
        $questions = ($this->navAction === 'next')
            ? $pages[$this->currentPage]->questions
            : $pages->flatMap->questions;

        foreach ($questions as $question) {
            if ($question->required) {
                if ($question->question_type === 'multiple_choice') {
                    $rules["answers.{$question->id}"] = 'required|array|min:1';
                    $messages["answers.{$question->id}.required"] = 'This question is required.';
                } elseif ($question->question_type === 'likert') {
                    // Custom validation for likert: all rows must have a value
                    $rules["answers.{$question->id}"] = [
                        'required', 'array',
                        function ($attribute, $value, $fail) use ($question) {
                            $likertRows = is_array($question->likert_rows)
                                ? $question->likert_rows
                                : (json_decode($question->likert_rows, true) ?: []);
                            foreach ($likertRows as $rowIndex => $row) {
                                if (!isset($value[$rowIndex]) || $value[$rowIndex] === null || $value[$rowIndex] === '') {
                                    $fail('All rows in this question must be answered.');
                                }
                            }
                        }
                    ];
                    $messages["answers.{$question->id}.required"] = 'This question is required.';
                } else {
                    $rules["answers.{$question->id}"] = 'required';
                    $messages["answers.{$question->id}.required"] = 'This question is required.';
                }
            }

            // Add validation for 'Other' text input
            if (in_array($question->question_type, ['multiple_choice', 'radio'])) {
                $otherChoice = $question->choices->firstWhere('is_other', true);
                if ($otherChoice) {
                    $otherChoiceId = $otherChoice->id;
                    // Rule: If 'Other' is selected, the corresponding text input is required.
                    $ruleKey = "otherTexts.{$question->id}";

                    if ($question->question_type === 'multiple_choice') {
                        // Check if the 'Other' checkbox is checked
                        $rules[$ruleKey] = "required_if:answers.{$question->id}.{$otherChoiceId},true|nullable|string|max:255";
                    } elseif ($question->question_type === 'radio') {
                        // Check if the 'Other' radio button's value is selected
                        $rules[$ruleKey] = "required_if:answers.{$question->id},{$otherChoiceId}|nullable|string|max:255";
                    }
                    $messages["{$ruleKey}.required_if"] = 'Please specify your answer for "Other".';
                }
            }
        }

        $this->validate($rules, $messages);

        // Handle Preview Mode Submission
        if ($this->isPreview && $this->navAction === 'submit') {
            session()->flash('success', 'Survey preview completed successfully!');
            return redirect()->route('surveys.create', $this->survey->id);
        }

        // If Next, just advance page
        if ($this->navAction === 'next') {
            $this->currentPage++;
            $this->navAction = 'submit';
            return;
        }

        // Create a new response
        $response = Response::create([
            'survey_id' => $this->survey->id,
            'user_id' => Auth::id(),
        ]);

        // Save answers
        foreach ($this->answers as $questionId => $answer) {
            $question = $this->survey->pages->flatMap->questions->firstWhere('id', $questionId);
            $otherChoice = $question->choices->firstWhere('is_other', true); // Find 'Other' choice if exists

            // Skip saving if the answer is effectively empty (null or empty array/string)
            if ($answer === null || $answer === '' || (is_array($answer) && empty(array_filter($answer, fn($v) => $v !== null && $v !== '')))) {
                continue;
            }

            if ($question->question_type === 'multiple_choice' && is_array($answer)) {
                $hasAnswer = false;
                foreach ($answer as $choiceId => $checked) {
                    if ($checked) {
                        $choice = $question->choices->firstWhere('id', $choiceId);
                        if ($choice) {
                            $answerText = $choice->choice_text;
                            // If this is the 'Other' choice, use the text from otherTexts
                            if ($choice->is_other) {
                                $answerText = $this->otherTexts[$questionId] ?? 'Other'; // Use provided text or default
                            }
                            Answer::create([
                                'response_id' => $response->id,
                                'survey_question_id' => $questionId,
                                'answer' => $answerText, // Save choice text or 'Other' text
                            ]);
                            $hasAnswer = true;
                        }
                    }
                }
                if (!$hasAnswer) continue;

            } elseif ($question && $question->question_type === 'radio') {
                $choice = $question->choices->firstWhere('id', $answer);
                if ($choice) { // Ensure a choice was actually selected
                    $answerValue = $choice->choice_text;
                    // If this is the 'Other' choice, use the text from otherTexts
                    if ($choice->is_other) {
                        $answerValue = $this->otherTexts[$questionId] ?? 'Other'; // Use provided text or default
                    }
                    Answer::create([
                        'response_id' => $response->id,
                        'survey_question_id' => $questionId,
                        'answer' => $answerValue, // Save choice text or 'Other' text
                    ]);
                } else {
                    continue; // Skip if no valid choice ID was found for radio
                }

            } elseif ($question && $question->question_type === 'likert' && is_array($answer)) {
                if (empty(array_filter($answer, fn($v) => $v !== null && $v !== ''))) {
                    continue;
                }
                $encodedAnswer = json_encode($answer);
                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'answer' => $encodedAnswer,
                ]);
            } elseif ($question && $question->question_type === 'rating') {
                $ratingValue = is_numeric($answer) ? intval($answer) : null;
                if ($ratingValue === null) {
                    continue;
                }
                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'answer' => $ratingValue,
                ]);
            } else {
                $answerValue = $answer;

                if (in_array($question->question_type, ['radio'])) {
                    $choice = $question->choices->firstWhere('id', $answer);
                    $answerValue = $choice ? $choice->choice_text : $answer;
                }

                if ($answerValue === null || $answerValue === '') {
                    continue;
                }

                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'answer' => $answerValue,
                ]);
            }
        }

        // Update survey status if needed
        if (!$this->isPreview && $this->survey->status === 'published') {
            $this->survey->status = 'ongoing';
            $this->survey->save();
        }

        // Award points to the user
        if (!$this->isPreview) {
            $user = Auth::user();
            if ($user && $this->survey->points_allocated) {
                $userModel = User::find($user->id);
                if ($userModel) {
                    $userModel->points = ($userModel->points ?? 0) + $this->survey->points_allocated;
                    $userModel->save();
                }
            }
            session()->flash('success', 'Survey submitted!');
            return redirect()->route('feed.index');
        }
    }

    public function render()
    {
        return view('livewire.surveys.answer-survey.answer-survey', [
            'survey' => $this->survey,
            'answers' => $this->answers,
        ]);
    }
}
