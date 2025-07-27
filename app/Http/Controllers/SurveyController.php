<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Contracts\View\View;

class SurveyController extends Controller
{
    public function create(Request $request, $surveyId = null)
    {
        $user = Auth::user();

        // Only allow super_admin, researcher, respondent
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin', 'respondent'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($surveyId) {
            $surveyModel = Survey::find($surveyId);

            // Check if survey exists
            if (!$surveyModel) {
                abort(404, 'The requested page could not be found.');
            }

            // Only super_admin can access all, others only their own
            if ($user->type !== 'super_admin' && $surveyModel->user_id !== $user->id) {
                abort(403, 'You do not have permission to access this page.');
            }

            return view('researcher.show-form-builder', ['survey' => $surveyModel]);
        }

        // Only allow survey creation for researcher, institution_admin, super_admin
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        abort(403, 'You do not have permission to access this page.');
    }

    public function answer(Survey $survey)
    {
        $user = Auth::user();
        
        // Check if the logged-in user is the owner of this survey
        if ($survey->user_id === Auth::id()) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        // Check if user has already answered this survey
        $existingResponse = Response::where('survey_id', $survey->id)
                                   ->where('user_id', $user->id)
                                   ->first();
        
        if ($existingResponse) {
            // User has already answered, show the "already answered" interface
            return view('respondent.show-already-answered', compact('survey', 'existingResponse'));
        }
        
        $survey->load('pages.questions.choices');
        return view('respondent.show-answer-form', compact('survey'));
    }

    public function showAnswerForm(Survey $survey, $isPreview = false): View
    {
        $user = Auth::user();

        if ($isPreview) {
            // Only allow super_admin to preview any, others only their own
            if ($user->type !== 'super_admin' && $survey->user_id !== $user->id) {
                abort(403, 'You do not have permission to access this page.');
            }
        }

        return view('respondent.show-answer-form', [
            'survey' => $survey,
            'isPreview' => (bool) $isPreview
        ]);
    }

    public function showAnswerFormRedirect()
    {
        abort(403, 'You do not have permission to access this page.');
    }

    public function showSurveys(): View
    {
        return view('researcher.show-form-index');
    }

    public function showResponses($surveyId): View
    {
        $user = Auth::user();

        // Only allow super_admin, researcher, respondent
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin', 'respondent'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        $survey = Survey::find($surveyId);

        // Check if survey exists
        if (!$survey) {
            abort(404, 'The requested page could not be found.');
        }

        // Only super_admin can access all, others only their own
        if ($user->type !== 'super_admin' && $survey->user_id !== $user->id) {
            abort(403, 'You do not have permission to access this page.');
        }

        return view('researcher.show-form-responses', compact('survey'));
    }

    public function showIndividualResponses(Survey $survey): View
    {
        $user = Auth::user();

        // Only allow super_admin, researcher, respondent
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin', 'respondent'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        // Only super_admin can access all, others only their own
        if ($user->type !== 'super_admin' && $survey->user_id !== $user->id) {
            abort(403, 'You do not have permission to access this page.');
        }

        return view('researcher.show-individual-responses', ['surveyId' => $survey->id]);
    }

    public function showOwnResponse(Survey $survey, Response $response)
    {
        return view('respondent.show-own-response', compact('survey', 'response'));
    }

}
   
