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

    public function mount(Survey $survey)
    {
        $this->survey = $survey->load('pages.questions.choices');
    }

    public function submit()
    {
        $this->validate([
            'answers' => 'required|array',
        ]);

        $response = Response::create([
            'survey_id' => $this->survey->id,
            'user_id' => Auth::id(),
        ]);

        foreach ($this->answers as $questionId => $answer) {
            $question = $this->survey->pages->flatMap->questions->firstWhere('id', $questionId);

            // For multiple choice, $answer is an array of [choiceId => bool]
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
            } else {
                // For radio and others
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

        session()->flash('success', 'Survey submitted!');
        return redirect()->route('feed.index');
    }

    public function render()
    {
        return view('livewire.surveys.answer-survey.answer-survey');
    }
}
