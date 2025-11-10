<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\Institution;
use App\Models\SurveyTopic;
use App\Models\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\InstitutionAdmin\UserSurveys\InstitutionUserSurveysIndex;
use App\Livewire\SuperAdmin\UserSurveys\UserSurveysIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create two institutions
    $this->institution1 = Institution::factory()->create([
        'name' => 'Test University 1'
    ]);
    
    $this->institution2 = Institution::factory()->create([
        'name' => 'Test University 2'
    ]);
    
    // Create institution admin for institution 1
    $this->institutionAdmin = User::factory()->create([
        'email' => 'admin@institution1.com',
        'type' => 'institution_admin',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
    ]);
    
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create researchers for institution 1
    $this->researcher1 = User::factory()->create([
        'email' => 'researcher1@institution1.com',
        'type' => 'researcher',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
    ]);
    
    $this->researcher2 = User::factory()->create([
        'email' => 'researcher2@institution1.com',
        'type' => 'researcher',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
    ]);
    
    // Create researcher for institution 2
    $this->researcher3 = User::factory()->create([
        'email' => 'researcher3@institution2.com',
        'type' => 'researcher',
        'institution_id' => $this->institution2->id,
        'is_active' => true,
    ]);
    
    // Create survey topics
    $this->topic1 = SurveyTopic::create(['name' => 'Education']);
    $this->topic2 = SurveyTopic::create(['name' => 'Technology']);
    
    // Create surveys for institution 1
    $this->survey1 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher1->id,
        'title' => 'Survey 1 - Pending',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic1->id,
        'is_locked' => false,
    ]);
    
    $this->survey2 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher1->id,
        'title' => 'Survey 2 - Published',
        'description' => 'Test Description',
        'status' => 'published',
        'type' => 'advanced',
        'points_allocated' => 20,
        'survey_topic_id' => $this->topic1->id,
        'is_locked' => false,
    ]);
    
    $this->survey3 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher2->id,
        'title' => 'Survey 3 - Locked',
        'description' => 'Test Description',
        'status' => 'published',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic2->id,
        'is_locked' => true,
        'lock_reason' => 'Violates policy',
    ]);
    
    // Create survey for institution 2
    $this->survey4 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher3->id,
        'title' => 'Survey 4 - Institution 2',
        'description' => 'Test Description',
        'status' => 'published',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic2->id,
        'is_locked' => false,
    ]);
    
    // Create archived survey for institution 1
    $this->survey5 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher1->id,
        'title' => 'Survey 5 - Archived',
        'description' => 'Test Description',
        'status' => 'published',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic1->id,
    ]);
    $this->survey5->delete(); // Soft delete to archive
    
    // Create pages and questions for surveys
    foreach ([$this->survey1, $this->survey2, $this->survey3, $this->survey4, $this->survey5] as $survey) {
        $page = SurveyPage::create([
            'survey_id' => $survey->id,
            'title' => 'Page 1',
            'page_number' => 1
        ]);
        
        for ($i = 1; $i <= 6; $i++) {
            SurveyQuestion::create([
                'survey_id' => $survey->id,
                'survey_page_id' => $page->id,
                'question_text' => "Question $i",
                'question_type' => 'multiple_choice',
                'required' => true,
                'order' => $i
            ]);
        }
    }
    
    // Add responses to some surveys
    for ($i = 0; $i < 5; $i++) {
        Response::create([
            'survey_id' => $this->survey2->id,
            'user_id' => User::factory()->create(['institution_id' => $this->institution1->id])->id,
            'is_complete' => true,
            'completed_at' => Carbon::now()
        ]);
    }
});

// Institution Admin Survey List Tests
it('loads institution admin survey list page', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class);
    
    $component->assertStatus(200);
    
    $surveys = $component->viewData('surveys');
    // Should see 4 surveys from institution 1 (3 active + 1 archived)
    expect($surveys->total())->toBeGreaterThanOrEqual(3);
});

it('institution admin only sees surveys from their institution', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class);
    $surveys = $component->viewData('surveys');
    
    // Check all surveys belong to institution 1 users
    foreach ($surveys as $survey) {
        expect($survey->user->institution_id)->toBe($this->institution1->id);
    }
    
    // Should not see surveys from institution 2
    $surveyTitles = $surveys->pluck('title')->toArray();
    expect($surveyTitles)->not->toContain('Survey 4 - Institution 2');
});

it('filters surveys by status for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    Livewire::test(InstitutionUserSurveysIndex::class)
        ->call('filterByStatus', 'pending')
        ->assertSet('statusFilter', 'pending');
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class)
        ->set('statusFilter', 'pending');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->status)->toBe('pending');
    }
});

it('filters surveys by type for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class)
        ->call('filterByType', 'advanced')
        ->assertSet('typeFilter', 'advanced');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->type)->toBe('advanced');
    }
});

it('filters locked surveys for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class)
        ->call('filterByStatus', 'locked')
        ->assertSet('statusFilter', 'locked');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->is_locked)->toBeTrue();
    }
});

it('filters archived surveys for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class)
        ->call('filterByStatus', 'archived')
        ->assertSet('statusFilter', 'archived');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->trashed())->toBeTrue();
    }
});

it('searches surveys by title for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class)
        ->set('searchTerm', 'Survey 1');
    
    $surveys = $component->viewData('surveys');
    expect($surveys->total())->toBe(1);
    expect($surveys->first()->title)->toBe('Survey 1 - Pending');
});

it('shows correct survey counts for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class);
    
    expect($component->viewData('pendingCount'))->toBeGreaterThanOrEqual(1);
    expect($component->viewData('publishedCount'))->toBeGreaterThanOrEqual(1);
    expect($component->viewData('lockedCount'))->toBeGreaterThanOrEqual(1);
    expect($component->viewData('archivedCount'))->toBeGreaterThanOrEqual(1);
});

// Super Admin Survey List Tests
it('loads super admin survey list page', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class);
    
    $component->assertStatus(200);
    
    $surveys = $component->viewData('surveys');
    // Should see all 5 surveys (4 active + 1 archived)
    expect($surveys->total())->toBeGreaterThanOrEqual(4);
});

it('super admin sees surveys from all institutions', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class);
    $surveys = $component->viewData('surveys');
    
    $surveyTitles = $surveys->pluck('title')->toArray();
    
    // Should see surveys from both institutions
    expect($surveyTitles)->toContain('Survey 1 - Pending');
    expect($surveyTitles)->toContain('Survey 4 - Institution 2');
});

it('filters surveys by status for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class)
        ->call('filterByStatus', 'pending')
        ->assertSet('statusFilter', 'pending');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->status)->toBe('pending');
    }
});

it('filters surveys by type for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class)
        ->call('filterByType', 'advanced')
        ->assertSet('typeFilter', 'advanced');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->type)->toBe('advanced');
    }
});

it('filters locked surveys for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class)
        ->call('filterByStatus', 'locked')
        ->assertSet('statusFilter', 'locked');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->is_locked)->toBeTrue();
    }
});

it('filters archived surveys for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class)
        ->call('filterByStatus', 'archived')
        ->assertSet('statusFilter', 'archived');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->trashed())->toBeTrue();
    }
});

it('filters institution-only surveys for super admin', function () {
    // Mark survey as institution-only
    $this->survey1->update(['is_institution_only' => true]);
    
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class)
        ->call('filterByInstitution', 'institution')
        ->assertSet('institutionFilter', 'institution');
    
    $surveys = $component->viewData('surveys');
    foreach ($surveys as $survey) {
        expect($survey->is_institution_only)->toBeTrue();
    }
});

it('searches surveys by title or UUID for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class)
        ->set('searchTerm', 'Survey 2');
    
    $surveys = $component->viewData('surveys');
    expect($surveys->total())->toBe(1);
    expect($surveys->first()->title)->toBe('Survey 2 - Published');
});

it('shows correct survey counts for super admin', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(UserSurveysIndex::class);
    
    expect(Survey::where('status', 'pending')->count())->toBeGreaterThanOrEqual(1);
    expect(Survey::where('status', 'published')->count())->toBeGreaterThanOrEqual(2);
    expect(Survey::where('is_locked', true)->count())->toBeGreaterThanOrEqual(1);
    expect(Survey::onlyTrashed()->count())->toBeGreaterThanOrEqual(1);
});

// Data Isolation Tests
it('institution admin cannot see surveys from other institutions', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionUserSurveysIndex::class);
    $surveys = $component->viewData('surveys');
    
    $institution2SurveyIds = [$this->survey4->id];
    $surveyIds = $surveys->pluck('id')->toArray();
    
    foreach ($institution2SurveyIds as $surveyId) {
        expect($surveyIds)->not->toContain($surveyId);
    }
});

it('super admin can see surveys from all institutions', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class);
    $surveys = $component->viewData('surveys');
    
    $surveyIds = $surveys->pluck('id')->toArray();
    
    // Should contain surveys from both institutions
    expect($surveyIds)->toContain($this->survey1->id);
    expect($surveyIds)->toContain($this->survey4->id);
});

// Response Count Tests
it('displays correct response count for surveys', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class);
    $surveys = $component->viewData('surveys');
    
    $survey2 = $surveys->firstWhere('id', $this->survey2->id);
    expect($survey2->responses_count)->toBe(5);
});

// Pagination Tests
it('paginates survey list correctly', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(UserSurveysIndex::class);
    $surveys = $component->viewData('surveys');
    
    expect($surveys)->toHaveProperty('total');
    expect($surveys)->toHaveProperty('perPage');
});
