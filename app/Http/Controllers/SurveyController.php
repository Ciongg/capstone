<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;

class SurveyController extends Controller
{
    public function create()
    {
        // Check if the user already has an existing survey in progress
        $existingSurvey = Survey::where('user_id', Auth::id())
            ->where('status', 'wip') // Work in progress
            ->first();

        if ($existingSurvey) {
            // Redirect to the FormBuilder component for the existing survey
            return view('researcher.show-form-builder', ['survey' => $existingSurvey]);
        }

        // Create a new survey if no existing survey is found
        $survey = Survey::create([
            'user_id' => Auth::id(),
            'title' => 'Untitled Survey',
            'description' => null,
            'status' => 'wip', // Work in progress
            'type' => 'basic',
        ]);

        // Add a default page to the survey
        $page = SurveyPage::create([
            'survey_id' => $survey->id,
            'page_number' => 1,
        ]);

        // Add a default question to the page
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'survey_page_id' => $page->id,
            'question_text' => 'Default Question Text',
            'question_type' => 'multiple_choice',
            'order' => 1,
            'required' => false,
        ]);

        // Add a default choice to the question
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Default Choice Text',
        ]);

        // Redirect to the FormBuilder component for the new survey
        return view('researcher.show-form-builder', ['survey' => $survey]);
    }
}
