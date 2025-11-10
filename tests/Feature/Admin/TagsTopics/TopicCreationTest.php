<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\SurveyTopic;
use App\Models\Survey;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Tags\TopicIndex;

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
    
    Auth::login($this->superAdmin);
});

// Topic CRUD Tests
it('can create edit and delete topics', function () {
    // Create
    Livewire::test(TopicIndex::class)
        ->set('topicName', 'Gaming')
        ->call('saveTopic');
    
    $topic = SurveyTopic::where('name', 'Gaming')->first();
    expect($topic)->not->toBeNull();
    
    // Edit
    Livewire::test(TopicIndex::class)
        ->call('openTopicModal', $topic->id)
        ->set('topicName', 'Video Gaming')
        ->call('saveTopic');
    
    $topic->refresh();
    expect($topic->name)->toBe('Video Gaming');
    
    // Delete
    Livewire::test(TopicIndex::class)
        ->call('deleteTopic', $topic->id);
    
    expect(SurveyTopic::find($topic->id))->toBeNull();
});

it('validates topic name is required and unique', function () {
    SurveyTopic::create(['name' => 'Gaming']);
    
    Livewire::test(TopicIndex::class)
        ->set('topicName', '')
        ->call('saveTopic')
        ->assertHasErrors(['topicName']);
    
    Livewire::test(TopicIndex::class)
        ->set('topicName', 'Gaming')
        ->call('saveTopic')
        ->assertHasErrors(['topicName']);
});

it('can delete topic with associated surveys', function () {
    $topic = SurveyTopic::create(['name' => 'Gaming']);
    
    $researcher = User::factory()->create([
        'type' => 'researcher',
        'institution_id' => $this->institution->id
    ]);
    
    Survey::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'user_id' => $researcher->id,
        'title' => 'Gaming Survey',
        'description' => 'Test',
        'status' => 'published',
        'type' => 'basic',
        'points_allocated' => 10,
        'survey_topic_id' => $topic->id,
    ]);
    
    Livewire::test(TopicIndex::class)
        ->call('deleteTopic', $topic->id);
    
    expect(SurveyTopic::find($topic->id))->toBeNull();
});

it('displays topics in alphabetical order', function () {
    SurveyTopic::create(['name' => 'Zebra']);
    SurveyTopic::create(['name' => 'Gaming']);
    SurveyTopic::create(['name' => 'Apple']);
    
    $component = Livewire::test(TopicIndex::class);
    $topics = $component->viewData('topics');
    
    $names = $topics->pluck('name')->toArray();
    expect($names)->toEqual(['Apple', 'Gaming', 'Zebra']);
});
