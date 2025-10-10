<?php

use App\Livewire\Profile\Modal\EditProfileModal;
use App\Services\TestTimeService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user
    $this->user = User::factory()->create([
        'first_name' => 'Original',
        'last_name' => 'Name',
        'email' => 'test@example.com',
        'phone_number' => '09123456789',
        'profile_photo_path' => null,
        'profile_updated_at' => null, // Initially no profile update cooldown
    ]);
    
    // Login as the test user
    Auth::login($this->user);
    
    // Mock storage for file uploads
    Storage::fake('public');
});

it('loads edit profile modal with correct initial data', function () {
    // Test that the modal initializes with user data
    Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->assertSet('first_name', 'Original')
        ->assertSet('last_name', 'Name')
        ->assertSet('email', 'test@example.com')
        ->assertSet('phone_number', '09123456789')
        ->assertSet('canUpdateProfile', true); // Should be able to update since profile_updated_at is null
});

it('updates user profile data in database on save', function () {
    // Test profile data updates when submitted
    Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->set('first_name', 'Updated')
        ->set('last_name', 'Person')
        ->set('phone_number', '09987654321')
        ->call('save')
        ->assertDispatched('close-modal')
        ->assertDispatched('profileSaved');
    
    // Refresh user from database
    $this->user->refresh();
    
    // Verify changes were saved to database
    expect($this->user->first_name)->toBe('Updated');
    expect($this->user->last_name)->toBe('Person');
    expect($this->user->phone_number)->toBe('09987654321');
    expect($this->user->profile_updated_at)->not->toBeNull();
});

it('uploads profile photo and updates database', function () {
    // Create a test image
    $file = UploadedFile::fake()->create('avatar.jpg', 100);
    
    // Test photo upload and save
    Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->set('photo', $file)
        ->call('save');
    
    // Refresh user from database
    $this->user->refresh();
    
    // Verify photo path was saved to database and file exists
    expect($this->user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->user->profile_photo_path);
});

it('deletes existing profile photo', function () {
    // First upload an image
    $file = UploadedFile::fake()->create('avatar.jpg', 100);
    
    $component = Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->set('photo', $file)
        ->call('save');
    
    // Refresh user from database to get the photo path
    $this->user->refresh();
    $photoPath = $this->user->profile_photo_path;
    
    // Now delete the image
    Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->call('deleteCurrentPhoto');
    
    // Refresh user from database again
    $this->user->refresh();
    
    // Verify photo was removed
    expect($this->user->profile_photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($photoPath);
});

it('enforces profile update cooldown period', function () {
    // Set the profile_updated_at to a recent date to trigger cooldown
    $this->user->profile_updated_at = Carbon::now()->subDays(10); // Updated 10 days ago
    $this->user->save();
    
    // Test that the component shows cooldown and cannot update
    $component = Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->assertSet('canUpdateProfile', false)
        ->set('first_name', 'Blocked')
        ->set('last_name', 'Update')
        ->call('save');
    
    // Verify error session flash was shown
    $component->assertHasNoErrors(); // No validation errors, just session flash
    
    // Refresh user and verify data was not changed
    $this->user->refresh();
    expect($this->user->first_name)->toBe('Original');
    expect($this->user->last_name)->toBe('Name');
});

it('calculates cooldown period correctly', function () {
    // Set a specific update time for consistent testing
    $mockTime = Carbon::parse('2023-01-01 12:00:00');
    $this->user->profile_updated_at = $mockTime;
    $this->user->save();
    
    // Mock time using the same approach as VoucherVerifyTest
    $now = $mockTime->copy()->addDays(30);
    mockCurrentTime($now);
    
    // Test component cooldown calculation
    $component = Livewire::test(EditProfileModal::class, ['user' => $this->user]);
    
    // Should show 90 days remaining (120-30)
    expect($component->get('daysUntilProfileUpdateAvailable'))->toBe(90);
    expect($component->get('canUpdateProfile'))->toBeFalse();
    
    // Reset Carbon mock
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

it('allows update after cooldown period expires', function () {
    // Set a specific update time for consistent testing
    $mockTime = Carbon::now()->subDays(121);
    mockCurrentTime($mockTime->copy()->addDays(121)); // Current time = exactly 121 days after update
    
    $this->user->profile_updated_at = $mockTime;
    $this->user->save();
    
    // Test component allows updates
    Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->assertSet('canUpdateProfile', true)
        ->set('first_name', 'Cooldown')
        ->set('last_name', 'Expired')
        ->call('save');
    
    // Verify update succeeded
    $this->user->refresh();
    expect($this->user->first_name)->toBe('Cooldown');
    expect($this->user->last_name)->toBe('Expired');
    expect($this->user->profile_updated_at)->toBeInstanceOf(Carbon::class);
    
    // Reset Carbon mock
    Carbon::setTestNow(null);
    TestTimeService::mock(null);
});

// Add mockCurrentTime helper function at the top
if (!function_exists('mockCurrentTime')) {
    function mockCurrentTime($dateTime)
    {
        Carbon::setTestNow($dateTime);
        TestTimeService::mock($dateTime);
    }
}

it('validates required fields', function () {
    Livewire::test(EditProfileModal::class, ['user' => $this->user])
        ->set('first_name', '') // Required field
        ->set('last_name', '') // Required field
        ->call('save')
        ->assertHasErrors(['first_name', 'last_name']);
    
    // Verify data was not changed
    $this->user->refresh();
    expect($this->user->first_name)->toBe('Original');
    expect($this->user->last_name)->toBe('Name');
});


