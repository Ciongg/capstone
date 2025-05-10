<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response; // Added
use Carbon\CarbonInterface; // Added

class IndividualResponses extends Component
{
    public Survey $survey;
    public int $current = 0; // index of the current respondent

    // Properties to hold processed data for the view
    public ?\App\Models\Response $currentRespondent = null;
    public ?\App\Models\User $respondentUser = null;
    public int $trustScore = 0;
    public ?string $timeCompleted = null;
    public array $matchedSurveyTagsInfo = []; // For demographics box
    public array $pagesWithProcessedAnswers = [];

    public function mount($surveyId)
    {
        $this->survey = Survey::with([
            'responses.user.tags', // Eager load user and their tags for all responses
            'responses.answers',
            'pages.questions.choices',
            'tags' // Eager load survey tags
        ])->findOrFail($surveyId);

        $this->processCurrentResponseDetails();
    }

    public function updatedCurrent()
    {
        $this->processCurrentResponseDetails();
    }

    public function processCurrentResponseDetails(): void
    {
        if ($this->survey->responses->isEmpty() || !isset($this->survey->responses[$this->current])) {
            $this->currentRespondent = null;
            $this->respondentUser = null;
            $this->trustScore = 0;
            $this->timeCompleted = null;
            $this->matchedSurveyTagsInfo = [];
            $this->pagesWithProcessedAnswers = [];
            return;
        }

        $this->currentRespondent = $this->survey->responses[$this->current];
        $this->respondentUser = $this->currentRespondent->user;

        // Respondent User Info
        if ($this->respondentUser) {
            $this->trustScore = $this->respondentUser->trust_score ?? 0;

            // Matched Demographics
            $this->matchedSurveyTagsInfo = [];
            $respondentTagIds = $this->respondentUser->tags->pluck('id')->toArray();
            $surveyTags = $this->survey->tags;
            $matchedCount = 0;
            foreach ($surveyTags as $surveyTag) {
                if (in_array($surveyTag->id, $respondentTagIds)) {
                    $this->matchedSurveyTagsInfo[] = ['name' => $surveyTag->name, 'matched' => true];
                    $matchedCount++;
                }
            }
            if ($surveyTags->isNotEmpty() && $matchedCount === 0) {
                 $this->matchedSurveyTagsInfo['status'] = 'none_matched';
            } elseif ($surveyTags->isEmpty()) {
                 $this->matchedSurveyTagsInfo['status'] = 'no_target_demographics';
            } else {
                 $this->matchedSurveyTagsInfo['status'] = 'has_matches';
            }

        } else {
            $this->trustScore = 0;
            $this->matchedSurveyTagsInfo = ['status' => 'no_user_data'];
        }

        // Time Completed
        if ($this->currentRespondent->created_at && $this->currentRespondent->updated_at) {
            $this->timeCompleted = $this->currentRespondent->updated_at->diffForHumans(
                $this->currentRespondent->created_at,
                ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]
            );
        } else {
            $this->timeCompleted = null;
        }

        // Process Answers for each question
        $this->pagesWithProcessedAnswers = [];
        foreach ($this->survey->pages as $page) {
            $processedQuestions = [];
            foreach ($page->questions->sortBy('order') as $question) {
                $questionData = [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'order' => $question->order,
                    'choices' => [],
                    'likert_columns' => [],
                    'likert_rows' => [],
                    'likert_answer_data' => [],
                    'single_answer' => 'No answer',
                    'stars' => $question->stars ?? 5,
                ];

                $questionAnswers = $this->currentRespondent->answers->where('survey_question_id', $question->id);
                $answerDataString = $questionAnswers->first() ? $questionAnswers->first()->answer : null;
                $decodedAnswer = $answerDataString ? json_decode($answerDataString, true) : null;

                if (in_array($question->question_type, ['multiple_choice', 'radio'])) {
                    foreach ($question->choices as $choice) {
                        $isSelected = false;
                        if ($decodedAnswer !== null) {
                            if ($question->question_type === 'multiple_choice' && is_array($decodedAnswer) && in_array($choice->id, $decodedAnswer)) {
                                $isSelected = true;
                            } elseif ($question->question_type === 'radio' && !is_array($decodedAnswer) && (int)$decodedAnswer === $choice->id) {
                                $isSelected = true;
                            }
                        }
                        $questionData['choices'][] = [
                            'id' => $choice->id,
                            'choice_text' => $choice->choice_text,
                            'is_selected' => $isSelected,
                        ];
                    }
                } elseif ($question->question_type === 'likert') {
                    $questionData['likert_columns'] = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
                    $questionData['likert_rows'] = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                    $questionData['likert_answer_data'] = $decodedAnswer ?: [];
                } elseif ($question->question_type === 'rating') {
                    $questionData['single_answer'] = $answerDataString ?? '0'; // Default to 0 for rating if no answer
                } else { // essay, short_text, date
                    $questionData['single_answer'] = $answerDataString ?? 'No answer';
                }
                $processedQuestions[] = $questionData;
            }
            $this->pagesWithProcessedAnswers[] = [
                'id' => $page->id,
                'title' => $page->title,
                'subtitle' => $page->subtitle,
                'questions' => $processedQuestions,
            ];
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-responses.individual-responses', [
            'survey' => $this->survey, // Still pass survey for general info like total responses
            // Other properties are now public and directly accessible in Blade
        ]);
    }
}
