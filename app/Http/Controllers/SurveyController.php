<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Contracts\View\View;

class SurveyController extends Controller
{
    public function create(Request $request, $surveyId = null): View
    {
        // If a specific survey ID is provided, show that survey
        if ($surveyId) {
            $surveyModel = Survey::findOrFail($surveyId);
            
            // Check if the logged-in user owns this survey
            if ($surveyModel->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }
            
            return view('researcher.show-form-builder', ['survey' => $surveyModel]);
        }
        
        // Fallback: If /surveys/create is hit directly without an ID (e.g. user types URL)
        // This path will NOT use type/method from URL parameters anymore.
        // It creates a very default 'basic' survey.
        // The primary creation path is now through the modal and its Livewire component.
        $surveyModel = Survey::create([
            'user_id' => Auth::id(),
            'title' => 'Untitled Survey (Default)',
            'description' => null,
            'status' => 'pending',
            'type' => 'basic', 
            'points_allocated' => 10, // Default points for a basic survey
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
            'question_text' => 'Enter Question Title',
            'question_type' => 'multiple_choice',
            'order' => 1,
            'required' => false,
        ]);
        
        // Add default choices to the question
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
