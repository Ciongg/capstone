<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response;
use Carbon\CarbonInterface;
use App\Models\User;

class IndividualResponses extends Component
{
    public Survey $survey;
    public int $current = 0;
    public ?Response $currentRespondent = null;
    public ?User $respondentUser = null;
    public float $trustScore = 0; // Changed from int to float to handle decimals
    public ?string $timeCompleted = null;
    public array $matchedSurveyTagsInfo = [];
    public array $pagesWithProcessedAnswers = [];
    
    // Add properties for Go To Respondent feature
    public $gotoRespondent = '';
    public $gotoError = '';

    protected $listeners = ['responseReported' => 'handleResponseReported'];

    public function mount($surveyId)
    {
        // Load survey with all related data needed for displaying responses
        $this->survey = Survey::with([
            'responses.user',
            'responses.snapshot', // Add the snapshot relationship
            'responses.answers',
            'pages.questions.choices',
            'tags'
        ])->findOrFail($surveyId);
        
        $this->processCurrentResponseDetails();
    }

    public function updatedCurrent()
    {
        $this->processCurrentResponseDetails();
        
        // Refresh the survey responses to get updated reported status
        $this->survey->load('responses.user.tags');
    }

    public function processCurrentResponseDetails()
    {
        // Reset all values if no responses exist or index is invalid
        if ($this->survey->responses->isEmpty() || $this->current < 0 || $this->current >= $this->survey->responses->count()) {
            $this->currentRespondent = null;
            $this->respondentUser = null;
            $this->trustScore = 0;
            $this->timeCompleted = "0";
            $this->matchedSurveyTagsInfo = [];
            $this->pagesWithProcessedAnswers = [];
            return;
        }

        // Get the current response by index from the collection
        $responses = $this->survey->responses->values(); // Reset keys to ensure proper indexing
        $this->currentRespondent = $responses[$this->current];
        
        // Refresh the current respondent to get latest data including reported status
        $this->currentRespondent->refresh();
        
        $this->respondentUser = $this->currentRespondent->user;

        // Always get trust score from live user data when available
        if ($this->respondentUser) {
            // Set trust score from current user data (live)
            $this->trustScore = $this->respondentUser->trust_score ?? 0;
        } else {
            // Default to 0 if no user associated
            $this->trustScore = 0;
        }

        // Process snapshot information for other data if available
        $snapshot = $this->currentRespondent->snapshot;
        
        if ($snapshot) {
            // Get completion time from snapshot
            if ($snapshot->completion_time_seconds) {
                $minutes = floor($snapshot->completion_time_seconds / 60);
                $seconds = $snapshot->completion_time_seconds % 60;
                $this->timeCompleted = sprintf("%d:%02d", $minutes, $seconds);
            } else {
                $this->timeCompleted = "0:00";
            }

            // Process demographic matching information from snapshot
            $this->matchedSurveyTagsInfo = [];
            $demographicTags = json_decode($snapshot->demographic_tags, true) ?? [];
            $surveyTags = $this->survey->tags; // check this survey's tags
            $matchedCount = 0;
            
            // Find which tags match between snapshot and survey
            foreach ($surveyTags as $surveyTag) {
                $matched = false;
                
                // Check if this survey tag exists in the snapshot's demographic tags
                foreach ($demographicTags as $demographicTag) {
                    if (isset($demographicTag['id']) && $demographicTag['id'] === $surveyTag->id) {
                        $this->matchedSurveyTagsInfo[] = [
                            'name' => $surveyTag->name, 
                            'matched' => true
                        ];
                        $matched = true;
                        $matchedCount++;
                        break;
                    }
                }
                
                // If no match was found, add it as unmatched
                if (!$matched) {
                    $this->matchedSurveyTagsInfo[] = [
                        'name' => $surveyTag->name, 
                        'matched' => false
                    ];
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
        } else if ($this->respondentUser) {
            // Fallback to user data for demographics if snapshot is not available
            $this->timeCompleted = "0:00";
            
            // Process demographic matching using current user data
            $this->matchedSurveyTagsInfo = [];
            $respondentTagIds = $this->respondentUser->tags->pluck('id')->toArray();
            $surveyTags = $this->survey->tags;
            $matchedCount = 0;
            
            foreach ($surveyTags as $surveyTag) {
                if (in_array($surveyTag->id, $respondentTagIds)) {
                    $this->matchedSurveyTagsInfo[] = ['name' => $surveyTag->name, 'matched' => true];
                    $matchedCount++;
                } else {
                    $this->matchedSurveyTagsInfo[] = ['name' => $surveyTag->name, 'matched' => false];
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
            // Default values when no data available
            $this->timeCompleted = "0:00";
            $this->matchedSurveyTagsInfo = ['status' => 'no_user_data'];
        }

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

                // Get answers for this question from the current response
                $questionAnswer = $this->currentRespondent->answers->where('survey_question_id', $question->id)->first(); // laravel collection then first grabs it and returns a model
                $answerDataString = $questionAnswer?->answer; // grabs the answer field from the model, which is a json string
                $decodedAnswer = $answerDataString ? json_decode($answerDataString, true) : []; // php associative array

                // Process based on question type
                if (in_array($question->question_type, ['multiple_choice', 'radio'])) {
                    // For choice-based questions, mark selected options
                    $otherChoiceId = null;
                    $otherSelected = false;
                    
                    foreach ($question->choices as $choice) {
                        $isSelected = false; //sets current choice as false or not selected
                        if ($decodedAnswer !== null) {
                            // Multiple choice stores answer as array of IDs
                            // the decoded answer must be an array and the choice id must be in that array if it is mark that choice as selected
                            if ($question->question_type === 'multiple_choice' && is_array($decodedAnswer) && in_array($choice->id, $decodedAnswer)) {
                                $isSelected = true;
                                
                                // Track if an "other" option is selected
                                if ($choice->is_other) {
                                    $otherSelected = true;
                                    $otherChoiceId = $choice->id;
                                }
                            } 
                            // Radio stores answer as single ID
                            // the decoded answer must not be an array and the decoded answer
                            // decoded answer for radio buttons is usually just equal to "3" or "4" or the index of their choice so it works well with choice->id
                            elseif ($question->question_type === 'radio' && !is_array($decodedAnswer) && (int)$decodedAnswer === $choice->id) {
                                $isSelected = true;
                                
                                // Track if an "other" option is selected
                                if ($choice->is_other) {
                                    $otherSelected = true;
                                    $otherChoiceId = $choice->id;
                                }
                            }
                        }
                        
                        //add this choice's data id, text, and selection status to the array
                        $questionData['choices'][] = [
                            'id' => $choice->id,
                            'choice_text' => $choice->choice_text,
                            'is_selected' => $isSelected,
                            'is_other' => $choice->is_other ? 1 : 0,  // Add the is_other field
                        ];
                    }
                    
                    // Add the "other_text" value if an "other" option was selected
                    if ($otherSelected) {
                        // First check: get other_text directly from the answer record column
                        if ($questionAnswer && !empty($questionAnswer->other_text)) {
                            $questionData['other_text'] = $questionAnswer->other_text;
                        } 
                        // Second check: look for a separate "other_text" type answer
                        else {
                            $otherTextAnswer = $this->currentRespondent->answers
                                ->where('survey_question_id', $question->id)
                                ->whereNotNull('other_text')
                                ->first();
                                
                            if ($otherTextAnswer && !empty($otherTextAnswer->other_text)) {
                                $questionData['other_text'] = $otherTextAnswer->other_text;
                            }
                            // Third check: try to get it from metadata
                            elseif ($questionAnswer && isset($questionAnswer->metadata)) {
                                $metadata = json_decode($questionAnswer->metadata, true);
                                $questionData['other_text'] = $metadata['other_text'] ?? null;
                            }
                        }
                        
                        // If still no other_text found, check the specific answer for radio/choice
                        if (empty($questionData['other_text']) && $questionAnswer) {
                            // For multiple choice, try to find the specific answer with the other choice ID
                            if ($question->question_type === 'multiple_choice') {
                                // Check each answer that matches the question ID
                                foreach ($this->currentRespondent->answers as $answer) {
                                    if ($answer->survey_question_id === $question->id && !empty($answer->other_text)) {
                                        $questionData['other_text'] = $answer->other_text;
                                        break;
                                    }
                                }
                            }
                            // For radio buttons, the answer is directly on the record
                            elseif ($question->question_type === 'radio' && $otherChoiceId) {
                                // The other_text should be on the main answer record
                                $questionData['other_text'] = $questionAnswer->other_text;
                            }
                        }
                    } else {
                        $questionData['other_text'] = null;
                    }
                } elseif ($question->question_type === 'likert') {
                    // For Likert scales, decode JSON structure and mark selected cells
                    // if question likert columns returns an array then use it, otherwise decode the json string as its json encoded in database if fails use an empty array
                    $questionData['likert_columns'] = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
                    $questionData['likert_rows'] = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                    $questionData['likert_answer_data'] = $decodedAnswer ?: [];
                } elseif ($question->question_type === 'rating') {
                    // For rating questions (stars), default to 0 if no answer
                    $questionData['single_answer'] = $answerDataString ?? '0'; 
                } else { 
                    // For text questions (essay, short_text, date)
                    $questionData['single_answer'] = $answerDataString ?? 'No answer';
                }



                // add this question data into the processed questions array as one element att the end of the array due to the []             
                $processedQuestions[] = $questionData;
            }
            
            // Add processed page data
            //handles the page title and subtitle and grabs all the questiosn as we have processed
            $this->pagesWithProcessedAnswers[] = [
                'id' => $page->id,
                'title' => $page->title,
                'subtitle' => $page->subtitle,
                'questions' => $processedQuestions,
            ];
        }
    }

    // Add method to refresh current response after reporting
    public function refreshCurrentResponse()
    {
        if ($this->currentRespondent) {
            $this->currentRespondent->refresh();
        }
    }

    // Add method to handle response reported event
    public function handleResponseReported()
    {
        // Refresh the survey with all its responses to get updated reported status
        $this->survey->load('responses.user.tags');
        
        // Refresh the current response details
        $this->processCurrentResponseDetails();
        
        // Optional: Show a brief success message or visual feedback
        $this->dispatch('$refresh');
    }

    public function goToRespondent()
    {
        $this->gotoError = '';
        $max = $this->survey->responses->count();
        $number = intval($this->gotoRespondent);
        // Validate input: must be a number, >= 1, <= max
        if (!is_numeric($this->gotoRespondent) || $number < 1 || $number > $max) {
            $this->gotoError = "Please enter a valid respondent number between 1 and $max.";
            return;
        }
        $this->current = $number - 1;
        $this->gotoRespondent = '';
        $this->processCurrentResponseDetails();
    }

    public function render()
    {
        return view('livewire.surveys.form-responses.individual-responses');
    }
}