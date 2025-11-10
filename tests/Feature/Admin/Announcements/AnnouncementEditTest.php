<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use App\Models\Announcement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Livewire\SuperAdmin\Announcements\Modal\ManageAnnouncementModal;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test institutions
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test-university.edu'
    ]);

    $this->secondInstitution = Institution::create([
        'name' => 'Second University',
        'domain' => 'second-university.edu'
    ]);

    // Create users with different roles
    $this->superAdmin = User::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'email' => 'superadmin@example.com',
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'is_active' => true
    ]);

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

    // Create test announcements
    $this->publicAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Public Announcement',
        'description' => 'This is a public announcement',
        'target_audience' => 'public',
        'active' => true,
    ]);
    
    $this->institutionAnnouncement = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Institution Announcement',
        'description' => 'This is for an institution',
        'target_audience' => 'institution_specific',
        'institution_id' => $this->institution->id,
        'active' => true,
    ]);

    $this->announcementWithImage = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Announcement With Image',
        'description' => 'This has an image',
        'image_path' => 'announcements/test-image.jpg',
        'target_audience' => 'public',
        'active' => true,
    ]);

    $this->announcementWithUrl = Announcement::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'title' => 'Announcement With URL',
        'description' => 'This has a URL',
        'target_audience' => 'public',
        'active' => true,
        'url' => 'https://example.com',
    ]);

    // Set up fake storage for testing file uploads
    Storage::fake('public');
    Storage::disk('public')->put('announcements/test-image.jpg', 'fake image content');
});

it('loads existing announcement data correctly', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->assertSet('title', 'Public Announcement')
        ->assertSet('description', 'This is a public announcement')
        ->assertSet('targetAudience', 'public')
        ->assertSet('active', true)
        ->assertSet('institutionId', null)
        ->assertSuccessful();
});

it('loads institution-specific announcement data correctly', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->institutionAnnouncement->id])
        ->assertSet('title', 'Institution Announcement')
        ->assertSet('targetAudience', 'institution_specific')
        ->assertSet('institutionId', $this->institution->id)
        ->assertSuccessful();
});

it('loads announcement with image correctly', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->announcementWithImage->id])
        ->assertSet('currentImage', 'announcements/test-image.jpg')
        ->assertSuccessful();
});

it('loads announcement with URL correctly', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->announcementWithUrl->id])
        ->assertSet('url', 'https://example.com')
        ->assertSuccessful();
});

it('can update announcement title and description', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('title', 'Updated Announcement Title')
        ->set('description', 'Updated announcement description')
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated')
        ->assertDispatched('close-modal');
    
    $this->publicAnnouncement->refresh();
    expect($this->publicAnnouncement->title)->toBe('Updated Announcement Title');
    expect($this->publicAnnouncement->description)->toBe('Updated announcement description');
});

it('can update announcement target audience', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('targetAudience', 'institution_specific')
        ->set('institutionId', $this->institution->id)
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->publicAnnouncement->refresh();
    expect($this->publicAnnouncement->target_audience)->toBe('institution_specific');
    expect($this->publicAnnouncement->institution_id)->toBe($this->institution->id);
});

it('can update announcement active status', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('active', false)
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->publicAnnouncement->refresh();
    expect($this->publicAnnouncement->active)->toBeFalse();
});

it('can update announcement end date', function () {
    $this->actingAs($this->superAdmin);
    
    $newEndDate = Carbon::tomorrow()->format('Y-m-d\TH:i');
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('end_date', $newEndDate)
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->publicAnnouncement->refresh();
    expect($this->publicAnnouncement->end_date->format('Y-m-d H:i'))->toBe(Carbon::parse($newEndDate)->format('Y-m-d H:i'));
});

it('can update announcement URL', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('url', 'https://example.org')
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->publicAnnouncement->refresh();
    expect($this->publicAnnouncement->url)->toBe('https://example.org');
});

it('can mark image for deletion and update', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->announcementWithImage->id])
        ->call('markImageForDeletion')
        ->assertSet('imageMarkedForDeletion', true)
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->announcementWithImage->refresh();
    expect($this->announcementWithImage->image_path)->toBeNull();
    Storage::disk('public')->assertMissing('announcements/test-image.jpg');
});

it('can upload new image for announcement', function () {
    $this->actingAs($this->superAdmin);
    
    $file = UploadedFile::fake()->create('new-image.jpg', 100);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('image', $file)
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->publicAnnouncement->refresh();
    expect($this->publicAnnouncement->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->publicAnnouncement->image_path);
});

it('can replace existing image with new one', function () {
    $this->actingAs($this->superAdmin);
    
    $file = UploadedFile::fake()->create('replacement-image.jpg', 100);
    $oldImagePath = $this->announcementWithImage->image_path;
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->announcementWithImage->id])
        ->set('image', $file)
        ->call('updateAnnouncement')
        ->assertDispatched('announcement-updated');
    
    $this->announcementWithImage->refresh();
    expect($this->announcementWithImage->image_path)->not->toBe($oldImagePath);
    Storage::disk('public')->assertMissing($oldImagePath);
    Storage::disk('public')->assertExists($this->announcementWithImage->image_path);
});

it('validates required fields', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('title', '')
        ->call('updateAnnouncement')
        ->assertHasErrors(['title']);
});

it('validates end date is after or equal to start date', function () {
    $this->actingAs($this->superAdmin);
    
    $startDate = Carbon::now()->addDays(2)->format('Y-m-d\TH:i');
    $endDate = Carbon::now()->addDay()->format('Y-m-d\TH:i');
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('start_date', $startDate)
        ->set('end_date', $endDate)
        ->call('updateAnnouncement')
        ->assertHasErrors(['end_date']);
});

it('validates URL format', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('url', 'not-a-valid-url')
        ->call('updateAnnouncement')
        ->assertHasErrors(['url']);
});

it('requires institution ID when target audience is institution specific', function () {
    $this->actingAs($this->superAdmin);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('targetAudience', 'institution_specific')
        ->set('institutionId', null)
        ->call('updateAnnouncement')
        ->assertHasErrors(['institutionId']);
});

it('can delete announcement', function () {
    $this->actingAs($this->superAdmin);
    
    $announcementId = $this->publicAnnouncement->id;
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $announcementId])
        ->call('deleteAnnouncement')
        ->assertDispatched('announcement-deleted')
        ->assertDispatched('close-modal');
    
    expect(Announcement::find($announcementId))->toBeNull();
});

it('deletes image from storage when deleting announcement with image', function () {
    $this->actingAs($this->superAdmin);
    
    $imagePath = $this->announcementWithImage->image_path;
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->announcementWithImage->id])
        ->call('deleteAnnouncement')
        ->assertDispatched('announcement-deleted');
    
    Storage::disk('public')->assertMissing($imagePath);
});

it('removes image preview when requested', function () {
    $this->actingAs($this->superAdmin);
    
    $file = UploadedFile::fake()->create('temp-image.jpg', 100);
    
    Livewire::test(ManageAnnouncementModal::class, ['announcementId' => $this->publicAnnouncement->id])
        ->set('image', $file)
        ->call('removeImagePreview')
        ->assertSet('image', null);
});
