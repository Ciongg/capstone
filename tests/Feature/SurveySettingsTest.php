<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\Institution;
use App\Models\Tag;
use App\Models\TagCategory;
use App\Models\InstitutionTag;
use App\Models\InstitutionTagCategory;
use App\Models\SurveyTopic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Livewire\Surveys\FormBuilder\Modal\SurveySettingsModal;
use App\Livewire\Surveys\FormBuilder\FormBuilder;

// Ensures database is reset between tests for isolation
uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an academic institution for the researcher
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);
    
    // Create a researcher user who will create surveys
    $this->researcher = User::factory()->create([
        'email' => 'researcher@example.com',
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create a second researcher (for collaborator tests)
    $this->collaborator = User::factory()->create([
        'email' => 'collaborator@example.com',
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create survey topics
    $this->topic1 = SurveyTopic::create(['name' => 'Education']);
    $this->topic2 = SurveyTopic::create(['name' => 'Technology']);
    
    // Create tag categories and tags for demographics testing
    $this->tagCategory = TagCategory::create(['name' => 'Age Group']);
    $this->tag1 = Tag::create(['name' => '18-24', 'tag_category_id' => $this->tagCategory->id]);
    $this->tag2 = Tag::create(['name' => '25-34', 'tag_category_id' => $this->tagCategory->id]);
    
    // Create institution tag categories and tags
    $this->institutionTagCategory = InstitutionTagCategory::create([
        'name' => 'Department',
        'institution_id' => $this->institution->id
    ]);
    $this->institutionTag1 = InstitutionTag::create([
        'name' => 'Computer Science',
        'institution_tag_category_id' => $this->institutionTagCategory->id
    ]);
    $this->institutionTag2 = InstitutionTag::create([
        'name' => 'Engineering',
        'institution_tag_category_id' => $this->institutionTagCategory->id
    ]);
    
    // Create a basic survey for testing settings
    $this->survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $this->researcher->id,
        'title' => 'Test Survey',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => 'basic',
        'points_allocated' => 10,
        'is_institution_only' => false
    ]);
    
    // Create a page for the survey
    $this->page = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Page 1',
        'description' => 'First page',
        'page_number' => 1
    ]);
    
    // Create required questions (at least 6 for validation tests)
    for ($i = 1; $i <= 6; $i++) {
        SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $this->page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => true,
            'order' => $i
        ]);
    }
    
    // Login as the researcher for all tests
    Auth::login($this->researcher);
    
    // Mock storage for file uploads
    Storage::fake('public');
});

// Form Settings Functionality Tests
it('loads survey settings modal with correct initial data', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->assertSet('title', 'Test Survey')
        ->assertSet('description', 'Test Description')
        ->assertSet('type', 'basic')
        ->assertSet('points_allocated', 10)
        ->assertSet('isInstitutionOnly', false)
        ->assertSet('isAnnounced', false);
});

it('updates basic survey settings', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('title', 'Updated Survey Title')
        ->set('description', 'Updated Description')
        ->set('target_respondents', 100)
        ->set('survey_topic_id', $this->topic1->id)
        ->call('saveSurveyInformation')
        ->assertDispatched('setSaveStatus')
        ->assertDispatched('surveySettingsUpdated')
        ->assertDispatched('surveyTitleUpdated')
        ->assertDispatched('close-modal');
    
    // Refresh survey from database
    $this->survey->refresh();
    
    // Verify the changes were persisted
    expect($this->survey->title)->toBe('Updated Survey Title');
    expect($this->survey->description)->toBe('Updated Description');
    expect($this->survey->target_respondents)->toBe(100);
    expect($this->survey->survey_topic_id)->toBe($this->topic1->id);
});

it('updates survey type and recalculates points', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('type', 'advanced')
        ->assertSet('points_allocated', 20) // Advanced surveys get 20 points
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    expect($this->survey->type)->toBe('advanced');
    expect($this->survey->points_allocated)->toBe(20);
});

it('validates required fields in survey settings', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('title', '') // Empty title should fail validation
        ->call('saveSurveyInformation')
        ->assertDispatched('validation-error'); // Check for dispatched event instead of errors
});

it('validates start date is in future for pending surveys', function () {
    $pastDate = Carbon::now()->subHour()->format('Y-m-d\TH:i');
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('start_date', $pastDate)
        ->call('saveSurveyInformation')
        ->assertDispatched('validation-error'); // Check for dispatched event instead of errors
});

it('validates end date is after start date', function () {
    $startDate = Carbon::now()->addDay()->format('Y-m-d\TH:i');
    $endDate = Carbon::now()->addHour()->format('Y-m-d\TH:i'); // Before start date
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('start_date', $startDate)
        ->set('end_date', $endDate)
        ->call('saveSurveyInformation')
        ->assertDispatched('validation-error'); // Check for dispatched event instead of errors
});

it('successfully sets start and end dates when valid', function () {
    $startDate = Carbon::now()->addDay()->format('Y-m-d\TH:i');
    $endDate = Carbon::now()->addDays(2)->format('Y-m-d\TH:i');
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('start_date', $startDate)
        ->set('end_date', $endDate)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    expect($this->survey->start_date->format('Y-m-d H:i'))->toBe(Carbon::parse($startDate)->format('Y-m-d H:i'));
    expect($this->survey->end_date->format('Y-m-d H:i'))->toBe(Carbon::parse($endDate)->format('Y-m-d H:i'));
});

it('handles banner image upload', function () {
    // Use create() instead of image() to avoid GD dependency
    $file = \Illuminate\Http\UploadedFile::fake()->create('survey-banner.jpg', 100);
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('banner_image', $file)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    expect($this->survey->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->survey->image_path);
});

it('can delete existing banner image', function () {
    // Use create() instead of image() to avoid GD dependency
    $file = \Illuminate\Http\UploadedFile::fake()->create('survey-banner.jpg', 100);
    
    $component = Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('banner_image', $file)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    $imagePath = $this->survey->image_path;
    
    // Now delete it
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->call('deleteCurrentBannerImage');
    
    $this->survey->refresh();
    expect($this->survey->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($imagePath);
});

it('toggles institution-only setting', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('isInstitutionOnly', true)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    expect($this->survey->is_institution_only)->toBeTrue();
});

it('toggles announcement setting', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('isAnnounced', true)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    // Fix by checking the raw value rather than using toBeTrue()
    // since the column might not be cast properly
    expect($this->survey->is_announced)->toEqual(1);
});

// Form Demographics Functionality Tests
it('saves general demographic tags to survey', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('selectedSurveyTags', [$this->tagCategory->id => [$this->tag1->id, $this->tag2->id]])
        ->call('saveSurveyTags');
    
    $this->survey->refresh();
    $tags = $this->survey->tags;
    
    expect($tags)->toHaveCount(2);
    expect($tags->pluck('id')->toArray())->toContain($this->tag1->id);
    expect($tags->pluck('id')->toArray())->toContain($this->tag2->id);
});

it('saves institution demographic tags to survey', function () {
    // Set survey to institution-only first
    $this->survey->update(['is_institution_only' => true]);
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('selectedInstitutionTags', [$this->institutionTagCategory->id => [$this->institutionTag1->id]])
        ->call('saveInstitutionTags');
    
    $this->survey->refresh();
    $tags = $this->survey->institutionTags;
    
    expect($tags)->toHaveCount(1);
    expect($tags->pluck('id')->toArray())->toContain($this->institutionTag1->id);
});

it('clears general tags when switching to institution-only', function () {
    // First add some general tags
    $this->survey->tags()->attach($this->tag1->id, ['tag_name' => $this->tag1->name]);
    
    // Now switch to institution-only
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('isInstitutionOnly', true)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    expect($this->survey->tags)->toHaveCount(0);
});

it('clears institution tags when switching to public survey', function () {
    // Set to institution-only and add institution tags
    $this->survey->update(['is_institution_only' => true]);
    $this->survey->institutionTags()->attach($this->institutionTag1->id, ['tag_name' => $this->institutionTag1->name]);
    
    // Now switch to public
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('isInstitutionOnly', false)
        ->call('saveSurveyInformation');
    
    $this->survey->refresh();
    expect($this->survey->institutionTags)->toHaveCount(0);
});

// Form Collaborator Functionality Tests
it('loads existing collaborators correctly', function () {
    // Add collaborator first
    $this->survey->collaborators()->attach($this->collaborator->id, ['user_uuid' => $this->collaborator->uuid]);
    
    $component = Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey]);
    $collaborators = $component->get('collaborators');
    
    expect($collaborators)->toHaveCount(1);
    expect($collaborators[0]['uuid'])->toBe($this->collaborator->uuid);
    expect($collaborators[0]['name'])->toBe($this->collaborator->first_name . ' ' . $this->collaborator->last_name);
});

it('adds a collaborator successfully', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('newCollaboratorUuid', $this->collaborator->uuid)
        ->call('addCollaborator')
        ->assertDispatched('showSuccessAlert');
    
    expect($this->survey->collaborators()->count())->toBe(1);
    expect($this->survey->isCollaborator($this->collaborator))->toBeTrue();
});

it('validates collaborator UUID format', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('newCollaboratorUuid', 'not-a-uuid')
        ->call('addCollaborator')
        ->assertDispatched('validation-error');
    
    expect($this->survey->collaborators()->count())->toBe(0);
});

it('prevents adding same collaborator twice', function () {
    // Add collaborator first
    $this->survey->collaborators()->attach($this->collaborator->id, ['user_uuid' => $this->collaborator->uuid]);
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('newCollaboratorUuid', $this->collaborator->uuid)
        ->call('addCollaborator')
        ->assertDispatched('validation-error');
    
    expect($this->survey->collaborators()->count())->toBe(1);
});

it('prevents adding survey owner as collaborator', function () {
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->set('newCollaboratorUuid', $this->researcher->uuid)
        ->call('addCollaborator')
        ->assertDispatched('validation-error');
    
    expect($this->survey->collaborators()->count())->toBe(0);
});

it('removes a collaborator successfully', function () {
    // Add collaborator first
    $this->survey->collaborators()->attach($this->collaborator->id, ['user_uuid' => $this->collaborator->uuid]);
    
    Livewire::test(SurveySettingsModal::class, ['survey' => $this->survey])
        ->call('removeCollaborator', $this->collaborator->uuid)
        ->assertDispatched('showSuccessAlert');
    
    expect($this->survey->collaborators()->count())->toBe(0);
});


