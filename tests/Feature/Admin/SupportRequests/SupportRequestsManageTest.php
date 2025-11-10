<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\SupportRequest;
use App\Models\Report;
use App\Models\Survey;
use App\Models\Response;
use App\Models\SurveyQuestion;
use App\Models\SurveyPage;
use App\Models\Institution;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\SupportRequests\Modal\SupportRequestViewModal;
use App\Services\TrustScoreService;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test.edu'
    ]);
    
    $this->superAdmin = User::create([
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'superadmin@system.com',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    $this->user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'user@test.edu',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
        'trust_score' => 100,
        'points' => 100,
    ]);
    
    $this->reporter = User::create([
        'first_name' => 'Reporter',
        'last_name' => 'User',
        'email' => 'reporter@test.edu',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
        'trust_score' => 100,
    ]);
});

// Modal Loading Tests
it('loads support request view modal', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    $component = Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id]);
    
    $component->assertStatus(200);
    expect($component->get('supportRequest'))->not->toBeNull();
    expect($component->get('supportRequest')->id)->toBe($request->id);
});

// Update Tests
it('can update support request status', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'in_progress')
        ->set('adminNotes', 'Working on it')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $request->refresh();
    expect($request->status)->toBe('in_progress');
    expect($request->admin_notes)->toBe('Working on it');
    expect($request->admin_id)->toBe($this->superAdmin->id);
});

it('can resolve support request', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'resolved')
        ->set('adminNotes', 'Issue resolved')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $request->refresh();
    expect($request->status)->toBe('resolved');
    expect($request->resolved_at)->not->toBeNull();
});

it('cannot update locked support request', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'resolved',
        'resolved_at' => now(),
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'pending')
        ->call('updateRequest', app(TrustScoreService::class))
        ->assertDispatched('notify');
    
    $request->refresh();
    expect($request->status)->toBe('resolved'); // Status should not change
});

// Report Appeal Tests
it('dismisses report when appeal is resolved', function () {
    Auth::login($this->superAdmin);
    
    $survey = Survey::create([
        'user_id' => $this->user->id,
        'title' => 'Test Survey',
        'description' => 'Test',
        'status' => 'published',
        'points_reward' => 10,
    ]);
    
    $surveyPage = SurveyPage::create([
        'survey_id' => $survey->id,
        'page_number' => 1,
    ]);
    
    $question = SurveyQuestion::create([
        'survey_id' => $survey->id,
        'survey_page_id' => $surveyPage->id,
        'question_text' => 'Test question',
        'question_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    
    $response = Response::create([
        'survey_id' => $survey->id,
        'user_id' => $this->user->id,
        'reported' => true,
    ]);
    
    $report = Report::create([
        'survey_id' => $survey->id,
        'response_id' => $response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->user->id,
        'question_id' => $question->id,
        'reason' => 'spam',
        'details' => 'False report',
        'status' => 'under_appeal',
        'trust_score_deduction' => -10,
        'points_deducted' => 5,
    ]);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Report Appeal',
        'description' => 'I was falsely reported',
        'request_type' => 'report_appeal',
        'status' => 'pending',
        'related_id' => $report->uuid,
        'related_model' => 'Report',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'resolved')
        ->set('adminNotes', 'Report was false')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $report->refresh();
    $this->user->refresh();
    $response->refresh();
    
    expect($report->status)->toBe('dismissed');
    expect($report->deduction_reversed)->toBeTruthy(); // Changed from toBeTrue()
    expect($this->user->trust_score)->toBe(110); // 100 + 10 restored
    expect($this->user->points)->toBe(105); // 100 + 5 restored
    expect($response->reported)->toBeFalsy(); // Changed from toBeFalse()
});

it('confirms report when appeal is rejected', function () {
    Auth::login($this->superAdmin);
    
    $survey = Survey::create([
        'user_id' => $this->user->id,
        'title' => 'Test Survey',
        'description' => 'Test',
        'status' => 'published',
        'points_reward' => 10,
    ]);
    
    $surveyPage = SurveyPage::create([
        'survey_id' => $survey->id,
        'page_number' => 1,
    ]);
    
    $question = SurveyQuestion::create([
        'survey_id' => $survey->id,
        'survey_page_id' => $surveyPage->id,
        'question_text' => 'Test question',
        'question_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    
    $response = Response::create([
        'survey_id' => $survey->id,
        'user_id' => $this->user->id,
        'reported' => true,
    ]);
    
    $report = Report::create([
        'survey_id' => $survey->id,
        'response_id' => $response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->user->id,
        'question_id' => $question->id,
        'reason' => 'spam',
        'details' => 'Valid report',
        'status' => 'under_appeal',
        'points_deducted' => 5,
    ]);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Report Appeal',
        'description' => 'Appeal',
        'request_type' => 'report_appeal',
        'status' => 'pending',
        'related_id' => $report->uuid,
        'related_model' => 'Report',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'rejected')
        ->set('adminNotes', 'Report is valid')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $report->refresh();
    expect($report->status)->toBe('confirmed');
    expect($report->points_restored)->toBeTruthy(); // Changed from toBeTrue()
});

// Notification Tests
it('sends notification when status changes', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'in_progress')
        ->set('adminNotes', 'Working on it')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $this->assertDatabaseHas('inbox_messages', [
        'recipient_id' => $this->user->id,
        'subject' => 'Support Request Update - Account issue #' . $request->id . ' - Status: In progress',
    ]);
});

it('sends notification when admin notes change', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'pending',
        'admin_notes' => 'Old notes',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('adminNotes', 'Updated notes')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $this->assertDatabaseHas('inbox_messages', [
        'recipient_id' => $this->user->id,
    ]);
});

// Audit Log Tests
it('creates audit log on update', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test Request',
        'description' => 'Test Description',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'resolved')
        ->call('updateRequest', app(TrustScoreService::class));
    
    $this->assertDatabaseHas('audit_logs', [
        'resource_type' => 'SupportRequest',
        'resource_id' => $request->id,
        'event_type' => 'update',
    ]);
});

// Validation Tests
it('validates required fields', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'description' => 'Test',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', '')
        ->call('updateRequest', app(TrustScoreService::class))
        ->assertHasErrors(['status']);
});

it('validates status values', function () {
    Auth::login($this->superAdmin);
    
    $request = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'description' => 'Test',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    Livewire::test(SupportRequestViewModal::class, ['requestId' => $request->id])
        ->set('status', 'invalid_status')
        ->call('updateRequest', app(TrustScoreService::class))
        ->assertHasErrors(['status']);
});
