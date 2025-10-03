<?php

use App\Livewire\Profile\ViewAbout;
use App\Models\User;
use App\Models\Tag;
use App\Models\TagCategory;
use App\Models\InstitutionTag;
use App\Models\InstitutionTagCategory;
use App\Models\Institution;
use App\Services\TestTimeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an institution
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);
    
    // Create a user for testing demographics updates
    $this->user = User::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'institution_id' => $this->institution->id,
        'demographic_tags_updated_at' => null, // No initial cooldown
        'institution_demographic_tags_updated_at' => null // No initial cooldown
    ]);
    
    // Create tag categories and tags for demographics testing
    $this->tagCategory1 = TagCategory::create(['name' => 'Age Group']);
    $this->tagCategory2 = TagCategory::create(['name' => 'Gender']);
    
    $this->tag1 = Tag::create(['name' => '18-24', 'tag_category_id' => $this->tagCategory1->id]);
    $this->tag2 = Tag::create(['name' => '25-34', 'tag_category_id' => $this->tagCategory1->id]);
    $this->tag3 = Tag::create(['name' => 'Male', 'tag_category_id' => $this->tagCategory2->id]);
    $this->tag4 = Tag::create(['name' => 'Female', 'tag_category_id' => $this->tagCategory2->id]);
    
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
    
    // Login as the test user
    Auth::login($this->user);
});

// Add mockCurrentTime helper function at the top if not already exists
if (!function_exists('mockCurrentTime')) {
    function mockCurrentTime($dateTime)
    {
        Carbon::setTestNow($dateTime);
        TestTimeService::mock($dateTime);
    }
}

it('loads demographics component with correct initial data', function () {
    // Test component loads with correct data
    Livewire::test(ViewAbout::class)
        ->assertSet('canUpdateDemographics', true)
        ->assertSet('canUpdateInstitutionDemographics', true);
});

it('saves demographic tags to the database', function () {
    // Initialize tag selections
    $selectedTags = [
        $this->tagCategory1->id => $this->tag1->id, // Age: 18-24
        $this->tagCategory2->id => $this->tag4->id  // Gender: Female
    ];
    
    // Test saving demographics
    Livewire::test(ViewAbout::class)
        ->set('selectedTags', $selectedTags)
        ->call('saveDemographicTags');
    
    // Refresh user and check tags
    $this->user->refresh();
    $userTags = $this->user->tags()->pluck('tags.id')->toArray();
    
    // Verify correct tags saved
    expect($userTags)->toContain($this->tag1->id);
    expect($userTags)->toContain($this->tag4->id);
    expect($userTags)->not->toContain($this->tag2->id);
    expect($userTags)->not->toContain($this->tag3->id);
    
    // Verify updated_at timestamp set
    expect($this->user->demographic_tags_updated_at)->not->toBeNull();
});

it('saves institution demographic tags to the database', function () {
    // Initialize institution tag selections
    $selectedInstitutionTags = [
        $this->institutionTagCategory->id => $this->institutionTag1->id // Department: Computer Science
    ];
    
    // Test saving institution demographics
    Livewire::test(ViewAbout::class)
        ->set('selectedInstitutionTags', $selectedInstitutionTags)
        ->call('saveInstitutionDemographicTags');
    
    // Refresh user and check tags
    $this->user->refresh();
    $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();
    
    // Verify correct tags saved
    expect($userInstitutionTags)->toContain($this->institutionTag1->id);
    expect($userInstitutionTags)->not->toContain($this->institutionTag2->id);
    
    // Verify updated_at timestamp set
    expect($this->user->institution_demographic_tags_updated_at)->not->toBeNull();
});

it('enforces demographic update cooldown period', function () {
    // Set a specific update time
    $mockTime = Carbon::parse('2023-01-01 12:00:00');
    $this->user->demographic_tags_updated_at = $mockTime;
    $this->user->save();
    
    // Mock current time to be 30 days after update
    mockCurrentTime($mockTime->copy()->addDays(30));
    
    // Add initial tag to check it's not changed
    $this->user->tags()->attach($this->tag2->id, ['tag_name' => $this->tag2->name]); // Initial tag: 25-34
    
    // Try updating with new tags during cooldown
    $component = Livewire::test(ViewAbout::class)
        ->assertSet('canUpdateDemographics', false)
        ->set('selectedTags', [
            $this->tagCategory1->id => $this->tag1->id, // Try changing to 18-24
            $this->tagCategory2->id => $this->tag4->id  // Add gender: Female
        ])
        ->call('saveDemographicTags');
    
    // Refresh user and check tags
    $this->user->refresh();
    $userTags = $this->user->tags()->pluck('tags.id')->toArray();
    
    // Verify tags were not changed
    expect($userTags)->toContain($this->tag2->id); // Original tag still there
    expect($userTags)->not->toContain($this->tag1->id); // New tag not added
    expect($userTags)->not->toContain($this->tag4->id); // New tag not added
    
    // Reset mocks
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

it('enforces institution demographic update cooldown period', function () {
    // Set institution demographics update time to a recent date
    $this->user->institution_demographic_tags_updated_at = Carbon::now()->subDays(30); // Updated 30 days ago
    $this->user->save();
    
    // Add initial institution tag to check it's not changed
    $this->user->institutionTags()->attach($this->institutionTag2->id, ['tag_name' => $this->institutionTag2->name]); // Initial: Engineering
    
    // Try updating during cooldown
    $component = Livewire::test(ViewAbout::class)
        ->assertSet('canUpdateInstitutionDemographics', false)
        ->set('selectedInstitutionTags', [
            $this->institutionTagCategory->id => $this->institutionTag1->id // Try changing to CS
        ])
        ->call('saveInstitutionDemographicTags');
    
    // Refresh user and check tags
    $this->user->refresh();
    $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();
    
    // Verify tags were not changed
    expect($userInstitutionTags)->toContain($this->institutionTag2->id); // Original tag still there
    expect($userInstitutionTags)->not->toContain($this->institutionTag1->id); // New tag not added
    
    // Reset mocks
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

it('allows demographic updates after cooldown period expires', function () {
    // Set a specific update time
    $mockTime = Carbon::parse('2023-01-01 12:00:00');
    $this->user->demographic_tags_updated_at = $mockTime;
    $this->user->save();
    
    // Mock current time to be exactly 121 days after update (just past the 120-day cooldown)
    mockCurrentTime($mockTime->copy()->addDays(121));
    
    // Add initial tag
    $this->user->tags()->attach($this->tag2->id, ['tag_name' => $this->tag2->name]); // Initial tag: 25-34
    
    // Update after cooldown expired
    Livewire::test(ViewAbout::class)
        ->assertSet('canUpdateDemographics', true)
        ->set('selectedTags', [
            $this->tagCategory1->id => $this->tag1->id, // Age: 18-24
        ])
        ->call('saveDemographicTags');
    
    // Refresh user and check tags
    $this->user->refresh();
    $userTags = $this->user->tags()->pluck('tags.id')->toArray();
    
    // Verify tags were changed
    expect($userTags)->toContain($this->tag1->id); // New tag added
    expect($userTags)->not->toContain($this->tag2->id); // Old tag removed
    
    // Reset mocks
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

it('calculates cooldown time remaining correctly', function () {
    // Set a specific update time for consistent testing
    $mockTime = Carbon::parse('2023-01-01 12:00:00');
    $this->user->demographic_tags_updated_at = $mockTime;
    $this->user->save();
    
    // Mock current time to be exactly 30 days after update
    mockCurrentTime($mockTime->copy()->addDays(30));
    
    // Test component cooldown calculation
    $component = Livewire::test(ViewAbout::class);
    
    // Should show exactly 90 days remaining (120-30)
    expect($component->get('daysUntilDemographicsUpdateAvailable'))->toBe(90);
    expect($component->get('canUpdateDemographics'))->toBeFalse();
    
    // Reset time mock
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

