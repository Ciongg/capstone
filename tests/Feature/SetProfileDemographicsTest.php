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

if (!function_exists('lockTimestamp')) {
    function lockTimestamp(Carbon $dateTime): string
    {
        return $dateTime->clone()->toISOString();
    }
}

it('loads demographics component with correct initial data', function () {
    // Test component loads with correct data
    Livewire::test(ViewAbout::class, ['user' => $this->user])
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
    Livewire::test(ViewAbout::class, ['user' => $this->user])
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
    expect(array_keys($this->user->demographic_tag_cooldowns ?? []))
        ->toEqualCanonicalizing([
            (string) $this->tagCategory1->id,
            (string) $this->tagCategory2->id,
        ]);
});

it('saves institution demographic tags to the database', function () {
    // Initialize institution tag selections
    $selectedInstitutionTags = [
        $this->institutionTagCategory->id => $this->institutionTag1->id // Department: Computer Science
    ];
    
    // Test saving institution demographics
    Livewire::test(ViewAbout::class, ['user' => $this->user])
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
    expect(array_keys($this->user->institution_demographic_tag_cooldowns ?? []))
        ->toEqualCanonicalizing([
            (string) $this->institutionTagCategory->id,
        ]);
});

it('enforces demographic update cooldown period', function () {
    $mockTime = Carbon::parse('2023-01-01 12:00:00');
    
    // Add initial tags for both categories BEFORE setting locks
    $this->user->tags()->attach($this->tag2->id, ['tag_name' => $this->tag2->name]); // Initial tag: 25-34
    $this->user->tags()->attach($this->tag3->id, ['tag_name' => $this->tag3->name]); // Initial tag: Male
    
    // Lock BOTH categories
    $this->user->demographic_tag_cooldowns = [
        (string) $this->tagCategory1->id => lockTimestamp($mockTime),
        (string) $this->tagCategory2->id => lockTimestamp($mockTime),
    ];
    $this->user->demographic_tags_updated_at = $mockTime;
    $this->user->save();
    
    // Mock current time to be 30 days after update
    mockCurrentTime($mockTime->copy()->addDays(30));
    
    // Try updating BOTH locked categories during cooldown
    $component = Livewire::test(ViewAbout::class, ['user' => $this->user])
        // canUpdateDemographics should be false since ALL categories are locked
        ->assertSet('canUpdateDemographics', false);
    
    // Verify that both categories are loaded as locked in originalSelectedTags
    $originalTags = $component->get('originalSelectedTags');
    expect($originalTags)->toHaveKey((string) $this->tagCategory1->id);
    expect($originalTags)->toHaveKey((string) $this->tagCategory2->id);
    expect($originalTags[(string) $this->tagCategory1->id])->toBe($this->tag2->id);
    expect($originalTags[(string) $this->tagCategory2->id])->toBe($this->tag3->id);
    
    $component->set('selectedTags', [
        $this->tagCategory1->id => $this->tag1->id,
        $this->tagCategory2->id => $this->tag4->id
    ]);
    $component->call('saveDemographicTags');

    $this->user->refresh();
    expect($this->user->demographic_tags_updated_at)->toEqual($mockTime);
    $userTags = $this->user->tags()->pluck('tags.id')->toArray();
    
    // Verify tags were not changed
    expect($userTags)->toContain($this->tag2->id); // Original tag still there
    expect($userTags)->toContain($this->tag3->id); // Original tag still there
    expect($userTags)->not->toContain($this->tag1->id); // New tag not added
    expect($userTags)->not->toContain($this->tag4->id); // New tag not added
    
    // Reset mocks
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

it('enforces institution demographic update cooldown period', function () {
    $lockedAt = Carbon::parse('2023-01-01 12:00:00');
    
    // Add initial tag BEFORE setting locks
    $this->user->institutionTags()->attach($this->institutionTag2->id, ['tag_name' => $this->institutionTag2->name]);
    
    $this->user->institution_demographic_tag_cooldowns = [
        (string) $this->institutionTagCategory->id => lockTimestamp($lockedAt),
    ];
    $this->user->institution_demographic_tags_updated_at = $lockedAt;
    $this->user->save();
    
    mockCurrentTime($lockedAt->copy()->addDays(30));
    
    $component = Livewire::test(ViewAbout::class, ['user' => $this->user])
        ->assertSet('canUpdateInstitutionDemographics', false);
    
    // Verify the original tag is loaded
    $originalInstitutionTags = $component->get('originalSelectedInstitutionTags');
    expect($originalInstitutionTags)->toHaveKey((string) $this->institutionTagCategory->id);
    expect($originalInstitutionTags[(string) $this->institutionTagCategory->id])->toBe($this->institutionTag2->id);
    
    $component->set('selectedInstitutionTags', [
        $this->institutionTagCategory->id => $this->institutionTag1->id
    ]);
    $component->call('saveInstitutionDemographicTags');

    $this->user->refresh();
    expect($this->user->institution_demographic_tags_updated_at)->toEqual($lockedAt);
    $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();
    
    expect($userInstitutionTags)->toContain($this->institutionTag2->id);
    expect($userInstitutionTags)->not->toContain($this->institutionTag1->id);
    
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

it('allows demographic updates after cooldown period expires', function () {
    $lockedAt = Carbon::parse('2023-01-01 12:00:00');
    $this->user->demographic_tag_cooldowns = [
        (string) $this->tagCategory1->id => lockTimestamp($lockedAt),
    ];
    $this->user->demographic_tags_updated_at = $lockedAt;
    $this->user->save();
    mockCurrentTime($lockedAt->copy()->addMonths(4)->addDay());
    
    // Add initial tag
    $this->user->tags()->attach($this->tag2->id, ['tag_name' => $this->tag2->name]); // Initial tag: 25-34
    
    // Update after cooldown expired
    Livewire::test(ViewAbout::class, ['user' => $this->user])
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
    $mockTime = Carbon::parse('2023-01-01 12:00:00');
    // Lock ALL categories to make canUpdateDemographics false
    $this->user->demographic_tag_cooldowns = [
        (string) $this->tagCategory1->id => lockTimestamp($mockTime),
        (string) $this->tagCategory2->id => lockTimestamp($mockTime),
    ];
    $this->user->demographic_tags_updated_at = $mockTime;
    $this->user->save();
    mockCurrentTime($mockTime->copy()->addDays(30));
    $component = Livewire::test(ViewAbout::class, ['user' => $this->user]);
    $lockInfo = $component->get('demographicLockInfo');
    $categoryKey = (string) $this->tagCategory1->id;
    $remainingDays = TestTimeService::now()->diffInDays($lockInfo[$categoryKey]['locked_until']);
    expect((int) $remainingDays)->toBe(90);
    expect($lockInfo[$categoryKey]['locked'])->toBeTrue();
    expect($component->get('canUpdateDemographics'))->toBeFalse();
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

