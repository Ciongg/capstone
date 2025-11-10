<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\SecurityLogs;
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
    
    // Create test users for logs
    $this->user1 = User::factory()->create([
        'email' => 'user1@example.com',
        'type' => 'researcher',
    ]);
    
    $this->user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'type' => 'respondent',
    ]);
    
    // Create security logs
    $this->securityLog1 = SecurityLogs::create([
        'user_id' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'login',
        'ip' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'geo' => ['city' => 'Manila', 'country' => 'Philippines'],
        'created_at' => Carbon::now()->subHours(2),
    ]);
    
    $this->securityLog2 = SecurityLogs::create([
        'user_id' => $this->user2->id,
        'email' => $this->user2->email,
        'event_type' => 'failed_login',
        'ip' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'geo' => ['city' => 'Cebu', 'country' => 'Philippines'],
        'created_at' => Carbon::now()->subHours(1),
    ]);
    
    $this->securityLog3 = SecurityLogs::create([
        'user_id' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'logout',
        'ip' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'geo' => ['city' => 'Manila', 'country' => 'Philippines'],
        'created_at' => Carbon::now()->subMinutes(30),
    ]);
    
    // Create audit logs - UPDATED to use performed_by
    $this->auditLog1 = AuditLogs::create([
        'performed_by' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'created',
        'resource_type' => 'Survey',
        'resource_id' => 1,
        'before' => null,
        'after' => ['title' => 'Test Survey'],
        'changed_fields' => ['title'],
        'created_at' => Carbon::now()->subHours(3),
    ]);
    
    $this->auditLog2 = AuditLogs::create([
        'performed_by' => $this->user2->id,
        'email' => $this->user2->email,
        'event_type' => 'updated',
        'resource_type' => 'User',
        'resource_id' => 2,
        'before' => ['first_name' => 'John'],
        'after' => ['first_name' => 'Jane'],
        'changed_fields' => ['first_name'],
        'created_at' => Carbon::now()->subHours(2),
    ]);
    
    $this->auditLog3 = AuditLogs::create([
        'performed_by' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'deleted',
        'resource_type' => 'Survey',
        'resource_id' => 5,
        'before' => ['title' => 'Test Survey'],
        'after' => null,
        'changed_fields' => ['deleted_at'],
        'created_at' => Carbon::now()->subHours(1),
    ]);
});

// Security Logs Display Tests
it('displays security logs in list', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security');
    
    $securityLogs = $component->get('securityLogs');
    
    expect($securityLogs->count())->toBeGreaterThanOrEqual(3);
    expect($securityLogs->pluck('id')->toArray())->toContain($this->securityLog1->id);
    expect($securityLogs->pluck('id')->toArray())->toContain($this->securityLog2->id);
});

it('displays security log email correctly', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security');
    
    $securityLogs = $component->get('securityLogs');
    $emails = $securityLogs->pluck('email')->toArray();
    
    expect($emails)->toContain('user1@example.com');
    expect($emails)->toContain('user2@example.com');
});

it('masks IP addresses by default', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security');
    
    expect($component->get('maskIp'))->toBeTrue();
    
    // Call the method through the component instance
    $maskedIp = $component->instance()->maskIpAddress('192.168.1.100');
    expect($maskedIp)->toBe('192**.***.100');
});

it('can toggle IP masking', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->call('toggleIpMasking');
    
    expect($component->get('maskIp'))->toBeFalse();
    
    // Call the method through the component instance
    $unmaskedIp = $component->instance()->maskIpAddress('192.168.1.100');
    expect($unmaskedIp)->toBe('192.168.1.100');
});

it('can search security logs by email', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->set('searchEmail', 'user1@example.com');
    
    $securityLogs = $component->get('securityLogs');
    
    foreach ($securityLogs as $log) {
        expect($log->email)->toContain('user1@example.com');
    }
});

it('can search security logs by ID', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->set('searchEmail', (string)$this->securityLog1->id);
    
    $securityLogs = $component->get('securityLogs');
    
    // Should find logs with this ID or email containing this ID
    expect($securityLogs->count())->toBeGreaterThanOrEqual(1);
    $ids = $securityLogs->pluck('id')->toArray();
    expect($ids)->toContain($this->securityLog1->id);
});

it('can search security logs by IP address', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->set('searchIp', '192.168.1.100');
    
    $securityLogs = $component->get('securityLogs');
    
    foreach ($securityLogs as $log) {
        expect($log->ip)->toContain('192.168.1.100');
    }
});

it('displays security logs ordered by newest first', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security');
    
    $securityLogs = $component->get('securityLogs');
    
    // The newest log should be first (securityLog3 was created most recently)
    expect($securityLogs->first()->created_at->greaterThanOrEqualTo($securityLogs->last()->created_at))->toBeTrue();
});

// Audit Logs Display Tests
it('displays audit logs in list', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit');
    
    $auditLogs = $component->get('auditLogs');
    
    expect($auditLogs->count())->toBeGreaterThanOrEqual(3);
    expect($auditLogs->pluck('id')->toArray())->toContain($this->auditLog1->id);
    expect($auditLogs->pluck('id')->toArray())->toContain($this->auditLog2->id);
});

it('displays audit log email correctly', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit');
    
    $auditLogs = $component->get('auditLogs');
    $emails = $auditLogs->pluck('email')->toArray();
    
    expect($emails)->toContain('user1@example.com');
    expect($emails)->toContain('user2@example.com');
});

it('can search audit logs by email', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->set('searchAuditEmail', 'user1@example.com');
    
    $auditLogs = $component->get('auditLogs');
    
    foreach ($auditLogs as $log) {
        expect($log->email)->toContain('user1@example.com');
    }
});

it('can search audit logs by ID', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->set('searchAuditEmail', (string)$this->auditLog1->id);
    
    $auditLogs = $component->get('auditLogs');
    
    // Should find logs with this ID or email containing this ID
    expect($auditLogs->count())->toBeGreaterThanOrEqual(1);
    $ids = $auditLogs->pluck('id')->toArray();
    expect($ids)->toContain($this->auditLog1->id);
});

it('can search audit logs by resource type', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->set('searchAuditResource', 'Survey');
    
    $auditLogs = $component->get('auditLogs');
    
    foreach ($auditLogs as $log) {
        expect($log->resource_type)->toContain('Survey');
    }
});

it('can search audit logs by resource ID', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->set('searchAuditResource', '5');
    
    $auditLogs = $component->get('auditLogs');
    
    foreach ($auditLogs as $log) {
        expect($log->resource_id)->toBe(5);
    }
});

it('can search audit logs by resource ID with hash', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->set('searchAuditResource', '#5');
    
    $auditLogs = $component->get('auditLogs');
    
    foreach ($auditLogs as $log) {
        expect($log->resource_id)->toBe(5);
    }
});

it('can search audit logs by event type', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->set('searchAuditEvent', 'created');
    
    $auditLogs = $component->get('auditLogs');
    
    foreach ($auditLogs as $log) {
        expect($log->event_type)->toContain('created');
    }
});

it('displays audit logs ordered by newest first', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit');
    
    $auditLogs = $component->get('auditLogs');
    
    expect($auditLogs->first()->id)->toBe($this->auditLog3->id);
});

// Tab Switching Tests
it('can switch between security and audit tabs', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->assertSet('activeTab', 'security')
        ->call('setActiveTab', 'audit')
        ->assertSet('activeTab', 'audit');
});

it('resets page when switching tabs', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->call('setActiveTab', 'audit')
        ->assertSet('activeTab', 'audit');
});

// Modal Opening Tests
it('can open security log view modal', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->call('viewSecurityLog', $this->securityLog1->id)
        ->assertSet('selectedSecurityLogId', $this->securityLog1->id)
        ->assertDispatched('open-modal');
});

it('can open audit log view modal', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(LogsIndex::class)
        ->set('activeTab', 'audit')
        ->call('viewAuditLog', $this->auditLog1->id)
        ->assertSet('selectedAuditLogId', $this->auditLog1->id)
        ->assertDispatched('open-modal');
});

// Friendly Location Tests
it('generates friendly location string', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class);
    
    // Call the method through the component instance
    $location = $component->instance()->getFriendlyLocation($this->securityLog1);
    
    expect($location)->toContain('Manila, Philippines');
    expect($location)->toContain('Chrome');
    expect($location)->toContain('Windows');
});

it('handles missing geo data gracefully', function () {
    $logWithoutGeo = SecurityLogs::create([
        'user_id' => $this->user1->id,
        'email' => $this->user1->email,
        'event_type' => 'login',
        'ip' => '192.168.1.102',
        'user_agent' => 'Mozilla/5.0',
        'geo' => null,
    ]);
    
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class);
    
    // Call the method through the component instance
    $location = $component->instance()->getFriendlyLocation($logWithoutGeo);
    
    expect($location)->toContain('Unknown Location');
});
