<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Surveys\FormBuilder\Modal\SurveyTypeModal;

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
    
    // Login as the researcher for all tests
    Auth::login($this->researcher);
});

it('can render the survey type modal in initial state', function () {
    // This test verifies that the modal loads correctly and shows the type selection step
    
    Livewire::test(SurveyTypeModal::class)
        ->assertSet('step', 'type')
        ->assertSet('surveyType', null)
        ->assertSet('creationMethod', null)
        ->assertSet('selectedTemplate', null)
        ->assertSee('What Type of Survey')
        ->assertSee('Basic Survey')
        ->assertSee('Advanced Survey');
});

it('can select basic survey type and proceed to method selection', function () {
    // This test verifies that selecting basic survey type advances to method selection
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->assertSet('surveyType', 'basic')
        ->assertSet('step', 'method')
        ->assertSee('Creating a basic survey')
        ->assertSee('Start from Scratch')
        ->assertSee('Use a Template');
});

it('can select advanced survey type and proceed to method selection', function () {
    // This test verifies that selecting advanced survey type advances to method selection
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'advanced')
        ->assertSet('surveyType', 'advanced')
        ->assertSet('step', 'method')
        ->assertSee('Creating a advanced survey')
        ->assertSee('Start from Scratch')
        ->assertSee('Use a Template');
});

it('can select creation method from scratch', function () {
    // This test verifies that selecting "from scratch" method sets the correct state
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'scratch')
        ->assertSet('creationMethod', 'scratch')
        ->assertSet('step', 'method'); // Should stay on method step for scratch option
});

it('can select template method and proceed to template selection', function () {
    // This test verifies that selecting template method advances to template selection
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'template')
        ->assertSet('creationMethod', 'template')
        ->assertSet('step', 'template')
        ->assertSee('Choose a Template')
        ->assertSee('ISO 25010 Template')
        ->assertSee('Academic Research Template');
});

it('can select ISO 25010 template', function () {
    // This test verifies that selecting ISO 25010 template sets the correct state
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'template')
        ->call('selectTemplate', 'iso25010')
        ->assertSet('selectedTemplate', 'iso25010');
});

it('can select academic research template', function () {
    // This test verifies that selecting academic research template sets the correct state
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'template')
        ->call('selectTemplate', 'academic')
        ->assertSet('selectedTemplate', 'academic');
});

it('can navigate back from method step to type step', function () {
    // This test verifies that the back button resets to type selection and clears selections
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'advanced')
        ->call('selectCreationMethod', 'scratch')
        ->call('goBack')
        ->assertSet('step', 'type')
        ->assertSet('surveyType', null)
        ->assertSet('creationMethod', null)
        ->assertSet('selectedTemplate', null)
        ->assertSee('What Type of Survey');
});

it('can navigate back from template step to method step', function () {
    // This test verifies that going back from template step preserves type and method selections
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'template')
        ->call('selectTemplate', 'iso25010')
        ->call('goBackToMethod')
        ->assertSet('step', 'method')
        ->assertSet('surveyType', 'basic')
        ->assertSet('creationMethod', 'template')
        ->assertSet('selectedTemplate', null)
        ->assertSee('Creating a basic survey');
});

it('creates basic survey from scratch with correct properties', function () {
    // This test verifies that creating a basic survey from scratch sets up the survey correctly
    
    $initialSurveyCount = Survey::count();
    $initialPageCount = SurveyPage::count();
    $initialQuestionCount = SurveyQuestion::count();
    $initialChoiceCount = SurveyChoice::count();
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'scratch')
        ->call('proceedToCreateSurvey')
        ->assertDispatched('close-modal')
        ->assertDispatched('survey-created-success');
    
    // Verify survey was created
    expect(Survey::count())->toBe($initialSurveyCount + 1);
    
    // Get the created survey
    $survey = Survey::latest()->first();
    expect($survey->user_id)->toBe($this->researcher->id);
    expect($survey->title)->toBe('Untitled Survey');
    expect($survey->status)->toBe('pending');
    expect($survey->type)->toBe('basic');
    expect($survey->points_allocated)->toBe(10); // Basic surveys get 10 points
    
    // Verify default page was created
    expect(SurveyPage::count())->toBe($initialPageCount + 1);
    $page = $survey->pages()->first();
    expect($page->page_number)->toBe(1);
    
    // Verify default question was created
    expect(SurveyQuestion::count())->toBe($initialQuestionCount + 1);
    $question = $survey->questions()->first();
    expect($question->question_text)->toBe('Enter Question Title');
    expect($question->question_type)->toBe('multiple_choice');
    expect($question->required)->toBeTrue();
    expect($question->order)->toBe(1);
    
    // Verify default choices were created
    expect(SurveyChoice::count())->toBe($initialChoiceCount + 2);
    $choices = $question->choices()->orderBy('order')->get();
    expect($choices[0]->choice_text)->toBe('Option 1');
    expect($choices[1]->choice_text)->toBe('Option 2');
});

it('creates advanced survey from scratch with correct properties', function () {
    // This test verifies that creating an advanced survey sets the correct points allocation
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'advanced')
        ->call('selectCreationMethod', 'scratch')
        ->call('proceedToCreateSurvey')
        ->assertDispatched('close-modal')
        ->assertDispatched('survey-created-success');
    
    // Get the created survey
    $survey = Survey::latest()->first();
    expect($survey->type)->toBe('advanced');
    expect($survey->points_allocated)->toBe(20); // Advanced surveys get 20 points
});

it('creates survey from ISO 25010 template with correct title', function () {
    // This test verifies that creating a survey from ISO 25010 template sets the correct title
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'template')
        ->call('selectTemplate', 'iso25010')
        ->call('proceedToCreateSurvey')
        ->assertDispatched('close-modal')
        ->assertDispatched('survey-created-success');
    
    // Get the created survey
    $survey = Survey::latest()->first();
    expect($survey->title)->toBe('ISO 25010 Software Quality Evaluation');
    expect($survey->type)->toBe('basic');
});

it('creates survey from academic research template with correct title', function () {
    // This test verifies that creating a survey from academic research template sets the correct title
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'advanced')
        ->call('selectCreationMethod', 'template')
        ->call('selectTemplate', 'academic')
        ->call('proceedToCreateSurvey')
        ->assertDispatched('close-modal')
        ->assertDispatched('survey-created-success');
    
    // Get the created survey
    $survey = Survey::latest()->first();
    expect($survey->title)->toBe('Academic Research Survey');
    expect($survey->type)->toBe('advanced');
    expect($survey->points_allocated)->toBe(20); // Advanced template still gets 20 points
});


it('prevents proceeding without survey type selection', function () {
    // This test verifies that survey creation is prevented without required selections
    
    $initialSurveyCount = Survey::count();
    
    Livewire::test(SurveyTypeModal::class)
        ->call('proceedToCreateSurvey')
        ->assertNotDispatched('close-modal')
        ->assertNotDispatched('survey-created-success');
    
    // Verify no survey was created
    expect(Survey::count())->toBe($initialSurveyCount);
});

it('prevents proceeding without creation method selection', function () {
    // This test verifies that survey creation is prevented without method selection
    
    $initialSurveyCount = Survey::count();
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('proceedToCreateSurvey')
        ->assertNotDispatched('close-modal')
        ->assertNotDispatched('survey-created-success');
    
    // Verify no survey was created
    expect(Survey::count())->toBe($initialSurveyCount);
});

it('prevents proceeding template method without template selection', function () {
    // This test verifies that template-based survey creation requires template selection
    
    $initialSurveyCount = Survey::count();
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'template')
        ->call('proceedToCreateSurvey')
        ->assertNotDispatched('close-modal')
        ->assertNotDispatched('survey-created-success');
    
    // Verify no survey was created
    expect(Survey::count())->toBe($initialSurveyCount);
});

it('maintains user context when creating surveys', function () {
    // This test verifies that created surveys are properly associated with the logged-in user
    
    Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'scratch')
        ->call('proceedToCreateSurvey');
    
    $survey = Survey::latest()->first();
    expect($survey->user_id)->toBe($this->researcher->id);
    expect($survey->user->email)->toBe('researcher@example.com');
    expect($survey->user->type)->toBe('researcher');
});

it('stores created survey UUID for later access', function () {
    // This test verifies that the component stores the UUID of the created survey
    
    $component = Livewire::test(SurveyTypeModal::class)
        ->call('selectSurveyType', 'basic')
        ->call('selectCreationMethod', 'scratch')
        ->call('proceedToCreateSurvey');
    
    $survey = Survey::latest()->first();
    expect($component->get('createdSurveyUuid'))->toBe($survey->uuid);
});

it('handles complete workflow from type to template creation', function () {
    // This test verifies the complete user workflow through all steps
    
    $component = Livewire::test(SurveyTypeModal::class)
        // Step 1: Select survey type
        ->assertSet('step', 'type')
        ->call('selectSurveyType', 'advanced')
        ->assertSet('step', 'method')
        ->assertSet('surveyType', 'advanced')
        
        // Step 2: Select creation method
        ->call('selectCreationMethod', 'template')
        ->assertSet('step', 'template')
        ->assertSet('creationMethod', 'template')
        
        // Step 3: Select template
        ->call('selectTemplate', 'academic')
        ->assertSet('selectedTemplate', 'academic')
        
        // Step 4: Create survey
        ->call('proceedToCreateSurvey')
        ->assertDispatched('close-modal')
        ->assertDispatched('survey-created-success');
    
    // Verify final survey has all correct properties
    $survey = Survey::latest()->first();
    expect($survey->type)->toBe('advanced');
    expect($survey->title)->toBe('Academic Research Survey');
    expect($survey->points_allocated)->toBe(20);
    expect($survey->user_id)->toBe($this->researcher->id);
});
