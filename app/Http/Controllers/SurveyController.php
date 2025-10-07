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
use Carbon\Carbon;
use App\Services\TestTimeService;

class SurveyController extends Controller
{
    /**
     * Check if user can access the survey as owner or collaborator
     */
    protected function canAccessSurvey($survey)
    {
        $user = auth()->user();
        
        // Super admins and institution admins can access all surveys
        if ($user->isSuperAdmin() || $user->type === 'institution_admin') {
            return true;
        }
        
        // Owner can access
        if ($survey->user_id === $user->id) {
            return true;
        }
        
        // Collaborators can access
        if ($survey->isCollaborator($user)) {
            return true;
        }
        
        return false;
    }

    public function create(Request $request, $surveyId = null)
    {
        $user = Auth::user();

        // Only allow super_admin, researcher, respondent
        if (!in_array($user->type, ['researcher', 'institution_admin', 'super_admin', 'respondent'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($surveyId) {
            // Find survey by UUID instead of ID
            $surveyModel = Survey::where('uuid', $surveyId)->first();

            // Check if survey exists
            if (!$surveyModel) {
                abort(404, 'The requested survey could not be found.');
            }

            // Check if user can access this survey (as owner or collaborator)
            if (!$this->canAccessSurvey($surveyModel)) {
                abort(403, 'You do not have permission to access this survey.');
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
            abort(403, 'You cannot answer your own survey.');
        }
        
        // Check if the logged-in user is a collaborator of this survey
        if ($survey->isCollaborator($user)) {
            abort(403, 'You cannot answer a survey you collaborate on.');
        }
        
        // Check if survey is locked by admin
        if ($survey->isLocked()) {
            abort(403, 'This survey has been locked by an administrator.');
        }

        // Check if survey is not started yet - using TestTimeService for consistency
        if ($survey->start_date && TestTimeService::now()->lt($survey->start_date)) {
            $start = \Carbon\Carbon::parse($survey->start_date)->format('M j, Y g:i A');
            abort(403, "This survey will start accepting responses on $start.");
        }
        
        // Check if survey is expired - using TestTimeService for consistency
        if ($survey->end_date && TestTimeService::now()->gt($survey->end_date)) {
            abort(403, 'This survey has expired and is no longer accepting responses.');
        }
        
        // Check if target respondents reached
        if ($survey->target_respondents && $survey->responses()->count() >= $survey->target_respondents) {
            abort(403, 'This survey has reached its maximum number of responses.');
        }
        
        // Check for demographic matching if it's an advanced survey
        if ($survey->type === 'advanced') {
            $userTagIds = $user->tags()->pluck('tags.id')->toArray();
            $surveyTagIds = $survey->tags()->pluck('tags.id')->toArray();
            
            // If the survey has tags but user doesn't match any of them
            if (!empty($surveyTagIds) && empty(array_intersect($userTagIds, $surveyTagIds))) {
                abort(403, 'You do not meet the demographic requirements for this advanced survey.');
            }
            
            // Check institution tags if survey is institution only
            if ($survey->is_institution_only) {
                $userInstitutionTagIds = $user->institutionTags()->pluck('institution_tags.id')->toArray();
                $surveyInstitutionTagIds = $survey->institutionTags()->pluck('institution_tags.id')->toArray();
                
                if (!empty($surveyInstitutionTagIds) && empty(array_intersect($userInstitutionTagIds, $surveyInstitutionTagIds))) {
                    abort(403, 'This survey is restricted to specific institutions that you are not a part of.');
                }
            }
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
            // For previews, check if user can access as owner or collaborator
            if (!$this->canAccessSurvey($survey)) {
                abort(403, 'You do not have permission to preview this survey.');
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

        // Find survey by UUID instead of ID
        $survey = Survey::where('uuid', $surveyId)->first();

        // Check if survey exists
        if (!$survey) {
            abort(404, 'The requested survey could not be found.');
        }

        // Check if user can access this survey (as owner or collaborator)
        if (!$this->canAccessSurvey($survey)) {
            abort(403, 'You do not have permission to view responses for this survey.');
        }

        return view('researcher.show-form-responses', compact('survey'));
    }

    public function showIndividualResponses(Survey $survey): View
    {
        // Check if user can access this survey (as owner or collaborator)
        if (!$this->canAccessSurvey($survey)) {
            abort(403, 'You do not have permission to view individual responses for this survey.');
        }

        return view('researcher.show-individual-responses', ['surveyId' => $survey->id]);
    }

    public function showOwnResponse(Survey $survey, Response $response)
    {
        return view('respondent.show-own-response', compact('survey', 'response'));
    }
}