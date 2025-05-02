<?php

namespace App\Livewire\Surveys\AnswerSurvey;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use Illuminate\Support\Facades\Auth;

class AnswerSurvey extends Component
{
    public Survey $survey;
    public $answers = [];
    public $currentPage = 0;
    public $navAction = 'submit'; // 'next' or 'submit'

    public function mount(Survey $survey)
    {
        $this->survey = $survey->load('pages.questions.choices');
        $this->currentPage = 0;
    }

    public function submit()
    {
        $rules = ['answers' => 'array'];
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
        }

        $this->validate($rules, $messages);

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

            if ($question->question_type === 'multiple_choice' && is_array($answer)) {
                foreach ($answer as $choiceId => $checked) {
                    if ($checked) {
                        $choice = $question->choices->firstWhere('id', $choiceId);
                        Answer::create([
                            'response_id' => $response->id,
                            'survey_question_id' => $questionId,
                            'answer' => $choice ? $choice->choice_text : '',
                        ]);
                    }
                }
            } elseif ($question && $question->question_type === 'likert' && is_array($answer)) {
                $answer = json_encode($answer);

                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'answer' => $answer,
                ]);
            } elseif ($question && $question->question_type === 'rating') {
                $ratingValue = is_numeric($answer) ? intval($answer) : null;
                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'answer' => $ratingValue,
                ]);
            } else {
                // For radio and other question types
                if (in_array($question->question_type, ['radio'])) {
                    $choice = $question->choices->firstWhere('id', $answer);
                    $answerValue = $choice ? $choice->choice_text : $answer;
                } else {
                    $answerValue = $answer;
                }
                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionId,
                    'answer' => $answerValue,
                ]);
            }
        }

        // Update survey status if needed
        if ($this->survey->status === 'published') {
            $this->survey->status = 'ongoing';
            $this->survey->save();
        }

        session()->flash('success', 'Survey submitted!');
        return redirect()->route('feed.index');
    }

    public function render()
    {
        return view('livewire.surveys.answer-survey.answer-survey', [
            'survey' => $this->survey,
            'answers' => $this->answers,
        ]);
    }
}
