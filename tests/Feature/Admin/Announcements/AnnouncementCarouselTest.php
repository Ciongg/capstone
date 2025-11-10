<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use App\Models\Announcement;
use App\Livewire\SuperAdmin\Announcements\Modal\AnnouncementCarousel;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test institution using direct creation instead of factory
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test-university.edu'
    ]);
    
    // Create users of different types
    $this->user = User::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'email' => 'user@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);
    
    $this->guestUser = User::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'email' => 'guest@example.com',
        'first_name' => 'Guest',
        'last_name' => 'User',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => null,
        'is_active' => true
    ]);
    
    // Create test announcements
    $this->publicAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Public Announcement',
        'description' => 'Everyone can see this',
        'target_audience' => 'public',
        'active' => true,
        'created_at' => now()->subHour(), // Ensure this is older
    ]);
    
    $this->institutionAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Institution Announcement',
        'description' => 'Only for institution',
        'target_audience' => 'institution_specific',
        'institution_id' => $this->institution->id,
        'active' => true,
        'created_at' => now()->subMinutes(30), // Ensure this has a consistent timestamp
    ]);
    
    $this->inactiveAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Inactive Announcement',
        'description' => 'Should not be shown',
        'target_audience' => 'public',
        'active' => false,
    ]);
    
    $this->expiredAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Expired Announcement',
        'description' => 'No longer active',
        'target_audience' => 'public',
        'active' => true,
        'start_date' => Carbon::now()->subDays(10),
        'end_date' => Carbon::yesterday(),
    ]);
});

it('shows public announcements to all users', function () {
    $this->actingAs($this->guestUser);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // Guest user should only see public announcements
    expect($announcements)->toHaveCount(1);
    expect($announcements[0]['title'])->toBe('Public Announcement');
});

it('shows institution-specific announcements to appropriate users', function () {
    $this->actingAs($this->user);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // User with institution should see both public and their institution announcements
    expect($announcements)->toHaveCount(2);
    
    // Check that both announcement types are in the list
    $titles = collect($announcements)->pluck('title')->toArray();
    expect($titles)->toContain('Public Announcement');
    expect($titles)->toContain('Institution Announcement');
});

it('filters out inactive announcements', function () {
    $this->actingAs($this->user);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // Should not contain the inactive announcement
    $titles = collect($announcements)->pluck('title')->toArray();
    expect($titles)->not->toContain('Inactive Announcement');
});

it('filters out expired announcements', function () {
    $this->actingAs($this->user);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // Should not contain the expired announcement
    $titles = collect($announcements)->pluck('title')->toArray();
    expect($titles)->not->toContain('Expired Announcement');
});

it('sorts announcements by creation date in descending order', function () {
    // First delete existing announcements to avoid any sorting issues
    Announcement::query()->delete();
    
    // Create older announcement first
    $olderAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Public Announcement',
        'description' => 'This is older',
        'target_audience' => 'public',
        'active' => true,
        'created_at' => now()->subMinutes(5),
    ]);
    
    // small delay to ensure timestamps are different
    sleep(1);
    
    $newerAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Newer Announcement',
        'description' => 'Most recent announcement',
        'target_audience' => 'public',
        'active' => true,
        'created_at' => now(), 
    ]);
    
    $this->actingAs($this->user);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    // First announcement should be the newest one
    expect($announcements[0]['title'])->toBe('Newer Announcement');
    expect($announcements[1]['title'])->toBe('Public Announcement');
});

it('renders empty state when no announcements exist', function () {
    // Delete all announcements
    Announcement::query()->delete();
    
    $this->actingAs($this->user);
    
    $carousel = Livewire::test(AnnouncementCarousel::class);
    $announcements = $carousel->get('announcements');
    
    expect($announcements)->toHaveCount(0);
    
    $carousel->assertSuccessful();
});
