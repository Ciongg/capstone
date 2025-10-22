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

    /**
     * Check if user has already answered this survey
     */
    protected function hasUserAnsweredSurvey($survey, $userId)
    {
        return Response::where('survey_id', $survey->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Check if user trust score is too low to answer surveys
     */
    protected function hasTooLowTrustScore($user)
    {
        return $user && $user->trust_score <= 40;
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

    /**
     * Show the survey answering form.
     * Access control is handled by SurveyAccessMiddleware.
     *
     * @param Survey $survey
     * @return \Illuminate\View\View
     */
    public function answer(Survey $survey)
    {
        // If the user is authenticated, perform additional checks
        if (Auth::check()) {
            $user = Auth::user();
            
            // Don't allow users with low trust scores
            if ($this->hasTooLowTrustScore($user)) {
                abort(403, 'Your trust score is too low to participate in surveys.');
            }
            
            // Don't allow the creator to answer their own survey
            if ($survey->user_id == $user->id) {
                abort(403, 'You cannot answer your own survey.');
            }
            
            // Don't allow collaborators to answer the survey
            if ($survey->isCollaborator($user)) {
                abort(403, 'You cannot answer a survey you collaborate on.');
            }
            
            // Don't allow users who have already answered the survey
            if ($this->hasUserAnsweredSurvey($survey, $user->id)) {
                abort(403, 'You have already answered this survey.');
            }
        }
        
        // Allow access if all checks pass or if the user is a guest with guest access enabled
        return view('respondent.show-answer-form', compact('survey'));
    }

    /**
     * Handle form submission.
     */
    public function submit(Survey $survey, Request $request)
    {
        // Form submission is handled by Livewire, but we should still do validation checks
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user has too low trust score
            if ($this->hasTooLowTrustScore($user)) {
                abort(403, 'Your trust score is too low to participate in surveys.');
            }
            
            // Check if user is eligible to submit
            if ($survey->user_id == $user->id || 
                $survey->isCollaborator($user) || 
                $this->hasUserAnsweredSurvey($survey, $user->id)) {
                abort(403, 'You are not eligible to submit a response to this survey.');
            }
        }
        
        return back();
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

    /**
     * Show the survey answering form for guest users (only if allowed)
     */
    public function guestAnswer(Survey $survey)
    {
        // Check if the survey allows guest responses
        if (!$survey->is_guest_allowed) {
            abort(403, 'This survey requires login to participate.');
        }
        
        // Flag to show guest access notice on the survey page
        session()->flash('guest_survey_access', true);
        
        return view('respondent.show-answer-form', [
            'survey' => $survey,
        ]);
    }

    /**
     * Handle submission of a survey for guest users (only if allowed)
     */
    public function guestSubmit(Survey $survey, Request $request)
    {
        // Check if the survey allows guest responses
        if (!$survey->is_guest_allowed) {
            abort(403, 'This survey does not allow guest responses.');
        }
        
        // The actual submission is handled by the Livewire component
        return back();
    }
}