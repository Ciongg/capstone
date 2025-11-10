<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\AuditLogs;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\Logs\LogsIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create test users
    $this->user1 = User::factory()->create([
        'email' => 'user1@example.com',
        'type' => 'researcher',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
    
    $this->user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'type' => 'respondent',
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);
    
    // Create audit log for survey creation - using correct columns
    $this->createLog = AuditLogs::create([
        'performed_by' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'created',
        'resource_type' => 'Survey',
        'resource_id' => 1,
        'before' => null,
        'after' => [
            'title' => 'Customer Satisfaction Survey',
            'description' => 'A survey about customer satisfaction',
            'status' => 'draft',
        ],
        'changed_fields' => ['title', 'description', 'status'],
        'created_at' => Carbon::now()->subHours(5),
    ]);
    
    // Create audit log for user update
    $this->updateLog = AuditLogs::create([
        'performed_by' => $this->user2->id,
        'email' => $this->user2->email,
        'event_type' => 'updated',
        'resource_type' => 'User',
        'resource_id' => 2,
        'before' => [
            'first_name' => 'John',
            'email' => 'john@example.com',
        ],
        'after' => [
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ],
        'changed_fields' => ['first_name', 'email'],
        'created_at' => Carbon::now()->subHours(3),
    ]);
    
    // Create audit log for survey deletion
    $this->deleteLog = AuditLogs::create([
        'performed_by' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'deleted',
        'resource_type' => 'Survey',
        'resource_id' => 5,
        'before' => [
            'title' => 'Test Survey',
            'status' => 'published',
        ],
        'after' => null,
        'changed_fields' => ['deleted_at'],
        'created_at' => Carbon::now()->subHours(2),
    ]);
    
    // Create audit log for survey restoration
    $this->restoreLog = AuditLogs::create([
        'performed_by' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'restored',
        'resource_type' => 'Survey',
        'resource_id' => 5,
        'before' => null,
        'after' => [
            'deleted_at' => null,
        ],
        'changed_fields' => ['deleted_at'],
        'created_at' => Carbon::now()->subHours(1),
    ]);
    
    // Create audit log for survey lock
    $this->lockLog = AuditLogs::create([
        'performed_by' => $this->superAdmin->id,
        'email' => $this->superAdmin->email,
        'event_type' => 'locked',
        'resource_type' => 'Survey',
        'resource_id' => 3,
        'before' => [
            'is_locked' => false,
            'lock_reason' => null,
        ],
        'after' => [
            'is_locked' => true,
            'lock_reason' => 'Violates community guidelines',
        ],
        'changed_fields' => ['is_locked', 'lock_reason'],
        'created_at' => Carbon::now()->subMinutes(30),
    ]);
});

it('can open audit log view modal', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->call('viewAuditLog', $this->createLog->id)
        ->assertSet('selectedAuditLogId', $this->createLog->id)
        ->assertDispatched('open-modal');
});

it('displays complete audit log information in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->call('viewAuditLog', $this->createLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    // Verify all log information is available
    expect($selectedLog->id)->toBe($this->createLog->id);
    expect($selectedLog->email)->toBe('user1@example.com');
    expect($selectedLog->event_type)->toBe('created');
    expect($selectedLog->resource_type)->toBe('Survey');
    expect($selectedLog->resource_id)->toBe(1);
    expect($selectedLog->after)->not->toBeNull();
    expect($selectedLog->created_at)->not->toBeNull();
});

it('displays user information in audit log modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->createLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog->performed_by)->toBe($this->user1->id);
    expect($selectedLog->user)->not->toBeNull();
    expect($selectedLog->user->first_name)->toBe('John');
    expect($selectedLog->user->last_name)->toBe('Doe');
    expect($selectedLog->user->email)->toBe('user1@example.com');
});

it('displays creation changes in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->createLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    $after = $selectedLog->after;
    
    expect($after['title'])->toBe('Customer Satisfaction Survey');
    expect($after['description'])->toBe('A survey about customer satisfaction');
    expect($after['status'])->toBe('draft');
});

it('displays update changes with old and new values in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->updateLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    $before = $selectedLog->before;
    $after = $selectedLog->after;
    
    expect($before['first_name'])->toBe('John');
    expect($after['first_name'])->toBe('Jane');
    expect($before['email'])->toBe('john@example.com');
    expect($after['email'])->toBe('jane@example.com');
});

it('displays deletion changes in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->deleteLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog->event_type)->toBe('deleted');
    expect($selectedLog->before)->not->toBeNull();
    expect($selectedLog->after)->toBeNull();
});

it('displays restoration changes in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->restoreLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog->event_type)->toBe('restored');
    expect($selectedLog->resource_type)->toBe('Survey');
    expect($selectedLog->resource_id)->toBe(5);
    expect($selectedLog->after['deleted_at'])->toBeNull();
});

it('displays lock changes with reason in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->lockLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog->event_type)->toBe('locked');
    expect($selectedLog->before['is_locked'])->toBeFalse();
    expect($selectedLog->after['is_locked'])->toBeTrue();
    expect($selectedLog->after['lock_reason'])->toBe('Violates community guidelines');
});

it('displays resource type and ID in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->createLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog->resource_type)->toBe('Survey');
    expect($selectedLog->resource_id)->toBe(1);
});

it('can switch between different audit logs in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->createLog->id)
        ->assertSet('selectedAuditLogId', $this->createLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    expect($selectedLog->event_type)->toBe('created');
    
    // Switch to update log
    $component->call('viewAuditLog', $this->updateLog->id)
        ->assertSet('selectedAuditLogId', $this->updateLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    expect($selectedLog->event_type)->toBe('updated');
});

it('modal shows null when no audit log selected', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('selectedAuditLogId', null);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog)->toBeNull();
});

it('displays timestamp in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->createLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    
    expect($selectedLog->created_at)->not->toBeNull();
    expect($selectedLog->created_at)->toBeInstanceOf(Carbon::class);
});

it('displays all change fields for complex updates in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->updateLog->id);
    
    $selectedLog = $component->get('selectedAuditLog');
    $changedFields = $selectedLog->changed_fields;
    
    expect($changedFields)->toContain('first_name');
    expect($changedFields)->toContain('email');
    expect(count($changedFields))->toBe(2);
});

it('handles different resource types in modal', function () {
    Auth::login($this->superAdmin);
    
    // Test Survey resource
    $component = Livewire::test(LogsIndex::class)
        ->call('viewAuditLog', $this->createLog->id);
    
    expect($component->get('selectedAuditLog')->resource_type)->toBe('Survey');
    
    // Test User resource
    $component->call('viewAuditLog', $this->updateLog->id);
    expect($component->get('selectedAuditLog')->resource_type)->toBe('User');
});
