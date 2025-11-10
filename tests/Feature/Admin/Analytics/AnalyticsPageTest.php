<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\Response;
use App\Models\Institution;
use App\Models\SurveyTopic;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\InstitutionAdmin\InstitutionAnalytics\AnalyticsIndex as InstitutionAnalytics;
use App\Livewire\SuperAdmin\Analytics\AnalyticsIndex as SuperAdminAnalytics;

// Ensures database is reset between tests for isolation
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
    
    // Create researchers for both institutions
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
    
    $this->researcher3 = User::factory()->create([
        'email' => 'researcher3@institution2.com',
        'type' => 'researcher',
        'institution_id' => $this->institution2->id,
        'is_active' => true,
    ]);
    
    // Create survey topics
    $this->topic1 = SurveyTopic::create(['name' => 'Education']);
    $this->topic2 = SurveyTopic::create(['name' => 'Technology']);
    $this->topic3 = SurveyTopic::create(['name' => 'Health']);
    
    // Create surveys for institution 1 researchers
    $this->survey1 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher1->id,
        'title' => 'Survey 1 - Education',
        'status' => 'finished',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic1->id,
        'target_respondents' => 50,
        'created_at' => Carbon::now()->subMonths(2)
    ]);
    
    $this->survey2 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher1->id,
        'title' => 'Survey 2 - Education',
        'status' => 'finished',
        'type' => 'advanced',
        'points_allocated' => 20,
        'survey_topic_id' => $this->topic1->id,
        'target_respondents' => 30,
        'created_at' => Carbon::now()->subMonth()
    ]);
    
    $this->survey3 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher2->id,
        'title' => 'Survey 3 - Technology',
        'status' => 'ongoing',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic2->id,
        'target_respondents' => 40,
        'created_at' => Carbon::now()->subWeeks(2)
    ]);
    
    // Create survey for institution 2 researcher
    $this->survey4 = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher3->id,
        'title' => 'Survey 4 - Health',
        'status' => 'finished',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $this->topic3->id,
        'target_respondents' => 25,
        'created_at' => Carbon::now()->subMonth()
    ]);
    
    // Create pages and questions for surveys
    foreach ([$this->survey1, $this->survey2, $this->survey3, $this->survey4] as $survey) {
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
    
    // Create responses using Response model
    // Survey 1: 25 responses
    for ($i = 0; $i < 25; $i++) {
        $user = User::factory()->create(['institution_id' => $this->institution1->id]);
        Response::create([
            'survey_id' => $this->survey1->id,
            'user_id' => $user->id,
            'is_complete' => true,
            'completed_at' => Carbon::now()
        ]);
    }
    
    // Survey 2: 20 responses
    for ($i = 0; $i < 20; $i++) {
        $user = User::factory()->create(['institution_id' => $this->institution1->id]);
        Response::create([
            'survey_id' => $this->survey2->id,
            'user_id' => $user->id,
            'is_complete' => true,
            'completed_at' => Carbon::now()
        ]);
    }
    
    // Survey 3: 15 responses
    for ($i = 0; $i < 15; $i++) {
        $user = User::factory()->create(['institution_id' => $this->institution1->id]);
        Response::create([
            'survey_id' => $this->survey3->id,
            'user_id' => $user->id,
            'is_complete' => true,
            'completed_at' => Carbon::now()
        ]);
    }
    
    // Survey 4: 10 responses
    for ($i = 0; $i < 10; $i++) {
        $user = User::factory()->create(['institution_id' => $this->institution2->id]);
        Response::create([
            'survey_id' => $this->survey4->id,
            'user_id' => $user->id,
            'is_complete' => true,
            'completed_at' => Carbon::now()
        ]);
    }
    
    // Create rewards
    $this->systemReward = Reward::create([
        'name' => 'System Badge',
        'type' => 'system',
        'points_cost' => 50,
        'cost' => 50,
        'quantity' => 100
    ]);
    
    $this->voucherReward = Reward::create([
        'name' => 'Gift Voucher',
        'type' => 'voucher',
        'points_cost' => 100,
        'cost' => 100,
        'quantity' => 50
    ]);
    
    // Create redemptions for institution 1 users
    RewardRedemption::create([
        'user_id' => $this->researcher1->id,
        'reward_id' => $this->systemReward->id,
        'points_spent' => 50,
        'status' => 'completed'
    ]);
    
    RewardRedemption::create([
        'user_id' => $this->researcher2->id,
        'reward_id' => $this->voucherReward->id,
        'points_spent' => 100,
        'status' => 'completed'
    ]);
    
    // Create redemption for institution 2 user
    RewardRedemption::create([
        'user_id' => $this->researcher3->id,
        'reward_id' => $this->systemReward->id,
        'points_spent' => 50,
        'status' => 'completed'
    ]);
});

// Institution Admin Analytics Tests
it('loads institution analytics page for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    
    // Check component loads
    $component->assertStatus(200);
    
    // Check institution is set correctly
    expect($component->get('institution'))->not->toBeNull();
    expect($component->get('institution')->id)->toBe($this->institution1->id);
    
    // Check metrics - institution admin + 2 researchers = 3 users total
    expect($component->get('surveyCount'))->toBe(3);
    expect($component->get('userCount'))->toBeGreaterThanOrEqual(3); // At least 3 (may include response users)
    expect($component->get('totalResponses'))->toBe(60);
});

it('shows correct survey topics for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    $topics = $component->get('preferredTopics');
    
    expect($topics)->toHaveCount(2);
    expect($topics[0]['name'])->toBe('Education');
    expect($topics[0]['count'])->toBe(2);
    expect($topics[1]['name'])->toBe('Technology');
    expect($topics[1]['count'])->toBe(1);
});

it('shows correct top researchers for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    $researchers = collect($component->get('topResearchers'));
    
    expect($researchers->count())->toBeGreaterThanOrEqual(2);
    
    $firstResearcher = $researchers->firstWhere('email', $this->researcher1->email);
    $secondResearcher = $researchers->firstWhere('email', $this->researcher2->email);
    
    expect($firstResearcher)->not->toBeNull();
    expect($firstResearcher['surveys_count'])->toBe(2);
    expect($secondResearcher)->not->toBeNull();
    expect($secondResearcher['surveys_count'])->toBe(1);
});

it('shows correct reward stats for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    $rewardStats = $component->get('rewardStats');
    
    expect($rewardStats['system'])->toBe(1);
    expect($rewardStats['voucher'])->toBe(1);
});

it('calculates correct response rate for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    $responseRate = $component->get('responseRate');
    
    // Total responses: 60, Total target: 120 (50 + 30 + 40) = 50%
    expect($responseRate)->toBe(50.0);
});

it('shows monthly survey data for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    $monthlySurveys = $component->get('monthlySurveys');
    
    expect($monthlySurveys)->toHaveCount(12);
    expect($monthlySurveys)->toBeArray();
});

it('exports CSV correctly for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    Livewire::test(InstitutionAnalytics::class)
        ->call('exportToCsv')
        ->assertDispatched('download-csv');
});

it('updates year filter for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $lastYear = Carbon::now()->subYear()->year;
    
    Livewire::test(InstitutionAnalytics::class)
        ->call('updateYear', $lastYear)
        ->assertSet('selectedYear', $lastYear);
});

// Super Admin Analytics Tests
it('loads super admin analytics page', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    
    $component->assertStatus(200);
    
    expect($component->get('surveyCount'))->toBe(4);
    expect($component->get('userCount'))->toBeGreaterThanOrEqual(6); // 2 admins + 3 researchers + response users
    expect($component->get('totalResponses'))->toBe(70); // All 70 Response records
});

it('shows correct survey topics for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    $topics = $component->get('preferredTopics');
    
    expect($topics)->toHaveCount(3);
    expect($topics[0]['name'])->toBe('Education');
    expect($topics[0]['count'])->toBe(2);
});

it('shows correct top researchers for super admin across all institutions', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    $researchers = collect($component->get('topResearchers'));
    
    expect($researchers->count())->toBeGreaterThanOrEqual(3);
    
    $researcherOne = $researchers->firstWhere('email', $this->researcher1->email);
    $researcherTwo = $researchers->firstWhere('email', $this->researcher2->email);
    $researcherThree = $researchers->firstWhere('email', $this->researcher3->email);
    
    expect($researcherOne)->not->toBeNull();
    expect($researcherOne['surveys_count'])->toBe(2);
    expect($researcherTwo)->not->toBeNull();
    expect($researcherTwo['surveys_count'])->toBe(1);
    expect($researcherThree)->not->toBeNull();
    expect($researcherThree['surveys_count'])->toBe(1);
});

it('shows correct reward stats for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    $rewardStats = $component->get('rewardStats');
    
    expect($rewardStats['system'])->toBe(2);
    expect($rewardStats['voucher'])->toBe(1);
});

it('shows monthly survey data for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    $monthlySurveys = $component->get('monthlySurveys');
    
    expect($monthlySurveys)->toHaveCount(12);
    expect($monthlySurveys)->toBeArray();
});

it('exports CSV correctly for super admin', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(SuperAdminAnalytics::class)
        ->call('exportToCsv')
        ->assertDispatched('download-csv');
});

it('updates year filter for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    $currentYear = $component->get('selectedYear');
    
    $component->set('selectedYear', $currentYear - 1);
    
    expect($component->get('selectedYear'))->toBe($currentYear - 1);
});

// Data Isolation Tests
it('institution admin only sees their institution data', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    
    expect($component->get('surveyCount'))->toBe(3);
    expect($component->get('totalResponses'))->toBe(60);
    
    $topics = $component->get('preferredTopics');
    $topicNames = collect($topics)->pluck('name')->toArray();
    expect($topicNames)->not->toContain('Health');
});

it('super admin sees all institution data', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SuperAdminAnalytics::class);
    
    expect($component->get('surveyCount'))->toBe(4);
    
    $topics = $component->get('preferredTopics');
    $topicNames = collect($topics)->pluck('name')->toArray();
    expect($topicNames)->toContain('Education');
    expect($topicNames)->toContain('Technology');
    expect($topicNames)->toContain('Health');
});

// Export Button Tests
it('export button is visible for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    Livewire::test(InstitutionAnalytics::class)
        ->assertSee('Export Analytics to CSV');
});

it('export button is visible for super admin', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(SuperAdminAnalytics::class)
        ->assertSee('Export Analytics to CSV');
});

it('export includes correct institution name for institution admin', function () {
    Auth::login($this->institutionAdmin);
    
    $component = Livewire::test(InstitutionAnalytics::class);
    $institution = $component->get('institution');
    
    expect($institution->name)->toBe('Test University 1');
});
