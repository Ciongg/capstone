<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Report;
use App\Models\Institution;
use App\Models\SurveyQuestion;
use App\Models\SurveyPage;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Reports\ReportIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create institution
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test.edu'
    ]);
    
    // Create super admin
    $this->superAdmin = User::create([
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'superadmin@system.com',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create researcher
    $this->researcher = User::create([
        'first_name' => 'Test',
        'last_name' => 'Researcher',
        'email' => 'researcher@test.edu',
        'password' => bcrypt('password'),
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create respondents
    $this->respondent = User::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@test.edu',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
        'trust_score' => 100,
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
    
    // Create survey
    $this->survey = Survey::create([
        'user_id' => $this->researcher->id,
        'title' => 'Test Survey',
        'description' => 'Test Description',
        'status' => 'published',
        'points_reward' => 10,
    ]);
    
    // Create survey page
    $this->surveyPage = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'page_number' => 1,
    ]);
    
    // Create question
    $this->question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->surveyPage->id,
        'question_text' => 'What is your opinion?',
        'question_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    
    // Create response
    $this->response = Response::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->respondent->id,
        'reported' => true,
    ]);
    
    // Create reports with different reasons
    $this->inappropriateReport = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->question->id,
        'reason' => 'inappropriate_content',
        'details' => 'Contains inappropriate content',
        'status' => 'unappealed',
    ]);
    
    $this->spamReport = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->question->id,
        'reason' => 'spam',
        'details' => 'This is spam',
        'status' => 'unappealed',
    ]);
    
    $this->offensiveReport = Report::create([
        'survey_id' => $this->survey->id,
        'response_id' => $this->response->id,
        'reporter_id' => $this->reporter->id,
        'respondent_id' => $this->respondent->id,
        'question_id' => $this->question->id,
        'reason' => 'offensive',
        'details' => 'Offensive language',
        'status' => 'dismissed',
    ]);
});

// Page Loading Tests
it('loads reports list page', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class);
    
    $component->assertStatus(200);
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(3);
});

it('displays all reports by default', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class);
    $reports = $component->viewData('reports');
    
    expect($reports->total())->toBe(3);
    $reasons = $reports->pluck('reason')->toArray();
    expect($reasons)->toContain('inappropriate_content', 'spam', 'offensive');
});

// Filter Tests
it('filters reports by inappropriate content', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->call('filterByReason', 'inappropriate_content')
        ->assertSet('reasonFilter', 'inappropriate_content');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(1);
    expect($reports->first()->reason)->toBe('inappropriate_content');
});

it('filters reports by spam', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->call('filterByReason', 'spam')
        ->assertSet('reasonFilter', 'spam');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(1);
    expect($reports->first()->reason)->toBe('spam');
});

it('filters reports by offensive', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->call('filterByReason', 'offensive')
        ->assertSet('reasonFilter', 'offensive');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(1);
    expect($reports->first()->reason)->toBe('offensive');
});

it('shows all reports when filter is set to all', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->call('filterByReason', 'spam')
        ->call('filterByReason', 'all')
        ->assertSet('reasonFilter', 'all');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(3);
});

// Search Tests
it('searches reports by survey title', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->set('searchTerm', 'Test Survey');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(3);
});

it('searches reports by respondent uuid', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->set('searchTerm', $this->respondent->uuid);
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(3);
});

it('handles empty search results', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->set('searchTerm', 'NONEXISTENT');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(0);
});

// Display Tests
it('displays report information correctly', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ReportIndex::class)
        ->assertSee('Test Survey')
        ->assertSee('John Doe'); // Changed from UUID to name since that's what's displayed
});

it('displays reason counts', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class);
    
    expect($component->viewData('inappropriateCount'))->toBe(1);
    expect($component->viewData('spamCount'))->toBe(1);
    expect($component->viewData('offensiveCount'))->toBe(1);
});

// Pagination Tests
it('paginates reports correctly', function () {
    Auth::login($this->superAdmin);
    
    // Create more reports
    for ($i = 1; $i <= 20; $i++) {
        Report::create([
            'survey_id' => $this->survey->id,
            'response_id' => $this->response->id,
            'reporter_id' => $this->reporter->id,
            'respondent_id' => $this->respondent->id,
            'question_id' => $this->question->id,
            'reason' => 'spam',
            'details' => "Spam report {$i}",
            'status' => 'unappealed',
        ]);
    }
    
    $component = Livewire::test(ReportIndex::class);
    $reports = $component->viewData('reports');
    
    expect($reports->perPage())->toBe(15);
    expect($reports->total())->toBe(23);
});

// Combined Filter and Search Tests
it('combines reason filter and search', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class)
        ->set('searchTerm', 'Test Survey')
        ->call('filterByReason', 'spam');
    
    $reports = $component->viewData('reports');
    expect($reports->total())->toBe(1);
    expect($reports->first()->reason)->toBe('spam');
});

// Sorting Tests
it('orders reports by creation date descending', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ReportIndex::class);
    $reports = $component->viewData('reports');
    
    $dates = $reports->pluck('created_at')->toArray();
    $sortedDates = collect($dates)->sortDesc()->values()->toArray();
    
    expect($dates)->toEqual($sortedDates);
});
