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
        // Check if user has permission to create/edit surveys
        $user = Auth::user();
        
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin'])) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        // If a specific survey ID is provided, show that survey for editing
        if ($surveyId) {
            $surveyModel = Survey::find($surveyId);
            
            // Check if survey exists
            if (!$surveyModel) {
                abort(404, 'The requested page could not be found.');
            }
            
            // Check if the logged-in user owns this survey or is a super admin
            if ($surveyModel->user_id !== Auth::id() && $user->type !== 'super_admin') {
                abort(403, 'You do not have permission to access this page.');
            }
            
            return view('researcher.show-form-builder', ['survey' => $surveyModel]);
        }
        
        // If no survey ID is provided, abort with 403 error
        // Survey creation should only happen through the proper workflow (modals)
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
        
        // If this is a preview, check permissions
        if ($isPreview) {
            // Check if user has permission to preview surveys
            if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin'])) {
                abort(403, 'You do not have permission to access this page.');
            }
            
            // Check if the logged-in user owns this survey or is a super admin
            if ($survey->user_id !== Auth::id() && $user->type !== 'super_admin') {
                abort(403, 'You do not have permission to access this page.');
            }
        }
        
        // Pass the survey model and the isPreview flag to the view.
        // The view 'respondent.show-answer-form' will handle rendering the Livewire component.
        return view('respondent.show-answer-form', [
            'survey' => $survey,
            'isPreview' => (bool) $isPreview // Ensure it's boolean
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
        
        // Check if user has permission to view survey responses
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin'])) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        $survey = Survey::find($surveyId);
        
        // Check if survey exists
        if (!$survey) {
            abort(404, 'The requested page could not be found.');
        }
        
        // Check if the logged-in user owns this survey or is a super admin
        if ($survey->user_id !== Auth::id() && $user->type !== 'super_admin') {
            abort(403, 'You do not have permission to access this page.');
        }
        
        return view('researcher.show-form-responses', compact('survey'));
    }

    public function showIndividualResponses(Survey $survey): View
    {
        $user = Auth::user();
        
        // Check if user has permission to view individual survey responses
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin'])) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        // Check if the logged-in user owns this survey or is a super admin
        if ($survey->user_id !== Auth::id() && $user->type !== 'super_admin') {
            abort(403, 'You do not have permission to access this page.');
        }
        
        return view('researcher.show-individual-responses', ['surveyId' => $survey->id]);
    }

    public function showOwnResponse(Survey $survey, Response $response)
    {
        return view('respondent.show-own-response', compact('survey', 'response'));
    }

}
