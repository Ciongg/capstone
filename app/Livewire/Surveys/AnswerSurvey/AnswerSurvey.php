<?php

namespace App\Livewire\Surveys\AnswerSurvey;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Added DB facade import
use App\Models\SurveyQuestion; // Added SurveyQuestion import
use Illuminate\Validation\Rule; // <-- Add this import
use Illuminate\Support\Facades\Log; // Import Log facade

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
        $this->survey = $survey->load([
            'pages' => function ($query) {
                $query->orderBy('order') // Order pages
                      ->with(['questions' => function ($qQuery) {
                          $qQuery->orderBy('order') // Order questions within pages
                                 ->with(['choices' => function ($cQuery) {
                                     $cQuery->orderBy('order'); // Order choices within questions
                                 }]);
                      }]);
            }
        ]);
        $this->isPreview = (bool) $isPreview;

        if (!$this->isPreview && !in_array($this->survey->status, ['published', 'ongoing'])) {
            abort(404, 'Survey not available.');
        }

        $this->currentPage = 0;

        $this->initializeAnswers();
    }

    protected function initializeAnswers()
    {
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

    protected function getValidationRules()
    {
        $rules = [];
        if (!$this->survey->pages->has($this->currentPage)) {
            return $rules;
        }
        $currentPageQuestions = $this->survey->pages[$this->currentPage]->questions;

        foreach ($currentPageQuestions as $question) {
            $rules = array_merge($rules, $this->getRulesForQuestion($question));
        }
        return $rules;
    }

    protected function getValidationMessages()
    {
        $messages = [];
        if (!$this->survey->pages->has($this->currentPage)) {
            return $messages;
        }
        $currentPageQuestions = $this->survey->pages[$this->currentPage]->questions;
        $questionNumberOffset = 0;
        for ($i = 0; $i < $this->currentPage; $i++) {
            if ($this->survey->pages->has($i)) {
                $questionNumberOffset += $this->survey->pages[$i]->questions->count();
            }
        }

        foreach ($currentPageQuestions as $index => $question) {
            $qNum = $questionNumberOffset + $index + 1;
            $messages = array_merge($messages, $this->getMessagesForQuestion($question, $qNum));
        }
        return $messages;
    }

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

    protected function getAllValidationMessages()
    {
        $messages = [];
        $questionNumberOffset = 0;
        foreach ($this->survey->pages as $pageIndex => $page) {
            foreach ($page->questions as $index => $question) {
                $qNum = $questionNumberOffset + $index + 1;
                $messages = array_merge($messages, $this->getMessagesForQuestion($question, $qNum));
            }
            $questionNumberOffset += $page->questions->count();
        }
        return $messages;
    }

    protected function getRulesForQuestion(SurveyQuestion $question)
    {
        $rules = [];
        $questionId = $question->id;
        $isRequired = $question->required;

        if ($question->question_type === 'multiple_choice') {
            $rules['answers.' . $questionId] = [
                'array', // Just ensure it's an array
                function ($attribute, $value, $fail) use ($question, $isRequired) {
                    $selectedCount = collect($value ?? [])->filter(fn($v) => $v === true)->count();

                    // 1. Check minimum required ONLY if question is required
                    if ($isRequired && $selectedCount < 1) {
                        $fail("Please select at least one option.");
                        return; // Stop further checks if minimum not met
                    }

                    // 2. Check limit condition if set
                    $limitCondition = $question->limit_condition;
                    $maxAnswers = $question->max_answers;

                    if ($limitCondition && $maxAnswers > 0) {
                        if ($limitCondition === 'equal_to') {
                            if ($selectedCount != $maxAnswers) {
                                if ($isRequired || $selectedCount > 0) {
                                     $fail("Please select exactly {$maxAnswers} options.");
                                }
                            }
                        } elseif ($limitCondition === 'at_most') {
                            if ($selectedCount > $maxAnswers) {
                                $fail("Please select no more than {$maxAnswers} options.");
                            }
                        }
                    }
                }
            ];
            $otherChoice = $question->choices->firstWhere('is_other', true);
            if ($otherChoice) {
                $rules['otherTexts.' . $questionId] = [
                    Rule::requiredIf(function () use ($questionId, $otherChoice) {
                        return isset($this->answers[$questionId][$otherChoice->id]) && $this->answers[$questionId][$otherChoice->id] === true;
                    }),
                    'nullable', 'string', 'max:255'
                ];
            }
            $rules['answers.' . $questionId . '.*'] = ['boolean'];

        } elseif ($isRequired) {
            if ($question->question_type === 'radio') {
                $rules['answers.' . $questionId] = ['required'];
                $otherChoice = $question->choices->firstWhere('is_other', true);
                if ($otherChoice) {
                    $rules['otherTexts.' . $questionId] = [
                        Rule::requiredIf(fn() => ($this->answers[$questionId] ?? null) == $otherChoice->id),
                        'nullable', 'string', 'max:255'
                    ];
                }
            } elseif ($question->question_type === 'likert') {
                $rules['answers.' . $questionId] = ['required', 'array'];
                $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                foreach (array_keys($likertRows) as $rowIndex) {
                    $rules['answers.' . $questionId . '.' . $rowIndex] = ['required'];
                }
            } else {
                $rules['answers.' . $questionId] = ['required'];
            }
        } else {
            if ($question->question_type === 'radio') {
                $rules['answers.' . $questionId] = ['nullable'];
                $otherChoice = $question->choices->firstWhere('is_other', true);
                if ($otherChoice) {
                    $rules['otherTexts.' . $questionId] = [
                        Rule::requiredIf(fn() => ($this->answers[$questionId] ?? null) == $otherChoice->id),
                        'nullable', 'string', 'max:255'
                    ];
                }
            } elseif ($question->question_type === 'likert') {
                $rules['answers.' . $questionId] = ['nullable', 'array'];
                $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                foreach (array_keys($likertRows) as $rowIndex) {
                    $rules['answers.' . $questionId . '.' . $rowIndex] = ['nullable'];
                }
            } else {
                $rules['answers.' . $questionId] = ['nullable'];
            }
        }
        return $rules;
    }

    protected function getMessagesForQuestion(SurveyQuestion $question, int $qNum)
    {
        $messages = [];
        $questionId = $question->id;

        // Only add the general '.required' message if it's NOT a multiple choice or likert question
        // The closure handles MC, and we add a specific Likert message below.
        if (!in_array($question->question_type, ['multiple_choice', 'likert'])) {
            $messages['answers.' . $questionId . '.required'] = "Question {$qNum} is required.";
        }

        // Keep Likert specific messages
        if ($question->question_type === 'likert') {
            // Add message for the entire Likert block being required
            $messages['answers.' . $questionId . '.required'] = "Please answer all parts of question {$qNum}.";

            // Keep messages for individual rows
            $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
            foreach (array_keys($likertRows) as $rowIndex) {
                $rowText = $likertRows[$rowIndex] ?? 'Row ' . ($rowIndex + 1);
                $messages['answers.' . $questionId . '.' . $rowIndex . '.required'] = "Please select an option for '{$rowText}' in question {$qNum}.";
            }
        }

        // Keep 'Other' text required message
        $messages['otherTexts.' . $questionId . '.required'] = "Please specify your 'Other' answer for question {$qNum}.";

        return $messages;
    }

    public function submit()
    {
        if ($this->navAction === 'next') {
            $this->validate($this->getValidationRules(), $this->getValidationMessages());

            if ($this->currentPage < $this->survey->pages->count() - 1) {
                $this->currentPage++;
                $this->dispatch('pageChanged');
            } else {
                $this->navAction = 'submit';
                $this->submit();
            }
        } elseif ($this->navAction === 'submit') {
            $this->validate($this->getAllValidationRules(), $this->getAllValidationMessages());

            if ($this->isPreview) {
                session()->flash('success', 'Preview submitted successfully! (No data saved)');
                return redirect()->route('surveys.create', ['survey' => $this->survey->id]);
            }

            DB::transaction(function () {
                $user = Auth::user();

                $response = Response::create([
                    'survey_id' => $this->survey->id,
                    'user_id' => $user?->id,
                ]);

                foreach ($this->answers as $questionId => $answerValue) {
                    $question = SurveyQuestion::find($questionId);
                    if (!$question) continue;

                    $answerData = [
                        'response_id' => $response->id,
                        'survey_question_id' => $questionId,
                        'answer' => null,
                        'other_text' => null,
                    ];

                    if ($question->question_type === 'multiple_choice') {
                        $selectedChoiceIds = collect($answerValue)->filter(fn($v) => $v === true)->keys()->toArray();
                        if (!empty($selectedChoiceIds)) {
                            $answerData['answer'] = json_encode($selectedChoiceIds);
                            $otherChoice = $question->choices->firstWhere('is_other', true);
                            if ($otherChoice && in_array($otherChoice->id, $selectedChoiceIds)) {
                                $answerData['other_text'] = $this->otherTexts[$questionId] ?? null;
                            }
                            Answer::create($answerData);
                        }
                    } elseif ($question->question_type === 'radio') {
                        if ($answerValue !== null) {
                            $answerData['answer'] = $answerValue;
                            $otherChoice = $question->choices->firstWhere('is_other', true);
                            if ($otherChoice && $answerValue == $otherChoice->id) {
                                $answerData['other_text'] = $this->otherTexts[$questionId] ?? null;
                            }
                            Answer::create($answerData);
                        }
                    } elseif ($question->question_type === 'likert') {
                        $filteredLikert = array_filter($answerValue ?? [], fn($v) => $v !== null);
                        if (!empty($filteredLikert)) {
                            $answerData['answer'] = json_encode($answerValue);
                            Answer::create($answerData);
                        }
                    } else {
                        // For text, essay, date inputs, etc.
                        if ($answerValue !== null && $answerValue !== '') {
                            $answerData['answer'] = $answerValue;
                            Answer::create($answerData);
                        } elseif (!$question->required) {
                            // For non-required questions with empty answers, use an empty string instead of null
                            $answerData['answer'] = '-';
                            Answer::create($answerData);
                        }
                    }
                }

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

            session()->flash('success', 'Survey submitted successfully!');
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
