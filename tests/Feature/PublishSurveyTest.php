<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use App\Models\Institution;
use App\Models\Tag;
use App\Models\TagCategory;
use App\Models\InstitutionTag;
use App\Models\InstitutionTagCategory;
use App\Models\Response;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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
    
    // Login as the researcher for all tests
    Auth::login($this->researcher);
});

// Helper function to create a basic survey with all requirements met
function createValidSurvey($isAdvanced = false, $includeGeneralTags = true, $includeInstitutionTags = false, $isInstitutionOnly = false)
{
    // Create base survey
    $survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'user_id' => Auth::id(),
        'title' => 'Test Survey',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => $isAdvanced ? 'advanced' : 'basic',
        'points_allocated' => $isAdvanced ? 20 : 10,
        'start_date' => Carbon::now()->addDay(),
        'end_date' => Carbon::now()->addDays(7),
        'is_institution_only' => $isInstitutionOnly,
        'is_announced' => false,
    ]);
    
    // Create a page
    $page = SurveyPage::create([
        'survey_id' => $survey->id,
        'title' => 'Page 1',
        'description' => 'First page',
        'page_number' => 1,
        'order' => 1,
    ]);
    
    // Create 6 required questions
    for ($i = 1; $i <= 6; $i++) {
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => true,
            'order' => $i
        ]);
        
        // Add choices to each question
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1
        ]);
        
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 2',
            'order' => 2
        ]);
    }
    
    // Add demographics tags if specified
    if ($includeGeneralTags) {
        $tagCategory = TagCategory::first();
        $tag = Tag::where('tag_category_id', $tagCategory->id)->first();
        $survey->tags()->attach($tag->id, ['tag_name' => $tag->name]);
    }
    
    if ($includeInstitutionTags) {
        $institutionTag = InstitutionTag::first();
        $survey->institutionTags()->attach($institutionTag->id, ['tag_name' => $institutionTag->name]);
    }
    
    return $survey;
}

// Test validation rules
it('prevents publishing a survey without pages', function () {
    // Create survey without pages
    $survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'user_id' => $this->researcher->id,
        'title' => 'Survey Without Pages',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => 'basic',
        'start_date' => Carbon::now()->addDay(),
        'end_date' => Carbon::now()->addDays(7),
    ]);
    
    // Try to publish
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey')
        ->assertDispatched('showErrorAlert'); // Should show error
    
    // Verify status didn't change
    $survey->refresh();
    expect($survey->status)->toBe('pending');
});

it('prevents publishing a survey with insufficient required questions', function () {
    // Create a survey with only 3 required questions (need 6)
    $survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'user_id' => $this->researcher->id,
        'title' => 'Survey With Few Questions',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => 'basic',
        'start_date' => Carbon::now()->addDay(),
        'end_date' => Carbon::now()->addDays(7),
    ]);
    
    // Create a page
    $page = SurveyPage::create([
        'survey_id' => $survey->id,
        'title' => 'Page 1',
        'page_number' => 1,
        'order' => 1,
    ]);
    
    // Create only 3 required questions
    for ($i = 1; $i <= 3; $i++) {
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => true,
            'order' => $i
        ]);
        
        // Add choices to each question
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1
        ]);
    }
    
    // Add 3 non-required questions (shouldn't count)
    for ($i = 4; $i <= 6; $i++) {
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => false, // Not required
            'order' => $i
        ]);
        
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1
        ]);
    }
    
    // Try to publish
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey')
        ->assertDispatched('showErrorAlert');
    
    // Verify status didn't change
    $survey->refresh();
    expect($survey->status)->toBe('pending');
});

it('prevents publishing advanced survey without demographics', function () {
    // Create an advanced survey without demographics
    $survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'user_id' => $this->researcher->id,
        'title' => 'Advanced Survey Without Demographics',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => 'advanced',
        'points_allocated' => 20,
        'start_date' => Carbon::now()->addDay(),
        'end_date' => Carbon::now()->addDays(7),
    ]);
    
    // Create a page with 6 required questions
    $page = SurveyPage::create([
        'survey_id' => $survey->id,
        'title' => 'Page 1',
        'page_number' => 1,
        'order' => 1,
    ]);
    
    for ($i = 1; $i <= 6; $i++) {
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => true,
            'order' => $i
        ]);
        
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1
        ]);
    }
    
    // Try to publish without demographics
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey')
        ->assertDispatched('showErrorAlert');
    
    // Verify status didn't change
    $survey->refresh();
    expect($survey->status)->toBe('pending');
});

it('prevents publishing institution-only advanced survey without institution tags', function () {
    // Create an advanced institution-only survey without institution tags
    $survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'user_id' => $this->researcher->id,
        'title' => 'Advanced Institution-Only Survey Without Tags',
        'description' => 'Test Description',
        'status' => 'pending',
        'type' => 'advanced',
        'points_allocated' => 20,
        'start_date' => Carbon::now()->addDay(),
        'end_date' => Carbon::now()->addDays(7),
        'is_institution_only' => true,
    ]);
    
    // Create a page with 6 required questions
    $page = SurveyPage::create([
        'survey_id' => $survey->id,
        'title' => 'Page 1',
        'page_number' => 1,
        'order' => 1,
    ]);
    
    for ($i = 1; $i <= 6; $i++) {
        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'survey_page_id' => $page->id,
            'question_text' => "Question $i",
            'question_type' => 'multiple_choice',
            'required' => true,
            'order' => $i
        ]);
        
        SurveyChoice::create([
            'survey_question_id' => $question->id,
            'choice_text' => 'Option 1',
            'order' => 1
        ]);
    }
    
    // Add general tags (these shouldn't count for institution-only survey)
    $survey->tags()->attach($this->tag1->id, ['tag_name' => $this->tag1->name]);
    
    // Try to publish without institution tags
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey')
        ->assertDispatched('showErrorAlert');
    
    // Verify status didn't change
    $survey->refresh();
    expect($survey->status)->toBe('pending');
});

// Test successful publishing scenarios
it('successfully publishes a basic survey with all requirements met', function () {
    // Create a valid survey using helper
    $survey = createValidSurvey(false, true);
    
    // Publish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey');
    
    // Verify status changed to published
    $survey->refresh();
    expect($survey->status)->toBe('published');
});

it('successfully publishes an advanced survey with demographic tags', function () {
    // Create a valid advanced survey
    $survey = createValidSurvey(true, true);
    
    // Publish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey');
    
    // Verify status changed to published
    $survey->refresh();
    expect($survey->status)->toBe('published');
    expect($survey->points_allocated)->toBe(20); // Advanced surveys get 20 points
});

it('successfully publishes an institution-only survey with institution tags', function () {
    // Create a valid institution-only survey
    $survey = createValidSurvey(true, false, true, true);
    
    // Publish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey');
    
    // Verify status changed to published
    $survey->refresh();
    expect($survey->status)->toBe('published');
});

it('sets survey status to ongoing if it already has responses', function () {
    // Create a valid survey
    $survey = createValidSurvey();
    
    // Create a response for this survey
    Response::create([
        'survey_id' => $survey->id,
        'user_id' => $this->researcher->id,
        'status' => 'completed',
    ]);
    
    // Publish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey');
    
    // Verify status changed to ongoing (not published)
    $survey->refresh();
    expect($survey->status)->toBe('ongoing');
});

// Test announcement creation
it('creates an announcement when is_announced is true', function () {
    // Create a valid survey with announcement enabled
    $survey = createValidSurvey();
    $survey->is_announced = true;
    $survey->save();
    
    // Initial count of announcements
    $initialAnnouncementCount = Announcement::count();
    
    // Publish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey');
    
    // Verify announcement was created
    expect(Announcement::count())->toBe($initialAnnouncementCount + 1);
    
    // Verify announcement details
    $announcement = Announcement::latest()->first();
    expect($announcement->title)->toBe($survey->title);
    expect($announcement->survey_id)->toBe($survey->id);
    expect($announcement->active)->toEqual(1); // Change toBeTrue() to toEqual(1)
});

it('does not create an announcement when is_announced is false', function () {
    // Create a valid survey with announcement disabled
    $survey = createValidSurvey();
    $survey->is_announced = false;
    $survey->save();
    
    // Initial count of announcements
    $initialAnnouncementCount = Announcement::count();
    
    // Publish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('publishSurvey');
    
    // Verify no announcement was created
    expect(Announcement::count())->toBe($initialAnnouncementCount);
});

it('creates appropriate target audience based on institution-only setting', function () {
    // Create a public survey with announcement enabled
    $publicSurvey = createValidSurvey();
    $publicSurvey->is_announced = true;
    $publicSurvey->is_institution_only = false;
    $publicSurvey->save();
    
    // Publish the public survey
    Livewire::test(FormBuilder::class, ['survey' => $publicSurvey])
        ->call('publishSurvey');
    
    // Verify announcement has public target audience
    $publicAnnouncement = Announcement::where('survey_id', $publicSurvey->id)->latest()->first();
    expect($publicAnnouncement->target_audience)->toBe('public');
    
    // Create an institution-only survey with announcement enabled
    $institutionSurvey = createValidSurvey(true, false, true, true);
    $institutionSurvey->is_announced = true;
    $institutionSurvey->save();
    
    // Publish the institution-only survey
    Livewire::test(FormBuilder::class, ['survey' => $institutionSurvey])
        ->call('publishSurvey');
    
    // Verify announcement has institution-specific target audience
    // Explicitly query for the announcement by survey_id to avoid getting the wrong one
    $institutionAnnouncement = Announcement::where('survey_id', $institutionSurvey->id)->latest()->first();
    expect($institutionAnnouncement->target_audience)->toBe('institution_specific');
});

it('unpublishes a survey and reverts status to pending', function () {
    // Create and publish a survey
    $survey = createValidSurvey();
    $survey->status = 'published';
    $survey->save();
    
    // Create an announcement for this survey
    Announcement::create([
        'title' => $survey->title,
        'description' => 'Test announcement',
        'target_audience' => 'public',
        'institution_id' => $this->institution->id,
        'active' => true,
        'survey_id' => $survey->id,
    ]);
    
    // Unpublish the survey
    Livewire::test(FormBuilder::class, ['survey' => $survey])
        ->call('unpublishSurvey');
    
    // Verify status changed back to pending
    $survey->refresh();
    expect($survey->status)->toBe('pending');
    
    // Verify announcement was deleted
    expect(Announcement::where('survey_id', $survey->id)->count())->toBe(0);
});
