<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\Response;
use App\Models\Report;
use App\Models\Institution;
use App\Models\SurveyTopic;
use App\Models\InboxMessage;
use App\Services\TrustScoreService;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Surveys\FormResponses\Modal\ViewReportResponseModal;

// Ensures database is reset between tests for isolation
uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an institution
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);
    
    // Create a survey topic
    $this->surveyTopic = SurveyTopic::create([
        'name' => 'Test Topic',
        'description' => 'A test survey topic'
    ]);
    
    // Create a researcher (survey owner) who will report responses
    $this->researcher = User::factory()->create([
        'email' => 'researcher@example.com',
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create a respondent who submitted the survey
    $this->respondent = User::factory()->create([
        'email' => 'respondent@example.com',
        'type' => 'respondent',
        'is_active' => true,
        'trust_score' => 100,
        'points' => 50,
    ]);
    
    // Create a published survey
    $this->survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Test Survey',
        'description' => 'A test survey for reporting',
        'user_id' => $this->researcher->id,
        'survey_topic_id' => $this->surveyTopic->id,
        'status' => 'published',
        'points_allocated' => 10,
        'target_respondents' => 100,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
    ]);
    
    // Create survey pages and questions
    $this->page = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Page 1',
        'order' => 1,
    ]);
    
    $this->question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Test Question',
        'question_type' => 'short_text',
        'required' => true,
        'order' => 1,
    ]);
    
    // Create a response to be reported
    $this->response = Response::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'survey_id' => $this->survey->id,
        'user_id' => $this->respondent->id,
        'reported' => false,
    ]);
    
    // Create a snapshot for the response
    $this->response->snapshot()->create([
        'first_name' => $this->respondent->first_name,
        'last_name' => $this->respondent->last_name,
        'trust_score' => $this->respondent->trust_score,
        'points' => $this->respondent->points,
        'account_level' => 1,
        'experience_points' => 200,
        'rank' => 'silver',
        'started_at' => now()->subMinutes(10),
        'completed_at' => now()->subMinutes(5),
        'completion_time_seconds' => 300,
    ]);
    
    // Create an answer for the response
    $this->response->answers()->create([
        'survey_question_id' => $this->question->id,
        'answer' => 'This is a potentially problematic response',
    ]);
});

it('can render the report response modal', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Test the Livewire component mounts correctly with the provided response and survey
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ]);
    
    // Verify the component initializes correctly
    expect($component->get('response'))->toBeInstanceOf(Response::class);
    expect($component->get('survey'))->toBeInstanceOf(Survey::class);
    
    // Verify questions were loaded properly
    $questions = $component->get('questions');
    expect($questions)->toBeArray();
    expect(count($questions))->toBe(1);
    expect($questions[0]['display'])->toContain('Test Question');
});

it('requires reason and details for report submission', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Test with missing reason
    Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('details', 'This response contains inappropriate content')
    ->call('submitReport')
    ->assertHasErrors(['reason']);
    
    // Test with missing details
    Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->call('submitReport')
    ->assertHasErrors(['details']);
    
    // Test with short details
    Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'Too short')
    ->call('submitReport')
    ->assertHasErrors(['details']);
});

it('shows confirmation screen before finalizing report', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This response contains inappropriate content that violates survey guidelines.')
    ->call('submitReport');
    
    // Verify confirmation screen is shown
    expect($component->get('showConfirmation'))->toBeTrue();
    expect($component->get('showSuccess'))->toBeFalse();
});

it('can successfully report a response', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Initial report count
    $initialReportCount = Report::count();
    $initialInboxCount = InboxMessage::count();
    
    // Test the full reporting process (submission + confirmation)
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This response contains inappropriate content that violates guidelines.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->call('confirmReport');
    
    // Verify report was created
    expect(Report::count())->toBe($initialReportCount + 1);
    
    // Verify the report data is correct
    $report = Report::latest()->first();
    expect($report->survey_id)->toBe($this->survey->id);
    expect($report->response_id)->toBe($this->response->id);
    expect($report->reporter_id)->toBe($this->researcher->id);
    expect($report->respondent_id)->toBe($this->respondent->id);
    expect($report->reason)->toBe('inappropriate_content');
    expect($report->details)->toBe('This response contains inappropriate content that violates guidelines.');
    expect($report->status)->toBe('unappealed');
    
    // Verify the response was marked as reported
    $this->response->refresh();
    expect($this->response->reported)->toBeTrue();
    
    // Verify inbox notification was sent to respondent
    expect(InboxMessage::count())->toBe($initialInboxCount + 1);
    $message = InboxMessage::latest()->first();
    expect($message->recipient_id)->toBe($this->respondent->id);
    expect($message->subject)->toBe('Your Survey Response Has Been Reported');
    // Fix: Use case-insensitive check for the content
    expect(strtolower($message->message))->toContain('inappropriate content');
    expect($message->message)->toContain($report->uuid);
    
    // Verify the component shows success message
    expect($component->get('showSuccess'))->toBeTrue();
    expect($component->get('isError'))->toBeFalse();
    expect($component->get('message'))->toContain('reported successfully');
});

it('does not allow duplicate reports on the same response', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Create an existing report
    Report::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->researcher->id,
        'respondent_id' => $this->respondent->id,
        'reason' => 'spam',
        'details' => 'Previous report',
        'status' => 'unappealed',
    ]);
    
    // Initial report count
    $initialReportCount = Report::count();
    
    // Try to report the same response again
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This response contains inappropriate content that violates guidelines.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->call('confirmReport');
    
    // Verify no new report was created
    expect(Report::count())->toBe($initialReportCount);
    
    // Verify error message is shown
    expect($component->get('showSuccess'))->toBeTrue();
    expect($component->get('isError'))->toBeTrue();
    expect($component->get('message'))->toContain('already been reported');
});

it('does not allow multiple reports against same user on same survey', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Create a first response and report it
    $firstResponse = Response::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'survey_id' => $this->survey->id,
        'user_id' => $this->respondent->id,
        'reported' => true,
    ]);
    
    Report::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'survey_id' => $this->survey->id,
        'response_id' => $firstResponse->id,
        'reporter_id' => $this->researcher->id,
        'respondent_id' => $this->respondent->id,
        'reason' => 'spam',
        'details' => 'Previous report',
        'status' => 'unappealed',
    ]);
    
    // Create a second response from same respondent
    $secondResponse = Response::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'survey_id' => $this->survey->id,
        'user_id' => $this->respondent->id,
        'reported' => false,
    ]);
    
    // Initial report count
    $initialReportCount = Report::count();
    
    // Try to report the second response from the same respondent on the same survey
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $secondResponse,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This is another problematic response.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->call('confirmReport');
    
    // Verify no new report was created
    expect(Report::count())->toBe($initialReportCount);
    
    // Verify error message is shown
    expect($component->get('showSuccess'))->toBeTrue();
    expect($component->get('isError'))->toBeTrue();
    expect($component->get('message'))->toContain('already reported this user');
});

it('applies trust score deductions based on report count', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Track initial trust score
    $initialTrustScore = $this->respondent->trust_score;
    
    // Create two previous reports to trigger trust score deduction
    for ($i = 0; $i < 2; $i++) {
        $prevResponse = Response::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'survey_id' => $this->survey->id,
            'user_id' => $this->respondent->id,
            'reported' => true,
        ]);
        
        Report::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'survey_id' => $this->survey->id,
            'response_id' => $prevResponse->id,
            'reporter_id' => User::factory()->create()->id, // Different reporter
            'respondent_id' => $this->respondent->id,
            'reason' => 'spam',
            'details' => 'Previous report ' . ($i + 1),
            'status' => 'unappealed',
            'trust_score_deduction' => -5,
        ]);
    }
    
    // This will be the 3rd report, which should trigger more significant deduction
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This response contains inappropriate content that violates guidelines.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->call('confirmReport');
    
    // Verify trust score was deducted
    $this->respondent->refresh();
    expect($this->respondent->trust_score)->toBeLessThan($initialTrustScore);
    
    // Get the report to check the deduction
    $report = Report::latest()->first();
    expect($report->trust_score_deduction)->toBeLessThan(0);
});

it('deducts survey points when a response is reported', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Track initial points
    $initialPoints = $this->respondent->points;
    
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This response contains inappropriate content that violates guidelines.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->call('confirmReport');
    
    // Verify points were deducted
    $this->respondent->refresh();
    expect($this->respondent->points)->toBe($initialPoints - $this->survey->points_allocated);
    
    // Verify points deduction was recorded in report
    $report = Report::latest()->first();
    expect($report->points_deducted)->toBe($this->survey->points_allocated);
    // Fix: Check for 0 instead of false since the database column is likely an integer
    expect($report->points_restored)->toBe(0);
});

it('can report with specific question reference', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('questionId', $this->question->id)
    ->set('details', 'This answer to the question contains inappropriate content.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->assertSet('selectedQuestionText', 'Q1. Test Question')
    ->call('confirmReport');
    
    // Verify report includes question reference
    $report = Report::latest()->first();
    expect($report->question_id)->toBe($this->question->id);
});

it('can cancel report at confirmation screen', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    // Initial report count
    $initialReportCount = Report::count();
    
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'This response contains inappropriate content.')
    ->call('submitReport')
    ->assertSet('showConfirmation', true)
    ->call('cancelConfirmation');
    
    // Verify confirmation was dismissed
    expect($component->get('showConfirmation'))->toBeFalse();
    
    // Verify no report was created
    expect(Report::count())->toBe($initialReportCount);
});

it('can close modal and reset form', function () {
    // Login as the researcher
    Auth::login($this->researcher);
    
    $component = Livewire::test(ViewReportResponseModal::class, [
        'response' => $this->response,
        'survey' => $this->survey
    ])
    ->set('reason', 'inappropriate_content')
    ->set('details', 'Test details')
    ->set('questionId', $this->question->id)
    ->set('showSuccess', true)
    ->call('closeModal');
    
    // Verify all fields are reset
    expect($component->get('reason'))->toBe('');
    expect($component->get('details'))->toBe('');
    expect($component->get('questionId'))->toBeNull();
    expect($component->get('showSuccess'))->toBeFalse();
    expect($component->get('showConfirmation'))->toBeFalse();
    
    // Verify the modal close event was dispatched
    $component->assertDispatched('close-modal', function ($event, $params) {
        return $params['name'] === 'view-report-response-modal';
    });
});
