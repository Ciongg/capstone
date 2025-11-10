<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\SecurityLogs;
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
    
    // Create test user
    $this->user = User::factory()->create([
        'email' => 'testuser@example.com',
        'type' => 'researcher',
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);
    
    // Create comprehensive security log
    $this->securityLog = SecurityLogs::create([
        'user_id' => $this->user->id,
        'email' => $this->user->email,
        'event_type' => 'login',
        'ip' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'geo' => [
            'city' => 'Manila',
            'country' => 'Philippines',
            'region' => 'Metro Manila',
            'postal_code' => '1000',
        ],
        'created_at' => Carbon::now()->subHours(2),
    ]);
    
    // Create failed login attempt
    $this->failedLoginLog = SecurityLogs::create([
        'user_id' => $this->user->id,
        'email' => $this->user->email,
        'event_type' => 'failed_login',
        'ip' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'geo' => ['city' => 'Cebu', 'country' => 'Philippines'],
        'created_at' => Carbon::now()->subHours(1),
    ]);
    
    // Create logout log
    $this->logoutLog = SecurityLogs::create([
        'user_id' => $this->user->id,
        'email' => $this->user->email,
        'event_type' => 'logout',
        'ip' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'geo' => ['city' => 'Manila', 'country' => 'Philippines'],
        'created_at' => Carbon::now()->subMinutes(30),
    ]);
});

it('can open security log view modal', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->call('viewSecurityLog', $this->securityLog->id)
        ->assertSet('selectedSecurityLogId', $this->securityLog->id)
        ->assertDispatched('open-modal');
});

it('displays complete security log information in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('activeTab', 'security')
        ->call('viewSecurityLog', $this->securityLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    // Verify all log information is available
    expect($selectedLog->id)->toBe($this->securityLog->id);
    expect($selectedLog->email)->toBe('testuser@example.com');
    expect($selectedLog->event_type)->toBe('login');
    expect($selectedLog->ip)->toBe('192.168.1.100');
    expect($selectedLog->user_agent)->toContain('Chrome');
    expect($selectedLog->user_agent)->toContain('Windows');
    expect($selectedLog->geo['city'])->toBe('Manila');
    expect($selectedLog->geo['country'])->toBe('Philippines');
    expect($selectedLog->created_at)->not->toBeNull();
});

it('displays user information in security log modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->securityLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog->user_id)->toBe($this->user->id);
    expect($selectedLog->user)->not->toBeNull();
    expect($selectedLog->user->first_name)->toBe('Test');
    expect($selectedLog->user->last_name)->toBe('User');
    expect($selectedLog->user->email)->toBe('testuser@example.com');
});

it('displays failed login details in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->failedLoginLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog->event_type)->toBe('failed_login');
    expect($selectedLog->email)->toBe('testuser@example.com');
    expect($selectedLog->ip)->toBe('192.168.1.101');
    expect($selectedLog->geo['city'])->toBe('Cebu');
});

it('displays logout details in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->logoutLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog->event_type)->toBe('logout');
    expect($selectedLog->email)->toBe('testuser@example.com');
});

it('displays geo location data in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->securityLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    $geo = $selectedLog->geo;
    
    expect($geo)->not->toBeNull();
    expect($geo['city'])->toBe('Manila');
    expect($geo['country'])->toBe('Philippines');
    expect($geo['region'])->toBe('Metro Manila');
    expect($geo['postal_code'])->toBe('1000');
});

it('handles security log without geo data in modal', function () {
    $logWithoutGeo = SecurityLogs::create([
        'user_id' => $this->user->id,
        'email' => $this->user->email,
        'event_type' => 'login',
        'ip' => '10.0.0.1',
        'user_agent' => 'Test Agent',
        'geo' => null,
    ]);
    
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $logWithoutGeo->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog->geo)->toBeNull();
    expect($selectedLog->ip)->toBe('10.0.0.1');
    expect($selectedLog->event_type)->toBe('login');
});

it('can switch between different security logs in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->securityLog->id)
        ->assertSet('selectedSecurityLogId', $this->securityLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    expect($selectedLog->event_type)->toBe('login');
    
    // Switch to failed login log
    $component->call('viewSecurityLog', $this->failedLoginLog->id)
        ->assertSet('selectedSecurityLogId', $this->failedLoginLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    expect($selectedLog->event_type)->toBe('failed_login');
});

it('modal shows null when no security log selected', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->set('selectedSecurityLogId', null);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog)->toBeNull();
});

it('displays complete user agent information', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->securityLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog->user_agent)->toBe('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
});

it('displays timestamp in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(LogsIndex::class)
        ->call('viewSecurityLog', $this->securityLog->id);
    
    $selectedLog = $component->get('selectedSecurityLog');
    
    expect($selectedLog->created_at)->not->toBeNull();
    expect($selectedLog->created_at)->toBeInstanceOf(Carbon::class);
});
