<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use App\Models\SupportRequest;
use App\Models\Survey;
use App\Models\Report;
use App\Models\Response;
use App\Livewire\SupportRequests\CreateSupportRequestModal;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test institution
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test-university.edu'
    ]);

    // Create test users
    $this->researcher = User::create([
        'uuid' => Str::uuid()->toString(),
        'email' => 'researcher@example.com',
        'first_name' => 'Test',
        'last_name' => 'Researcher',
        'password' => bcrypt('password'),
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);

    $this->respondent = User::create([
        'uuid' => Str::uuid()->toString(),
        'email' => 'respondent@example.com',
        'first_name' => 'Test',
        'last_name' => 'Respondent',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);

    // Create test survey
    $this->survey = Survey::create([
        'uuid' => Str::uuid()->toString(),
        'user_id' => $this->researcher->id,
        'title' => 'Test Survey',
        'description' => 'Test survey description',
        'is_locked' => false,
    ]);

    // Create locked survey
    $this->lockedSurvey = Survey::create([
        'uuid' => Str::uuid()->toString(),
        'user_id' => $this->researcher->id,
        'title' => 'Locked Survey',
        'description' => 'This survey is locked',
        'is_locked' => true,
    ]);

    $this->response = Response::create([
        'uuid' => Str::uuid()->toString(),
        'survey_id' => $this->survey->id,
        'user_id' => $this->respondent->id,
        'reported' => false,
    ]);

    // Create test report
    $this->report = Report::create([
        'uuid' => Str::uuid()->toString(),
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->researcher->id,
        'respondent_id' => $this->respondent->id,
        'reason' => 'Test report reason',
        'description' => 'Test report description',
        'details' => 'Additional report details',
        'status' => Report::STATUS_UNAPPEALED,
        'trust_score_deduction' => 0,
        'deduction_reversed' => false,
        'reporter_trust_score_deduction' => 0,
        'points_deducted' => 0,
        'points_restored' => false,
    ]);
});

it('can render the create support request modal', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->assertSuccessful()
        ->assertSee('Subject')
        ->assertSee('Description')
        ->assertSee('Request Type');
});

it('validates required fields', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', '')
        ->set('description', '')
        ->set('request_type', '')
        ->call('submitRequest')
        ->assertHasErrors(['subject', 'description', 'request_type']);
});

it('validates minimum subject length', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Test')
        ->set('description', 'This is a valid description with enough characters')
        ->set('request_type', 'other')
        ->call('submitRequest')
        ->assertHasErrors(['subject']);
});

it('validates minimum description length', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Valid Subject')
        ->set('description', 'Too short')
        ->set('request_type', 'other')
        ->call('submitRequest')
        ->assertHasErrors(['description']);
});

it('validates request type is in allowed values', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Valid Subject')
        ->set('description', 'This is a valid description with enough characters')
        ->set('request_type', 'invalid_type')
        ->call('submitRequest')
        ->assertHasErrors(['request_type']);
});

it('allows creating a general support request', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'General Support Request')
        ->set('description', 'I need help with a general issue that requires assistance')
        ->set('request_type', 'other')
        ->call('submitRequest');
    
    $supportRequest = SupportRequest::latest()->first();
    expect($supportRequest->subject)->toBe('General Support Request');
    expect($supportRequest->description)->toBe('I need help with a general issue that requires assistance');
    expect($supportRequest->request_type)->toBe('other');
    expect($supportRequest->user_id)->toBe($this->researcher->id);
    expect($supportRequest->status)->toBe('pending');
});

it('allows creating an account issue request', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Account Issue')
        ->set('description', 'I am having trouble accessing my account settings')
        ->set('request_type', 'account_issue')
        ->call('submitRequest');
    
    $supportRequest = SupportRequest::latest()->first();
    expect($supportRequest->request_type)->toBe('account_issue');
    expect($supportRequest->related_id)->toBeNull();
    expect($supportRequest->related_model)->toBeNull();
});

it('allows creating a survey question request', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Survey Question')
        ->set('description', 'I have a question about creating surveys and their features')
        ->set('request_type', 'survey_question')
        ->call('submitRequest');
    
    $supportRequest = SupportRequest::latest()->first();
    expect($supportRequest->request_type)->toBe('survey_question');
});

it('requires survey ID for survey lock appeals', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Survey Lock Appeal')
        ->set('description', 'I need to appeal the lock on my survey for valid reasons')
        ->set('request_type', 'survey_lock_appeal')
        ->set('related_id', null)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('validates survey ownership for lock appeals', function () {
    $otherUser = User::create([
        'uuid' => Str::uuid()->toString(),
        'email' => 'other@example.com',
        'first_name' => 'Other',
        'last_name' => 'User',
        'password' => bcrypt('password'),
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);

    $this->actingAs($otherUser);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Survey Lock Appeal')
        ->set('description', 'I need to appeal the lock on this survey but I do not own it')
        ->set('request_type', 'survey_lock_appeal')
        ->set('related_id', (string) $this->lockedSurvey->uuid)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('validates survey is actually locked for lock appeals', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Survey Lock Appeal')
        ->set('description', 'I need to appeal the lock on my survey which is not locked')
        ->set('request_type', 'survey_lock_appeal')
        ->set('related_id', (string) $this->survey->uuid)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('allows creating a valid survey lock appeal', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Survey Lock Appeal')
        ->set('description', 'I need to appeal the lock on my survey because of important reasons')
        ->set('request_type', 'survey_lock_appeal')
        ->set('related_id', (string) $this->lockedSurvey->uuid)
        ->call('submitRequest');
    
    $supportRequest = SupportRequest::latest()->first();
    expect($supportRequest->request_type)->toBe('survey_lock_appeal');
    expect($supportRequest->related_id)->toBe($this->lockedSurvey->uuid);
    expect($supportRequest->related_model)->toBe('Survey');
    expect($supportRequest->user_id)->toBe($this->researcher->id);
});

it('requires report ID for report appeals', function () {
    $this->actingAs($this->respondent);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this report against me for valid reasons')
        ->set('request_type', 'report_appeal')
        ->set('related_id', null)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('validates report exists and user is the respondent for appeals', function () {
    $this->actingAs($this->researcher);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this report but I am not the respondent')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('allows creating a valid report appeal', function () {
    $this->actingAs($this->respondent);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this report against me because it is unjustified')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest');
    
    $supportRequest = SupportRequest::latest()->first();
    expect($supportRequest->request_type)->toBe('report_appeal');
    expect($supportRequest->related_id)->toBe($this->report->uuid);
    expect($supportRequest->related_model)->toBe('Report');
    expect($supportRequest->user_id)->toBe($this->respondent->id);
});

it('marks report as under appeal when appeal is submitted', function () {
    $this->actingAs($this->respondent);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this report against me because it is unjustified')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest');
    
    $this->report->refresh();
    expect($this->report->isUnderAppeal())->toBeTrue();
});

it('prevents duplicate appeals for the same report', function () {
    // First appeal
    $this->actingAs($this->respondent);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this report against me because it is unjustified')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest');
    
    // Try to submit another appeal for the same report
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Another Report Appeal')
        ->set('description', 'I am trying to submit another appeal for the same report')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('prevents appeals for already confirmed reports', function () {
    $this->report->update(['status' => Report::STATUS_CONFIRMED]);
    
    $this->actingAs($this->respondent);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this confirmed report against me')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('prevents appeals for already dismissed reports', function () {
    $this->report->update(['status' => Report::STATUS_DISMISSED]);
    
    $this->actingAs($this->respondent);
    
    Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Report Appeal')
        ->set('description', 'I need to appeal this dismissed report against me')
        ->set('request_type', 'report_appeal')
        ->set('related_id', (string) $this->report->uuid)
        ->call('submitRequest')
        ->assertHasErrors(['related_id']);
});

it('resets form after successful submission', function () {
    $this->actingAs($this->researcher);
    
    $component = Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Test Subject')
        ->set('description', 'This is a test description with enough characters')
        ->set('request_type', 'other')
        ->call('submitRequest');
    
    expect($component->get('subject'))->toBe('');
    expect($component->get('description'))->toBe('');
    expect($component->get('request_type'))->toBe('');
    expect($component->get('showSuccess'))->toBeTrue();
});

it('shows success message after submission', function () {
    $this->actingAs($this->researcher);
    
    $component = Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Test Subject')
        ->set('description', 'This is a test description with enough characters')
        ->set('request_type', 'other')
        ->call('submitRequest');
    
    expect($component->get('showSuccess'))->toBeTrue();
    expect($component->get('message'))->toContain('submitted successfully');
});

it('resets all fields when modal is closed', function () {
    $this->actingAs($this->researcher);
    
    $component = Livewire::test(CreateSupportRequestModal::class)
        ->set('subject', 'Test Subject')
        ->set('description', 'This is a test description')
        ->set('request_type', 'other')
        ->set('showSuccess', true)
        ->call('closeModal');
    
    expect($component->get('subject'))->toBe('');
    expect($component->get('description'))->toBe('');
    expect($component->get('request_type'))->toBe('');
    expect($component->get('showSuccess'))->toBeFalse();
});
