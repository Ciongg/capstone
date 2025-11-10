<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Report;
use App\Models\Answer;
use App\Models\Institution;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice; // Changed from SurveyQuestionChoice
use App\Models\SurveyPage;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Reports\Modal\ViewReportModal;

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
    
    $this->researcher = User::create([
        'first_name' => 'Test',
        'last_name' => 'Researcher',
        'email' => 'researcher@test.edu',
        'password' => bcrypt('password'),
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    $this->respondent = User::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@test.edu',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    $this->reporter = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Reporter',
        'email' => 'jane@test.edu',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    $this->survey = Survey::create([
        'user_id' => $this->researcher->id,
        'title' => 'Test Survey',
        'description' => 'Test Description',
        'status' => 'published',
        'points_reward' => 10,
    ]);
    
    $this->surveyPage = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'page_number' => 1,
    ]);
    
    $this->essayQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->surveyPage->id,
        'question_text' => 'What is your opinion?',
        'question_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    
    $this->response = Response::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->respondent->id,
        'reported' => true,
    ]);
});

// Modal Loading Tests
it('loads view report modal', function () {
    Auth::login($this->superAdmin);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->essayQuestion->id,
        'reason' => 'inappropriate_content',
        'details' => 'Test details',
        'status' => 'unappealed',
    ]);
    
    $component = Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    $component->assertStatus(200);
    expect($component->get('report'))->not->toBeNull();
    expect($component->get('report')->id)->toBe($report->id);
});

it('displays report basic information', function () {
    Auth::login($this->superAdmin);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->essayQuestion->id,
        'reason' => 'inappropriate_content',
        'details' => 'Contains bad words',
        'status' => 'unappealed',
    ]);
    
    Livewire::test(ViewReportModal::class, ['reportId' => $report->id])
        ->assertSee('Test Survey')
        ->assertSee('Inappropriate content') // Changed from 'inappropriate_content'
        ->assertSee('Contains bad words');
});

// Answer Display Tests - Essay
it('displays essay answer correctly', function () {
    Auth::login($this->superAdmin);
    
    $answer = Answer::create([
        'response_id' => $this->response->id,
        'survey_question_id' => $this->essayQuestion->id,
        'answer' => 'This is my essay response with inappropriate content',
    ]);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->essayQuestion->id,
        'reason' => 'inappropriate_content',
        'details' => 'Bad content',
        'status' => 'unappealed',
    ]);
    
    $component = Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    expect($component->get('processedAnswer')['display_answer'])
        ->toBe('This is my essay response with inappropriate content');
});

// Answer Display Tests - Multiple Choice
it('displays multiple choice answer correctly', function () {
    Auth::login($this->superAdmin);
    
    $mcQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->surveyPage->id,
        'question_text' => 'Select all that apply',
        'question_type' => 'multiple_choice',
        'is_required' => true,
        'order' => 2,
    ]);
    
    $choice1 = SurveyChoice::create([ // Changed from SurveyQuestionChoice
        'survey_question_id' => $mcQuestion->id,
        'choice_text' => 'Option 1',
        'order' => 1,
    ]);
    
    $choice2 = SurveyChoice::create([ // Changed from SurveyQuestionChoice
        'survey_question_id' => $mcQuestion->id,
        'choice_text' => 'Option 2',
        'order' => 2,
    ]);
    
    $answer = Answer::create([
        'response_id' => $this->response->id,
        'survey_question_id' => $mcQuestion->id,
        'answer' => json_encode([$choice1->id, $choice2->id]),
    ]);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $mcQuestion->id,
        'reason' => 'spam',
        'details' => 'Spam answer',
        'status' => 'unappealed',
    ]);
    
    $component = Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    expect($component->get('processedAnswer')['display_answer'])
        ->toBe('Option 1, Option 2');
});

// Answer Display Tests - Radio
it('displays radio answer correctly', function () {
    Auth::login($this->superAdmin);
    
    $radioQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->surveyPage->id,
        'question_text' => 'Choose one',
        'question_type' => 'radio',
        'is_required' => true,
        'order' => 2,
    ]);
    
    $choice = SurveyChoice::create([ // Changed from SurveyQuestionChoice
        'survey_question_id' => $radioQuestion->id,
        'choice_text' => 'Selected Option',
        'order' => 1,
    ]);
    
    $answer = Answer::create([
        'response_id' => $this->response->id,
        'survey_question_id' => $radioQuestion->id,
        'answer' => (string)$choice->id,
    ]);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $radioQuestion->id,
        'reason' => 'offensive',
        'details' => 'Offensive choice',
        'status' => 'unappealed',
    ]);
    
    $component = Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    expect($component->get('processedAnswer')['display_answer'])
        ->toBe('Selected Option');
});

// Answer Display Tests - Rating
it('displays rating answer correctly', function () {
    Auth::login($this->superAdmin);
    
    $ratingQuestion = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->surveyPage->id,
        'question_text' => 'Rate this',
        'question_type' => 'rating',
        'is_required' => true,
        'order' => 2,
        'stars' => 5,
    ]);
    
    $answer = Answer::create([
        'response_id' => $this->response->id,
        'survey_question_id' => $ratingQuestion->id,
        'answer' => '4',
    ]);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $ratingQuestion->id,
        'reason' => 'suspicious',
        'details' => 'Suspicious rating',
        'status' => 'unappealed',
    ]);
    
    $component = Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    expect($component->get('processedAnswer')['display_answer'])
        ->toBe('4 out of 5 stars');
});

// User Information Display
it('displays reporter and respondent information', function () {
    Auth::login($this->superAdmin);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->essayQuestion->id,
        'reason' => 'spam',
        'details' => 'Test',
        'status' => 'unappealed',
    ]);
    
    $component = Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    expect($component->get('report')->reporter->id)->toBe($this->reporter->id);
    expect($component->get('report')->respondent->id)->toBe($this->respondent->id);
});

// Audit Log Test
it('creates audit log when viewing report', function () {
    Auth::login($this->superAdmin);
    
    $report = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->essayQuestion->id,
        'reason' => 'inappropriate_content',
        'details' => 'Test',
        'status' => 'unappealed',
    ]);
    
    Livewire::test(ViewReportModal::class, ['reportId' => $report->id]);
    
    $this->assertDatabaseHas('audit_logs', [
        'event_type' => 'view',
        'resource_type' => 'Report',
        'resource_id' => $report->id,
    ]);
});
