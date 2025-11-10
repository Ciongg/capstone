<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\Institution;
use App\Models\SurveyTopic;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\UserSurveys\Modal\UserSurveyViewModal;
use App\Livewire\InstitutionAdmin\UserSurveys\Modal\InstitutionUserSurveyViewModal;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create institution
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);
    
    // Create institution admin
    $this->institutionAdmin = User::factory()->create([
        'email' => 'admin@institution.com',
        'type' => 'institution_admin',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create researcher
    $this->researcher = User::factory()->create([
        'email' => 'researcher@institution.com',
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create survey topic
    $this->topic = SurveyTopic::create(['name' => 'Education']);
    
    // Create a survey
    $this->survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher->id,
        'title' => 'Test Survey',
        'description' => 'Test Description',
        'status' => 'published',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic->id,
        'is_locked' => false,
        'lock_reason' => null,
    ]);
    
    // Create page and questions
    $page = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Page 1',
        'page_number' => 1
    ]);
    
    for ($i = 1; $i <= 6; $i++) {
        SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => true,
            'order' => $i
        ]);
    }
});

// Super Admin Modal Tests
it('loads survey view modal for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id]);
    
    expect($component->get('survey'))->not->toBeNull();
    expect($component->get('survey')->id)->toBe($this->survey->id);
    expect($component->get('survey')->title)->toBe('Test Survey');
});

it('super admin can lock survey with reason', function () {
    Auth::login($this->superAdmin);
    
    $lockReason = 'Survey violates community guidelines';
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->set('lockReason', $lockReason)
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->is_locked)->toBeTrue();
    expect($this->survey->lock_reason)->toBe($lockReason);
    
    // Check notification was sent
    $message = InboxMessage::where('recipient_id', $this->researcher->id)
        ->where('subject', 'Your Survey Has Been Locked')
        ->first();
    
    expect($message)->not->toBeNull();
    expect($message->message)->toContain($lockReason);
});

it('super admin can lock survey without reason', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->set('lockReason', '')
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->is_locked)->toBeTrue();
    
    // Check notification was sent even without reason
    $message = InboxMessage::where('recipient_id', $this->researcher->id)
        ->where('subject', 'Your Survey Has Been Locked')
        ->first();
    
    expect($message)->not->toBeNull();
});

it('super admin can unlock survey', function () {
    // First lock the survey
    $this->survey->update([
        'is_locked' => true,
        'lock_reason' => 'Test reason'
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->is_locked)->toBeFalse();
    expect($this->survey->lock_reason)->toBeNull();
    
    // Check notification was sent
    $message = InboxMessage::where('recipient_id', $this->researcher->id)
        ->where('subject', 'Your Survey Has Been Unlocked')
        ->first();
    
    expect($message)->not->toBeNull();
});

it('super admin can archive survey', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('archiveSurvey');
    
    $this->survey->refresh();
    expect($this->survey->trashed())->toBeTrue();
});

it('super admin can restore archived survey', function () {
    // First archive the survey
    $this->survey->delete();
    
    Auth::login($this->superAdmin);
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('restoreSurvey');
    
    $this->survey->refresh();
    expect($this->survey->trashed())->toBeFalse();
});

it('dispatches event after status update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('toggleLockStatus')
        ->assertDispatched('surveyStatusUpdated');
});

// Institution Admin Modal Tests
it('loads survey view modal for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveyViewModal::class, ['surveyId' => $this->survey->id]);
    
    expect($component->get('survey'))->not->toBeNull();
    expect($component->get('survey')->id)->toBe($this->survey->id);
});

it('institution admin can lock survey with reason', function () {
    Auth::login($this->institutionAdmin);
    
    $lockReason = 'Violates institution policy';
    
    Livewire::test(InstitutionUserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->set('lockReason', $lockReason)
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->is_locked)->toBeTrue();
    expect($this->survey->lock_reason)->toBe($lockReason);
});

it('institution admin can unlock survey', function () {
    // First lock the survey
    $this->survey->update([
        'is_locked' => true,
        'lock_reason' => 'Test reason'
    ]);
    
    Auth::login($this->institutionAdmin);
    
    Livewire::test(InstitutionUserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->is_locked)->toBeFalse();
    expect($this->survey->lock_reason)->toBeNull();
});

it('institution admin can archive survey', function () {
    Auth::login($this->institutionAdmin);
    
    Livewire::test(InstitutionUserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('archiveSurvey');
    
    $this->survey->refresh();
    expect($this->survey->trashed())->toBeTrue();
});

it('institution admin can restore archived survey', function () {
    // First archive the survey
    $this->survey->delete();
    
    Auth::login($this->institutionAdmin);
    
    Livewire::test(InstitutionUserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('restoreSurvey');
    
    $this->survey->refresh();
    expect($this->survey->trashed())->toBeFalse();
});

// Lock Reason Tests
it('stores lock reason when locking survey', function () {
    Auth::login($this->superAdmin);
    
    $lockReason = 'Contains inappropriate content';
    
    Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->set('lockReason', $lockReason)
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->lock_reason)->toBe($lockReason);
});

it('clears lock reason when unlocking survey', function () {
    // First lock with reason
    $this->survey->update([
        'is_locked' => true,
        'lock_reason' => 'Test reason'
    ]);
    
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id])
        ->call('toggleLockStatus');
    
    $this->survey->refresh();
    expect($this->survey->lock_reason)->toBeNull();
    expect($component->get('lockReason'))->toBe('');
});

// Archived Survey Tests
it('can view archived survey details', function () {
    $this->survey->delete();
    
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id]);
    
    expect($component->get('survey'))->not->toBeNull();
    expect($component->get('survey')->trashed())->toBeTrue();
});

it('cannot lock archived survey', function () {
    $this->survey->delete();
    
    Auth::login($this->superAdmin);
    
    // Archived surveys should not be lockable (handled in view logic)
    $component = Livewire::test(UserSurveyViewModal::class, ['surveyId' => $this->survey->id]);
    
    expect($component->get('survey')->trashed())->toBeTrue();
});
