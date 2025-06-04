<?php

namespace App\Livewire\Surveys\FormResponses;

use Livewire\Component;
use App\Models\Survey;
use App\Models\Response; // Used for type hinting
use Carbon\CarbonInterface; // Used for time difference formatting
use App\Models\User; // Used for type hinting

/**
 * Shows detailed response data and allows navigation between respondents
 */
class IndividualResponses extends Component
{

    public Survey $survey;
    
    public int $current = 0;

    //The currently displayed response 
    public ?Response $currentRespondent = null;
    
    // The user who submitted the current response 
    public ?User $respondentUser = null;
    
    // User's trust score (0-100) 
    public int $trustScore = 0;
    
    // Human-readable time taken to complete the survey 
    public ?string $timeCompleted = null;
    
    // Information about matched demographic tags 
    public array $matchedSurveyTagsInfo = [];
    
    //  Processed survey pages with questions and answers 
    public array $pagesWithProcessedAnswers = [];

    
    //Initialize component with survey data
    //$surveyId ID of the survey to display
    
    public function mount($surveyId)
    {
        // Load survey with all related data needed for displaying responses
        $this->survey = Survey::with([
            'responses.user.tags', // For demographic matching
            'responses.answers',   // For displaying answers
            'pages.questions.choices', // For showing question options
            'tags'                 // For demographic targeting comparison
            ])->findOrFail($surveyId);
            
            $this->processCurrentResponseDetails();
        }
        
          //Process updated response when navigation changes e.g when the current variable's value changes
          public function updatedCurrent()
          {
              $this->processCurrentResponseDetails();
          }
        
    // Process the current response data for display
    // Sets all the component properties based on the current response
    public function processCurrentResponseDetails()
    {
        // Reset all values if no responses exist or index is invalid used for fallback errors
        if ($this->survey->responses->isEmpty() || !isset($this->survey->responses[$this->current])) {
            $this->currentRespondent = null;
            $this->respondentUser = null;
            $this->trustScore = 0;
            $this->timeCompleted = null;
            $this->matchedSurveyTagsInfo = [];
            $this->pagesWithProcessedAnswers = [];
            return;
        }

        // Set current response and user based on the passed survey
        $this->currentRespondent = $this->survey->responses[$this->current];
        $this->respondentUser = $this->currentRespondent->user;

        // Process user information if available
        if ($this->respondentUser) {
            // Get trust score
            $this->trustScore = $this->respondentUser->trust_score ?? 0;

            // Process demographic matching information
            $this->matchedSurveyTagsInfo = [];
            $respondentTagIds = $this->respondentUser->tags->pluck('id')->toArray(); //grab all the respondent tag IDs to an array
            $surveyTags = $this->survey->tags; // check this survey's tags
            $matchedCount = 0;
            
            // Find which tags match between user and survey
            foreach ($surveyTags as $surveyTag) {
                // Loop through each survey tag and check if it exists in the respondent's tag IDs.
                // If it matches, add an associative array to matchedSurveyTagsInfo with the tag name and a matched status.

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

        // Calculate time to complete the survey
        if ($this->currentRespondent->created_at && $this->currentRespondent->updated_at) {
            $this->timeCompleted = $this->currentRespondent->updated_at->diffForHumans(
                $this->currentRespondent->created_at,
                ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]
            );
        } else {
            $this->timeCompleted = null;
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
                    foreach ($question->choices as $choice) {
                        $isSelected = false; //sets current choice as false or not selected
                        if ($decodedAnswer !== null) {
                            // Multiple choice stores answer as array of IDs
                            // the decoded answer must be an array and the choce id must be in that array if it is mark that choice as selected
                            if ($question->question_type === 'multiple_choice' && is_array($decodedAnswer) && in_array($choice->id, $decodedAnswer)) {
                                $isSelected = true;
                            } 
                            // Radio stores answer as single ID
                            // the decoded answer must not be an array and the decoded answer
                            // decoded answer for radio buttons is usually just equal to "3" or "4" or the index of their choice so it works well with choice->id
                            elseif ($question->question_type === 'radio' && !is_array($decodedAnswer) && (int)$decodedAnswer === $choice->id) {
                                $isSelected = true;
                            }
                        }
                        
                        //add this choice's data id, text, and selection status to the array
                        $questionData['choices'][] = [
                            'id' => $choice->id,
                            'choice_text' => $choice->choice_text,
                            'is_selected' => $isSelected,
                        ];
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

    public function render()
    {
        return view('livewire.surveys.form-responses.individual-responses');
    }
}