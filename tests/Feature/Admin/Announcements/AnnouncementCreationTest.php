<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use App\Models\Announcement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Livewire\SuperAdmin\Announcements\Modal\CreateAnnouncementModal;
use App\Livewire\SuperAdmin\Announcements\Modal\AnnouncementCarousel;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test institutions using direct creation instead of factory
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test-university.edu'
    ]);

    $this->secondInstitution = Institution::create([
        'name' => 'Second University',
        'domain' => 'second-university.edu'
    ]);

    // Create users with proper types but without role assignments
    // The User::hasRole method in the app will check the 'type' field
    $this->superAdmin = User::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'email' => 'superadmin@example.com',
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'is_active' => true
    ]);
    // Remove assignRole call

    // Create an institution admin
    $this->institutionAdmin = User::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'email' => 'institutionadmin@example.com',
        'first_name' => 'Institution',
        'last_name' => 'Admin',
        'password' => bcrypt('password'),
        'type' => 'institution_admin',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);
    // Remove assignRole call

    // Create a regular researcher
    $this->researcher = User::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'email' => 'researcher@example.com',
        'first_name' => 'Test',
        'last_name' => 'Researcher',
        'password' => bcrypt('password'),
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);
    // Remove assignRole call

    // Set up fake storage for testing file uploads
    Storage::fake('public');
});

it('can render the create announcement modal', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->assertSuccessful()
        ->assertSee('Title')
        ->assertSee('Description')
        ->assertSee('Target Audience')
        ->assertSee('Active');
});

it('validates required fields', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', '')
        ->set('targetAudience', 'institution_specific')
        ->set('institutionId', null)
        ->call('save')
        ->assertHasErrors(['title', 'institutionId']);
});

it('allows creating public announcements', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Test Public Announcement')
        ->set('description', 'This is a test announcement')
        ->set('targetAudience', 'public')
        ->set('active', true)
        ->call('save')
        ->assertDispatched('announcementCreated')
        ->assertDispatched('close-modal');
    
    $announcement = Announcement::latest()->first();
    expect($announcement->title)->toBe('Test Public Announcement');
    expect($announcement->description)->toBe('This is a test announcement');
    expect($announcement->target_audience)->toBe('public');
    expect($announcement->institution_id)->toBeNull();
    expect($announcement->active)->toBeTrue();
});

it('allows creating institution-specific announcements', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Test Institution Announcement')
        ->set('description', 'This is for one institution')
        ->set('targetAudience', 'institution_specific')
        ->set('institutionId', $this->institution->id)
        ->set('active', true)
        ->call('save')
        ->assertDispatched('announcementCreated');
    
    $announcement = Announcement::latest()->first();
    expect($announcement->title)->toBe('Test Institution Announcement');
    expect($announcement->target_audience)->toBe('institution_specific');
    expect($announcement->institution_id)->toBe($this->institution->id);
});

it('allows image upload for announcements', function () {
    $this->actingAs($this->superAdmin);
    
    $file = UploadedFile::fake()->create('announcement.jpg', 100);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Announcement with Image')
        ->set('description', 'This announcement has an image')
        ->set('image', $file)
        ->set('targetAudience', 'public')
        ->call('save')
        ->assertDispatched('announcementCreated');
    
    $announcement = Announcement::latest()->first();
    expect($announcement->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($announcement->image_path);
});

it('validates date ranges', function () {
    $this->actingAs($this->superAdmin);
    
    $today = Carbon::today()->format('Y-m-d');
    $yesterday = Carbon::yesterday()->format('Y-m-d');
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Test Date Validation')
        ->set('targetAudience', 'public')
        ->set('start_date', $today)
        ->set('end_date', $yesterday)
        ->call('save')
        ->assertHasErrors(['end_date']);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Test Date Validation')
        ->set('targetAudience', 'public')
        ->set('start_date', $yesterday)
        ->set('end_date', $today)
        ->call('save')
        ->assertDispatched('announcementCreated');
});

it('limits institution admins to their own institution', function () {
    $this->actingAs($this->institutionAdmin);
    
    // Should be able to create for own institution
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Institution Admin Announcement')
        ->set('targetAudience', 'institution_specific')
        ->set('institutionId', $this->institution->id)
        ->call('save')
        ->assertDispatched('announcementCreated');
    
    // Should fail when trying to create for another institution
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Institution Admin Announcement')
        ->set('targetAudience', 'institution_specific')
        ->set('institutionId', $this->secondInstitution->id)
        ->call('save');
        
    // Check if announcement was created with the second institution ID
    $invalidAnnouncement = Announcement::where('institution_id', $this->secondInstitution->id)
                                      ->where('title', 'Institution Admin Announcement')
                                      ->first();
    
    // Verify the announcement wasn't created with the invalid institution
    expect($invalidAnnouncement)->toBeNull();
});

it('allows creating announcements with URLs', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Announcement with URL')
        ->set('description', 'This has a link')
        ->set('url', 'https://example.com')
        ->set('targetAudience', 'public')
        ->call('save')
        ->assertDispatched('announcementCreated');
    
    $announcement = Announcement::latest()->first();
    expect($announcement->url)->toBe('https://example.com');
});

it('validates URLs properly', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(CreateAnnouncementModal::class)
        ->set('title', 'Invalid URL Test')
        ->set('targetAudience', 'public')
        ->set('url', 'not-a-url')
        ->call('save')
        ->assertHasErrors(['url']);
});

it('loads active announcements in the carousel', function () {
    // Create public and institution-specific announcements
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Public Announcement',
        'description' => 'Everyone can see this',
        'target_audience' => 'public',
        'active' => true,
    ]);
    
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Institution Announcement',
        'description' => 'Only for institution',
        'target_audience' => 'institution_specific',
        'institution_id' => $this->institution->id,
        'active' => true,
    ]);
    
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Other Institution Announcement',
        'description' => 'For second institution',
        'target_audience' => 'institution_specific',
        'institution_id' => $this->secondInstitution->id,
        'active' => true,
    ]);
    
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Inactive Announcement',
        'description' => 'Should not be shown',
        'target_audience' => 'public',
        'active' => false,
    ]);
    
    // Test for institution user
    $this->actingAs($this->researcher);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // Should see public and own institution announcements only (2 total)
    expect($announcements)->toHaveCount(2);
    // Note: SQL sorting might be different, so we'll check that both announcements exist
    $titles = collect($announcements)->pluck('title')->toArray();
    expect($titles)->toContain('Institution Announcement');
    expect($titles)->toContain('Public Announcement');
});

it('respects announcement date ranges', function () {
    // Create announcements with different date ranges
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Current Announcement',
        'description' => 'Currently active',
        'target_audience' => 'public',
        'active' => true,
        'start_date' => Carbon::yesterday(),
        'end_date' => Carbon::tomorrow(),
    ]);
    
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Future Announcement',
        'description' => 'Not active yet',
        'target_audience' => 'public',
        'active' => true,
        'start_date' => Carbon::tomorrow(),
        'end_date' => Carbon::tomorrow()->addDays(5),
    ]);
    
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Expired Announcement',
        'description' => 'No longer active',
        'target_audience' => 'public',
        'active' => true,
        'start_date' => Carbon::now()->subDays(10),
        'end_date' => Carbon::yesterday(),
    ]);
    
    $this->actingAs($this->researcher);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // Should only see the currently active announcement (1 total)
    expect($announcements)->toHaveCount(1);
    
    // Verify it's the current announcement
    $titles = collect($announcements)->pluck('title')->toArray();
    expect($titles)->toContain('Current Announcement');
});

it('shows perpetual announcements without end dates', function () {
    Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Perpetual Announcement',
        'description' => 'No end date',
        'target_audience' => 'public',
        'active' => true,
        'start_date' => Carbon::yesterday(),
        'end_date' => null,
    ]);
    
    $this->actingAs($this->researcher);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    expect($announcements)->toHaveCount(1);
    
    // Verify it's the perpetual announcement
    $titles = collect($announcements)->pluck('title')->toArray();
    expect($titles)->toContain('Perpetual Announcement');
});
