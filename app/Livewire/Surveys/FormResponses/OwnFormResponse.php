<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use App\Models\User;

class OwnFormResponse extends Component
{
    public Survey $survey;
    public ?Response $response = null;
    public ?User $respondentUser = null;
    public int $trustScore = 0;
    public ?string $timeCompleted = null;
    public array $matchedSurveyTagsInfo = [];
    public array $pagesWithProcessedAnswers = [];

    public function mount($surveyId, $responseId)
    {
        // Load survey with all related data needed for displaying responses
        $this->survey = Survey::with([
            'pages.questions.choices',
            'tags'
        ])->findOrFail($surveyId);

        // Load the specific response
        $this->response = Response::with([
            'user.tags',
            'answers'
        ])->findOrFail($responseId);

        // Ensure user can only view their own response or admin can view any
        if (!auth()->user()->isSuperAdmin() && $this->response->user_id !== auth()->id()) {
            abort(403, 'You can only view your own responses.');
        }

        $this->processResponseDetails();
    }

    public function processResponseDetails()
    {
        if (!$this->response) {
            return;
        }

        $this->respondentUser = $this->response->user;

        // Process user information if available
        if ($this->respondentUser) {
            // Get trust score
            $this->trustScore = $this->respondentUser->trust_score ?? 0;

            // Process demographic matching information
            $this->matchedSurveyTagsInfo = [];
            $respondentTagIds = $this->respondentUser->tags->pluck('id')->toArray();
            $surveyTags = $this->survey->tags;
            $matchedCount = 0;
            
            // Find which tags match between user and survey
            foreach ($surveyTags as $surveyTag) {
                if (in_array($surveyTag->id, $respondentTagIds)) {
                    $this->matchedSurveyTagsInfo[] = ['name' => $surveyTag->name, 'matched' => true];
                    $matchedCount++;
                }
            }
            
            // Set overall match status for display purposes
            if ($surveyTags->isNotEmpty() && $matchedCount === 0) {
                 $this->matchedSurveyTagsInfo['status'] = 'none_matched';
            } elseif ($surveyTags->isEmpty()) {
                 $this->matchedSurveyTagsInfo['status'] = 'no_target_demographics';
            } else {
                 $this->matchedSurveyTagsInfo['status'] = 'has_matches';
            }
        } else {
            // Default values when no user data available
            $this->trustScore = 0;
            $this->matchedSurveyTagsInfo = ['status' => 'no_user_data'];
        }

        // Set default time completed to 0
        $this->timeCompleted = "0";

        // Process answers for each question in the survey
        $this->pagesWithProcessedAnswers = [];
        foreach ($this->survey->pages as $page) {
            $processedQuestions = [];
            
            foreach ($page->questions->sortBy('order') as $question) {
                // Initialize question data structure
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

                // Get answers for this question from the response
                $questionAnswer = $this->response->answers->where('survey_question_id', $question->id)->first();
                $answerDataString = $questionAnswer?->answer;
                $decodedAnswer = $answerDataString ? json_decode($answerDataString, true) : [];

                // Process based on question type
                if (in_array($question->question_type, ['multiple_choice', 'radio'])) {
                    // For choice-based questions, mark selected options
                    $otherChoiceId = null;
                    $otherSelected = false;
                    
                    foreach ($question->choices as $choice) {
                        $isSelected = false;
                        if ($decodedAnswer !== null) {
                            if ($question->question_type === 'multiple_choice' && is_array($decodedAnswer) && in_array($choice->id, $decodedAnswer)) {
                                $isSelected = true;
                                
                                if ($choice->is_other) {
                                    $otherSelected = true;
                                    $otherChoiceId = $choice->id;
                                }
                            } 
                            elseif ($question->question_type === 'radio' && !is_array($decodedAnswer) && (int)$decodedAnswer === $choice->id) {
                                $isSelected = true;
                                
                                if ($choice->is_other) {
                                    $otherSelected = true;
                                    $otherChoiceId = $choice->id;
                                }
                            }
                        }
                        
                        $questionData['choices'][] = [
                            'id' => $choice->id,
                            'choice_text' => $choice->choice_text,
                            'is_selected' => $isSelected,
                            'is_other' => $choice->is_other ? 1 : 0,
                        ];
                    }
                    
                    // Add the "other_text" value if an "other" option was selected
                    if ($otherSelected) {
                        if ($questionAnswer && !empty($questionAnswer->other_text)) {
                            $questionData['other_text'] = $questionAnswer->other_text;
                        } 
                        else {
                            $otherTextAnswer = $this->response->answers
                                ->where('survey_question_id', $question->id)
                                ->whereNotNull('other_text')
                                ->first();
                                
                            if ($otherTextAnswer && !empty($otherTextAnswer->other_text)) {
                                $questionData['other_text'] = $otherTextAnswer->other_text;
                            }
                            elseif ($questionAnswer && isset($questionAnswer->metadata)) {
                                $metadata = json_decode($questionAnswer->metadata, true);
                                $questionData['other_text'] = $metadata['other_text'] ?? null;
                            }
                        }
                        
                        if (empty($questionData['other_text']) && $questionAnswer) {
                            if ($question->question_type === 'multiple_choice') {
                                foreach ($this->response->answers as $answer) {
                                    if ($answer->survey_question_id === $question->id && !empty($answer->other_text)) {
                                        $questionData['other_text'] = $answer->other_text;
                                        break;
                                    }
                                }
                            }
                            elseif ($question->question_type === 'radio' && $otherChoiceId) {
                                $questionData['other_text'] = $questionAnswer->other_text;
                            }
                        }
                    } else {
                        $questionData['other_text'] = null;
                    }
                } elseif ($question->question_type === 'likert') {
                    $questionData['likert_columns'] = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
                    $questionData['likert_rows'] = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                    $questionData['likert_answer_data'] = $decodedAnswer ?: [];
                } elseif ($question->question_type === 'rating') {
                    $questionData['single_answer'] = $answerDataString ?? '0'; 
                } else { 
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
        return view('livewire.surveys.form-responses.own-form-response');
    }
}
