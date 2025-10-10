<?php

namespace Tests\Feature;

use App\Livewire\Surveys\FormBuilder\FormBuilder;
use App\Models\Survey;
use App\Models\SurveyChoice;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\User;
use App\Models\Institution;
use App\Models\SurveyTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

// Use beforeEach instead of setUp() for consistency with other tests
beforeEach(function () {
    // Create test institution
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);

    // Create topic
    $this->topic = SurveyTopic::create([
        'name' => 'Test Topic',
        'description' => 'Topic for testing'
    ]);

    // Create a researcher user
    $this->user = User::factory()->create([
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
    ]);

    // Log in as the researcher
    Auth::login($this->user);

    // Create a test survey
    $this->survey = Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Test Survey',
        'description' => 'Survey for testing form builder',
        'user_id' => $this->user->id,
        'survey_topic_id' => $this->topic->id,
        'status' => 'pending',
        'points_allocated' => 10,
        'target_respondents' => 100,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'is_locked' => false,
    ]);

    // Create a first page
    $this->page = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'First Page',
        'order' => 1,
    ]);
});

// Convert @Test methods to it() style tests
it('loads existing survey data', function () {
    // Create a test question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Test Question',
        'question_type' => 'short_text',
        'order' => 1,
        'required' => true,
    ]);

    // Test that the form builder loads with the existing survey data
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->assertSet('survey.title', 'Test Survey')
        ->assertSet('survey.description', 'Survey for testing form builder')
        ->assertSet('survey.status', 'pending')
        ->assertSuccessful();
});

it('can add a page', function () {
    $initialPageCount = SurveyPage::where('survey_id', $this->survey->id)->count();

    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('addItem', 'page', null, ['title' => 'New Test Page'])
        ->assertSuccessful();

    // Check database for the added page
    expect(SurveyPage::where('survey_id', $this->survey->id)->count())
        ->toBe($initialPageCount + 1);

    $newPage = SurveyPage::where('survey_id', $this->survey->id)
        ->orderByDesc('id')
        ->first();

    expect($newPage->title)->toBe('New Test Page');
    expect($newPage->order)->toBe($initialPageCount + 1);
});

it('can add different question types', function () {
    $questionTypes = ['multiple_choice', 'radio', 'likert', 'short_text', 'essay', 'rating'];
    
    $livewire = Livewire::test(FormBuilder::class, ['survey' => $this->survey]);
    
    foreach ($questionTypes as $type) {
        $initialQuestionCount = SurveyQuestion::where('survey_page_id', $this->page->id)->count();
        
        $livewire->call('addItem', 'question', $this->page->id, [
            'question_type' => $type,
            'text' => "Test {$type} Question"
        ])->assertSuccessful();
        
        // Verify question was added to database
        expect(SurveyQuestion::where('survey_page_id', $this->page->id)->count())
            ->toBe($initialQuestionCount + 1);
        
        // Check question properties
        $question = SurveyQuestion::where('survey_page_id', $this->page->id)
            ->where('question_type', $type)
            ->latest()
            ->first();
            
        expect($question->question_text)->toBe("Test {$type} Question");
        expect($question->question_type)->toBe($type);
        expect((bool) $question->required)->toBeTrue();
        
        // Check that type-specific initialization happened
        if ($type === 'multiple_choice' || $type === 'radio') {
            // Should have two default options
            expect($question->choices()->count())->toBe(2);
        } elseif ($type === 'likert') {
            // Should have initialized columns and rows
            expect($question->likert_columns)->not->toBeNull();
            expect($question->likert_rows)->not->toBeNull();
        } elseif ($type === 'rating') {
            // Should have default star count
            expect($question->stars)->not->toBeNull();
            expect((int)$question->stars)->toBe(5);
        }
    }
});

it('can add and edit choices for multiple choice questions', function () {
    // First add a multiple choice question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Multiple Choice Test',
        'question_type' => 'multiple_choice',
        'order' => 1,
        'required' => true,
    ]);
    
    // Add choices
    $choice1 = SurveyChoice::create([
        'survey_question_id' => $question->id,
        'choice_text' => 'Option 1',
        'order' => 1,
    ]);
    
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        // Add a new choice
        ->call('addItem', 'choice', $question->id, ['text' => 'New Choice'])
        ->assertSuccessful()
        // Update existing choice
        ->set('choices.' . $choice1->id . '.choice_text', 'Updated Option 1')
        ->call('updateChoice', $choice1->id)
        ->assertSuccessful();
    
    // Verify new choice was added
    $newChoice = SurveyChoice::where('survey_question_id', $question->id)
        ->where('choice_text', 'New Choice')
        ->first();
    expect($newChoice)->not->toBeNull();
    
    // Verify existing choice was updated
    $choice1->refresh();
    expect($choice1->choice_text)->toBe('Updated Option 1');
});

it('can add other option to questions', function () {
    // Create a radio question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Radio With Other Test',
        'question_type' => 'radio',
        'order' => 1,
        'required' => true,
    ]);
    
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('addItem', 'otherOption', $question->id)
        ->assertSuccessful();
    
    // Verify "Other" option was added
    $otherChoice = SurveyChoice::where('survey_question_id', $question->id)
        ->where('is_other', true)
        ->first();
    
    expect($otherChoice)->not->toBeNull();
    expect($otherChoice->choice_text)->toBe('Other');
    expect($otherChoice->is_other)->toBeTrue();
    
    // Verify it was added as the last option (highest order)
    expect($otherChoice->order)->toBe(
        SurveyChoice::where('survey_question_id', $question->id)->max('order')
    );
});

it('can delete questions and choices', function () {
    // Create a question with choices
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Question To Delete',
        'question_type' => 'multiple_choice',
        'order' => 1,
    ]);
    
    // Add choices
    $choice1 = SurveyChoice::create([
        'survey_question_id' => $question->id,
        'choice_text' => 'Choice 1',
        'order' => 1,
    ]);
    
    $choice2 = SurveyChoice::create([
        'survey_question_id' => $question->id,
        'choice_text' => 'Choice 2',
        'order' => 2,
    ]);
    
    // First delete a choice
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('removeItem', 'choice', $choice1->id)
        ->assertSuccessful();
        
    // Verify the choice was deleted
    expect(SurveyChoice::find($choice1->id))->toBeNull();
    
    // Verify remaining choice was reordered
    $choice2->refresh();
    expect($choice2->order)->toBe(1);
    
    // Now delete the entire question
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('removeItem', 'question', $question->id)
        ->assertSuccessful();
        
    // Verify the question and all remaining choices were deleted
    expect(SurveyQuestion::find($question->id))->toBeNull();
    expect(SurveyChoice::where('survey_question_id', $question->id)->count())->toBe(0);
});

it('can delete pages with all contents', function () {
    // Create a second page with questions
    $page2 = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Page To Delete',
        'order' => 2,
    ]);
    
    $question1 = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $page2->id,
        'question_text' => 'Q1 On Page 2',
        'question_type' => 'short_text',
        'order' => 1,
    ]);
    
    $question2 = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $page2->id,
        'question_text' => 'Q2 On Page 2',
        'question_type' => 'radio',
        'order' => 2,
    ]);
    
    // Add some choices to question 2
    SurveyChoice::create([
        'survey_question_id' => $question2->id,
        'choice_text' => 'Option A',
        'order' => 1,
    ]);
    
    // Delete the page
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('removeItem', 'page', $page2->id)
        ->assertSuccessful();
        
    // Verify the page was deleted along with its questions and choices
    expect(SurveyPage::find($page2->id))->toBeNull();
    expect(SurveyQuestion::find($question1->id))->toBeNull();
    expect(SurveyQuestion::find($question2->id))->toBeNull();
    expect(SurveyChoice::where('survey_question_id', $question2->id)->count())->toBe(0);
});

it('can update question settings', function () {
    // Create a test question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Test Question',
        'question_type' => 'multiple_choice',
        'order' => 1,
        'required' => false,
    ]);
    
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        // Set the question as required
        ->set('questions.' . $question->id . '.required', true)
        // Set limit settings
        ->set('questions.' . $question->id . '.limit_condition', 'at_most')
        ->set('questions.' . $question->id . '.max_answers', 3)
        // Update question
        ->call('updateQuestion', $question->id)
        ->assertSuccessful();
        
    // Verify the changes persisted
    $question->refresh();
    expect($question->required)->toBeTrue();
    expect($question->limit_condition)->toBe('at_most');
    expect($question->max_answers)->toBe(3);
});

it('validates limit settings for multiple choice', function () {
    // Create a multiple choice question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Multiple Choice With Limits',
        'question_type' => 'multiple_choice',
        'order' => 1,
    ]);
    
    // Test with invalid max_answers (not a positive integer)
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->set('questions.' . $question->id . '.limit_condition', 'at_most')
        ->set('questions.' . $question->id . '.max_answers', 0) // Invalid value
        ->call('updateQuestion', $question->id)
        ->assertHasErrors(['questions.' . $question->id . '.max_answers']);
        
    // Test with valid settings
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->set('questions.' . $question->id . '.limit_condition', 'at_most')
        ->set('questions.' . $question->id . '.max_answers', 2) // Valid value
        ->call('updateQuestion', $question->id)
        ->assertHasNoErrors();
        
    // Verify changes
    $question->refresh();
    expect($question->limit_condition)->toBe('at_most');
    expect($question->max_answers)->toBe(2);
});

it('can reorder questions', function () {
    // Create multiple questions
    $question1 = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'First Question',
        'question_type' => 'short_text',
        'order' => 1,
    ]);
    
    $question2 = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Second Question',
        'question_type' => 'short_text',
        'order' => 2,
    ]);
    
    $question3 = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Third Question',
        'question_type' => 'short_text',
        'order' => 3,
    ]);
    
    // Move a question up
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('moveQuestionUp', $question2->id)
        ->assertSuccessful();
        
    // Check new order
    $question1->refresh();
    $question2->refresh();
    expect($question1->order)->toBe(2);
    expect($question2->order)->toBe(1);
    
    // Move a question down
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('moveQuestionDown', $question2->id)
        ->assertSuccessful();
        
    // Check new order
    $question1->refresh();
    $question2->refresh();
    expect($question1->order)->toBe(1);
    expect($question2->order)->toBe(2);
});

it('can reorder pages', function () {
    // Create multiple pages
    $page2 = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Second Page',
        'order' => 2,
    ]);
    
    $page3 = SurveyPage::create([
        'survey_id' => $this->survey->id,
        'title' => 'Third Page',
        'order' => 3,
    ]);
    
    // Move page up
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('movePageUp', $page2->id)
        ->assertSuccessful();
        
    // Check new order
    $this->page->refresh();
    $page2->refresh();
    expect($this->page->order)->toBe(2);
    expect($page2->order)->toBe(1);
    
    // Move page down
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('movePageDown', $page2->id)
        ->assertSuccessful();
        
    // Check new order
    $this->page->refresh();
    $page2->refresh();
    expect($this->page->order)->toBe(1);
    expect($page2->order)->toBe(2);
});

it('can reorder choices', function () {
    // Create a multiple choice question with choices
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Question with Choices',
        'question_type' => 'multiple_choice',
        'order' => 1,
    ]);
    
    $choice1 = SurveyChoice::create([
        'survey_question_id' => $question->id,
        'choice_text' => 'First Choice',
        'order' => 1,
    ]);
    
    $choice2 = SurveyChoice::create([
        'survey_question_id' => $question->id,
        'choice_text' => 'Second Choice',
        'order' => 2,
    ]);
    
    $choice3 = SurveyChoice::create([
        'survey_question_id' => $question->id,
        'choice_text' => 'Third Choice',
        'order' => 3,
    ]);
    
    // Move choice up
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('moveChoiceUp', $choice2->id)
        ->assertSuccessful();
        
    // Check new order
    $choice1->refresh();
    $choice2->refresh();
    expect($choice1->order)->toBe(2);
    expect($choice2->order)->toBe(1);
    
    // Move choice down
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('moveChoiceDown', $choice2->id)
        ->assertSuccessful();
        
    // Check new order
    $choice1->refresh();
    $choice2->refresh();
    expect($choice1->order)->toBe(1);
    expect($choice2->order)->toBe(2);
});

it('can update likert scale settings', function () {
    // Create a likert scale question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Likert Scale Question',
        'question_type' => 'likert',
        'order' => 1,
        'likert_columns' => json_encode(['Option 1', 'Option 2', 'Option 3']),
        'likert_rows' => json_encode(['Row 1', 'Row 2']),
    ]);
    
    $livewire = Livewire::test(FormBuilder::class, ['survey' => $this->survey]);
    
    // Add a column
    $livewire->call('addItem', 'likertColumn', $question->id)
        ->assertSuccessful();
        
    // Add a row
    $livewire->call('addItem', 'likertRow', $question->id)
        ->assertSuccessful();
        
    // Update existing column
    $livewire->set('likertColumns.' . $question->id . '.0', 'Updated Column')
        ->call('updateLikertColumn', $question->id, 0)
        ->assertSuccessful();
        
    // Update existing row
    $livewire->set('likertRows.' . $question->id . '.0', 'Updated Row')
        ->call('updateLikertRow', $question->id, 0)
        ->assertSuccessful();
    
    // Verify changes persisted
    $question->refresh();
    
    // Handle both array and JSON string format
    $columns = is_array($question->likert_columns) ? 
        $question->likert_columns : 
        json_decode($question->likert_columns, true);
        
    $rows = is_array($question->likert_rows) ? 
        $question->likert_rows : 
        json_decode($question->likert_rows, true);
    
    expect($columns[0])->toBe('Updated Column');
    expect($rows[0])->toBe('Updated Row');
    expect(count($columns))->toBe(4); // Original 3 + 1 new
    expect(count($rows))->toBe(3);    // Original 2 + 1 new
});

it('can update rating stars', function () {
    // Create a rating question
    $question = SurveyQuestion::create([
        'survey_id' => $this->survey->id,
        'survey_page_id' => $this->page->id,
        'question_text' => 'Rating Question',
        'question_type' => 'rating',
        'order' => 1,
        'stars' => 5,
    ]);
    
    // Change star count
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->set('ratingStars.' . $question->id, 10)
        ->call('updateRatingStars', $question->id)
        ->assertSuccessful();
        
    // Verify changes persisted
    $question->refresh();
    expect($question->stars)->toBe(10);
});

it('can publish survey', function () {
    // Add required questions to meet the minimum (6)
    for ($i = 1; $i <= 6; $i++) {
        SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $this->page->id,
            'question_text' => "Required Question $i",
            'question_type' => 'short_text',
            'required' => true,
            'order' => $i,
        ]);
    }
    
    Livewire::test(FormBuilder::class, ['survey' => $this->survey])
        ->call('publishSurvey')
        ->assertSuccessful();
        
    // Verify survey was published
    $this->survey->refresh();
    expect($this->survey->status)->toBe('published');
});
        
