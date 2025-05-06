<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use App\Models\Answer;
use App\Models\Response;
use Illuminate\Contracts\View\View; // Import View

class SurveyController extends Controller
{

    public function create($survey = null): View
    {
        if ($survey) {
            // Open an existing survey by ID
            $surveyModel = Survey::findOrFail($survey);

            // Optional: Check if the logged-in user owns this survey
            if ($surveyModel->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }

            return view('researcher.show-form-builder', ['survey' => $surveyModel]);
        }

        // Check if the user already has an existing survey in progress
        $existingSurvey = Survey::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($existingSurvey) {
            return view('researcher.show-form-builder', ['survey' => $existingSurvey]);
        }

        // Create a new survey if no existing survey is found
        $surveyModel = Survey::create([
            'user_id' => Auth::id(),
            'title' => 'Untitled Survey',
            'description' => null,
            'status' => 'pending',
            'type' => 'basic',
        ]);

        // Add a default page to the survey
        $page = SurveyPage::create([
            'survey_id' => $surveyModel->id,
            'page_number' => 1,
        ]);

        // Add a default question to the page
        $question = SurveyQuestion::create([
            'survey_id' => $surveyModel->id,
            'survey_page_id' => $page->id,
            'question_text' => 'Enter Question Here',
            'question_type' => 'multiple_choice',
            'order' => 1,
            'required' => false,
        ]);

        // Add a default choice to the question
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1,
        ]);

        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 2',
            'order' => 2,
        ]);

        return view('researcher.show-form-builder', ['survey' => $surveyModel]);
    }

    public function answer(Survey $survey)
    {
        $survey->load('pages.questions.choices');
        return view('respondent.show-answer-form', compact('survey'));
    }

    public function showAnswerForm(Survey $survey, $isPreview = false): View
    {
        // Pass the survey model and the isPreview flag to the view.
        // The view 'respondent.show-answer-form' will handle rendering the Livewire component.
        return view('respondent.show-answer-form', [
            'survey' => $survey,
            'isPreview' => (bool) $isPreview // Ensure it's boolean
        ]);
    }

    public function showSurveys(): View
    {
        return view('researcher.show-form-index');
    }

    public function showResponses($surveyId): View
    {
        $survey = Survey::findOrFail($surveyId);
        return view('researcher.show-form-responses', compact('survey'));
    }

    public function showIndividualResponses(Survey $survey): View
    {
        return view('researcher.show-individual-responses', ['surveyId' => $survey->id]);
    }

}
