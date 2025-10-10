<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice; 
use App\Models\Response;
use App\Models\Answer;
use App\Models\Institution;
use App\Models\SurveyTopic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\TestTimeService;

// Ensures database is reset between tests for isolation
uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up the database environment needed for survey answer testing
    // This represents a complete survey ecosystem with institution, users,
    // survey, questions, and possible answers
    
    // Create an academic institution for the researcher
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);
    
    // Create a topic category for the survey
    $this->surveyTopic = SurveyTopic::create([
        'name' => 'Test Topic',
        'description' => 'A test survey topic'
    ]);
    
    // Create a respondent user who will answer the survey
    $this->user = User::factory()->create([
        'email' => 'respondent@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'is_active' => true,
        'email_verified_at' => now(),
        'last_active_at' => now(),
        'points' => 50,          // Starting with some points
        'experience_points' => 200,
        'account_level' => 1,
        'trust_score' => 100,    // Perfect trust score
    ]);
    
    // Create a researcher user who will own the survey
    $this->creator = User::factory()->create([
        'email' => 'researcher@example.com',
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create a published survey ready to be answered
    $this->survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Test Survey',
        'description' => 'A test survey for testing',
        'user_id' => $this->creator->id,
        'survey_topic_id' => $this->surveyTopic->id,
        'status' => 'published',  // Survey is published and available
        'points_allocated' => 10, // Respondent will earn 10 points
        'target_respondents' => 100,
        'start_date' => now()->subDay(), // Started yesterday
        'end_date' => now()->addDays(7), // Ends in a week
        'is_locked' => false,     // Not locked/restricted
    ]);
    
    // Create a page to contain the questions
    $this->page = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Page 1',
        'order' => 1,
    ]);
    
    // Create various question types to test different input methods
    
    // Short text question (for collecting simple text responses)
    $this->textQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id, 
        'survey_page_id' => $this->page->id,
        'question_text' => 'What is your name?',
        'question_type' => 'short_text',
        'required' => true,  // This question must be answered
        'order' => 1,
    ]);
    
    // Radio button question (single choice from multiple options)
    $this->radioQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id, 
        'survey_page_id' => $this->page->id,
        'question_text' => 'What is your favorite color?',
        'question_type' => 'radio',
        'required' => true,
        'order' => 2,
    ]);
    
    // Create options for the radio button question
    $this->redChoice = SurveyChoice::create([
        'survey_question_id' => $this->radioQuestion->id,
        'choice_text' => 'Red',
        'order' => 1,
    ]);
    
    $this->blueChoice = SurveyChoice::create([
        'survey_question_id' => $this->radioQuestion->id,
        'choice_text' => 'Blue',
        'order' => 2,
    ]);
    
    // Multiple choice question (can select multiple options)
    $this->multipleChoiceQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id, 
        'survey_page_id' => $this->page->id,
        'question_text' => 'Which languages do you speak?',
        'question_type' => 'multiple_choice',
        'required' => false, // This question is optional
        'order' => 3,
    ]);
    
    // Create options for the multiple choice question
    $this->englishChoice = SurveyChoice::create([
        'survey_question_id' => $this->multipleChoiceQuestion->id,
        'choice_text' => 'English',
        'order' => 1,
    ]);
    
    $this->spanishChoice = SurveyChoice::create([
        'survey_question_id' => $this->multipleChoiceQuestion->id,
        'choice_text' => 'Spanish',
        'order' => 2,
    ]);
    
    // Likert scale question (matrix-style rating across multiple dimensions)
    $this->likertQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id, 
        'survey_page_id' => $this->page->id,
        'question_text' => 'Rate your satisfaction',
        'question_type' => 'likert',
        'required' => true,
        'likert_rows' => ['Service Quality', 'Response Time'], // Two aspects to rate
        'likert_columns' => ['Poor', 'Fair', 'Good', 'Excellent'], // Rating options
        'order' => 4,
    ]);
});

it('can render the answer survey screen', function () {
    // This test verifies that users can access the survey answer page
    // and that the page correctly loads the Livewire component
    
    // Login as a respondent
    Auth::login($this->user);
    
    // Visit the survey answer page using the survey's UUID
    $response = $this->get(route('surveys.answer', $this->survey->uuid));
    
    // Verify the page loads successfully
    expect($response->status())->toBe(200);
    
    // Verify the correct Livewire component for answering surveys is present
    $response->assertSeeLivewire('surveys.answer-survey.answer-survey');
});

it('can submit survey with all answer types and save to database', function () {
    // This test verifies the core survey submission functionality:
    // - All types of answers are properly saved to the database
    // - Response and snapshot records are created
    // - User receives points and experience for completing the survey
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Prepare sample answers for each question type
    $answers = [
        $this->textQuestion->id => 'John Doe',
        $this->radioQuestion->id => $this->redChoice->id,
        $this->multipleChoiceQuestion->id => [
            $this->englishChoice->id => true,
            $this->spanishChoice->id => true,
        ],
        $this->likertQuestion->id => [0 => 'Good', 1 => 'Excellent'], // Rating for each row
    ];
    
    // Track initial counts to verify changes
    $initialResponseCount = Response::count();
    $initialAnswerCount = Answer::count();
    $initialUserPoints = $this->user->points;
    $initialUserXP = $this->user->experience_points;
    
    // Simulate the form submission through the Livewire component
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', $answers)         // Provide all answers
        ->set('navAction', 'submit')       // Set to submit (not just navigate)
        ->call('submit')                   // Submit the form
        ->assertDispatched('surveySubmitted'); // Verify success event was triggered
    
    // Verify a new response record was created
    expect(Response::count())->toBe($initialResponseCount + 1);
    
    // Get the newly created response
    $response = Response::latest()->first();
    expect($response->survey_id)->toBe($this->survey->id);
    expect($response->user_id)->toBe($this->user->id);
    expect($response->reported)->toBeFalse(); // Verify it's not flagged
    
    // Verify user demographic snapshot was created (important for data integrity)
    expect($response->snapshot)->not->toBeNull();
    expect($response->snapshot->first_name)->toBe($this->user->first_name);
    expect($response->snapshot->trust_score)->toEqual($this->user->trust_score);
    expect($response->snapshot->completion_time_seconds)->toBeInt(); // Time tracking works
    
    // Verify all answers were saved correctly (one per question)
    expect(Answer::count())->toBe($initialAnswerCount + 4);
    
    // Verify text answer was saved correctly
    $textAnswer = Answer::where('response_id', $response->id)
        ->where('survey_question_id', $this->textQuestion->id)
        ->first();
    expect($textAnswer->answer)->toBe('John Doe');
    
    // Verify radio button answer stores the selected choice ID
    $radioAnswer = Answer::where('response_id', $response->id)
        ->where('survey_question_id', $this->radioQuestion->id)
        ->first();
    expect($radioAnswer->answer)->toBe((string)$this->redChoice->id);
    
    // Verify multiple choice answer stores selected choices as JSON
    $multipleAnswer = Answer::where('response_id', $response->id)
        ->where('survey_question_id', $this->multipleChoiceQuestion->id)
        ->first();
    $decodedAnswer = json_decode($multipleAnswer->answer, true);
    expect($decodedAnswer)->toContain($this->englishChoice->id, $this->spanishChoice->id);
    
    // Verify likert scale answer stores ratings as JSON
    $likertAnswer = Answer::where('response_id', $response->id)
        ->where('survey_question_id', $this->likertQuestion->id)
        ->first();
    $decodedLikert = json_decode($likertAnswer->answer, true);
    expect($decodedLikert[0])->toBe('Good');
    expect($decodedLikert[1])->toBe('Excellent');
    
    // Verify respondent received the allocated points and XP
    $this->user->refresh();
    expect($this->user->points)->toBe($initialUserPoints + $this->survey->points_allocated);
    expect($this->user->experience_points)->toBe($initialUserXP + 100); // Standard 100 XP
});

it('validates required fields before submission', function () {
    // This test verifies that required questions must be answered
    // before a survey can be submitted
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Try submitting with missing required answers
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', [
            $this->textQuestion->id => '', // Empty value for required text question
            $this->radioQuestion->id => null, // No selection for required radio question
            // Missing likert question entirely
        ])
        ->set('navAction', 'submit')
        ->call('submit')
        // Verify validation errors are triggered for each required field
        ->assertHasErrors([
            'answers.' . $this->textQuestion->id,
            'answers.' . $this->radioQuestion->id,
            'answers.' . $this->likertQuestion->id, 
        ])
        // Verify validation alert is shown to user
        ->assertDispatched('showValidationAlert');
    
    // Verify no response was created due to validation failure
    expect(Response::where('survey_id', $this->survey->id)->count())->toBe(0);
});

it('handles survey availability validation', function () {
    // This test verifies that surveys cannot be submitted if:
    // 1. The survey has expired (past end date)
    // 2. The survey is locked by an administrator
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Test expired survey scenario
    $this->survey->update(['end_date' => now()->subDay()]); // Set end date to yesterday
    
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', [$this->textQuestion->id => 'Test'])
        ->set('navAction', 'submit')
        ->call('submit')
        // Verify error is shown explaining why submission failed
        ->assertDispatched('surveySubmissionError');
    
    // Test locked survey scenario
    $this->survey->update([
        'end_date' => now()->addDay(), // Make it active again
        'is_locked' => true, // But lock it
        'lock_reason' => 'Survey under review'
    ]);
    
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', [$this->textQuestion->id => 'Test'])
        ->set('navAction', 'submit')
        ->call('submit')
        // Verify error is shown explaining why submission failed
        ->assertDispatched('surveySubmissionError');
});

it('handles response limit reached', function () {
    // This test verifies that surveys cannot be submitted when
    // they have reached their target number of respondents
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Set a low response limit of just 1 respondent
    $this->survey->update(['target_respondents' => 1]);
    
    // Create a response from a different user to reach the limit
    Response::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'survey_id' => $this->survey->id,
        'user_id' => User::factory()->create()->id, // Different user
        'reported' => false,
    ]);
    
    // Attempt to submit after limit is reached
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', [$this->textQuestion->id => 'Test'])
        ->set('navAction', 'submit')
        ->call('submit')
        // Verify submission error explains the limit is reached
        ->assertDispatched('surveySubmissionError');
});

it('updates survey status from published to ongoing on first response', function () {
    // This test verifies that a survey's status automatically changes from
    // 'published' to 'ongoing' after the first response is submitted
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Verify the survey starts with 'published' status
    expect($this->survey->status)->toBe('published');
    
    // Submit a valid response to the survey
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', [
            $this->textQuestion->id => 'John Doe',
            $this->radioQuestion->id => $this->redChoice->id,
            $this->likertQuestion->id => [0 => 'Good', 1 => 'Excellent'],
        ])
        ->set('navAction', 'submit')
        ->call('submit');
    
    // Refresh the survey from the database and verify status changed
    $this->survey->refresh();
    expect($this->survey->status)->toBe('ongoing');
});

it('finishes survey when target respondents is reached', function () {
    // This test verifies that a survey's status automatically changes to 
    // 'finished' when the target number of respondents is reached
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Set target to exactly 1 respondent
    $this->survey->update(['target_respondents' => 1]);
    
    // Submit a response that will reach the target
    Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey])
        ->set('answers', [
            $this->textQuestion->id => 'John Doe',
            $this->radioQuestion->id => $this->redChoice->id,
            $this->likertQuestion->id => [0 => 'Good', 1 => 'Excellent'],
        ])
        ->set('navAction', 'submit')
        ->call('submit');
    
    // Refresh the survey and verify it's now marked as finished
    $this->survey->refresh();
    expect($this->survey->status)->toBe('finished');
});

it('can handle preview mode without saving data', function () {
    // This test verifies that researchers can preview their own surveys
    // without responses being saved to the database
    
    // Login as the survey creator (researcher)
    Auth::login($this->creator);
    
    // Track initial response count
    $initialResponseCount = Response::count();
    
    // Submit in preview mode
    Livewire::test('surveys.answer-survey.answer-survey', [
        'survey' => $this->survey,
        'isPreview' => true // Flag that indicates preview mode
    ])
        ->set('answers', [
            $this->textQuestion->id => 'Preview Test',
            $this->radioQuestion->id => $this->redChoice->id,
            $this->likertQuestion->id => [0 => 'Good', 1 => 'Excellent'],
        ])
        ->set('navAction', 'submit')
        ->call('submit')
        ->assertHasNoErrors() // Preview should validate but not persist
        ->assertRedirect();  // Should redirect after preview
    
    // Verify no response was saved to database (count unchanged)
    expect(Response::count())->toBe($initialResponseCount);
});

it('can navigate between pages in multi-page surveys', function () {
    // This test verifies that users can navigate between pages in multi-page surveys
    // and that their answers are preserved during navigation
    
    // Login as the respondent
    Auth::login($this->user);
    
    // Create a second page to test navigation
    $page2 = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Page 2',
        'order' => 2,
    ]);
    
    // Add a question to the second page
    $page2Question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $page2->id,
        'question_text' => 'Second page question',
        'question_type' => 'short_text',
        'required' => true,
        'order' => 1,
    ]);
    
    // Simulate filling out first page and clicking "Next"
    $component = Livewire::test('surveys.answer-survey.answer-survey', ['survey' => $this->survey->fresh()])
        ->set('answers', [
            $this->textQuestion->id => 'John Doe',
            $this->radioQuestion->id => $this->redChoice->id,
            $this->likertQuestion->id => [0 => 'Good', 1 => 'Excellent'],
        ])
        ->set('navAction', 'next') // Action is "next page" not "submit"
        ->call('submit')  // This triggers page navigation
        ->assertHasNoErrors(); // Should validate without errors
    
    // Verify we're now on page 2 (index 1)
    expect($component->get('currentPage'))->toBe(1);
    
    // Verify navigation action is still "next" (doesn't auto-change)
    expect($component->get('navAction'))->toBe('next');
    
    // Manually set navigation action to "submit" for final submission
    $component->set('navAction', 'submit');
    expect($component->get('navAction'))->toBe('submit');
});