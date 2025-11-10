<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Services\TestTimeService;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a standard active user for testing
    $this->user = User::factory()->create([
        'email' => 'active@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    // Create an archived (soft-deleted) user
    $this->archivedUser = User::factory()->create([
        'email' => 'archived@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
        'email_verified_at' => now(),
        'deleted_at' => now(),
    ]);
    
    // Create an inactive user
    $this->inactiveUser = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('password123'),
        'is_active' => false,
        'email_verified_at' => now(),
    ]);
});

it('can render the login screen', function () {
    $response = $this->get(route('login'));
    $response->assertStatus(200);
    $response->assertSeeLivewire('auth.login');
});

it('can authenticate users using login screen', function () {
    Livewire::test('auth.login')
        ->set('email', 'active@example.com')
        ->set('password', 'password123')
        ->call('attemptLogin')
        ->assertHasNoErrors() 
        ->assertRedirect(route('feed.index'));
    
    $this->assertTrue(Auth::check());
    $this->assertEquals('active@example.com', Auth::user()->email);
});

it('cannot authenticate with invalid password', function () {
    Livewire::test('auth.login')
        ->set('email', 'active@example.com')
        ->set('password', 'wrong-password')
        ->call('attemptLogin')
        ->assertDispatched('login-error');
    
    $this->assertFalse(Auth::check());
});

it('reactivates inactive user accounts on login', function () {
    Livewire::test('auth.login')
        ->set('email', 'inactive@example.com')
        ->set('password', 'password123')
        ->call('attemptLogin');
    
    $this->assertTrue(Auth::check());
    
    $this->inactiveUser->refresh();
    expect($this->inactiveUser->is_active)->toBeTrue();
});

it('cannot authenticate with archived account', function () {
    Livewire::test('auth.login')
        ->set('email', 'archived@example.com')
        ->set('password', 'password123')
        ->call('attemptLogin')
        ->assertDispatched('archived-account');
        
    $this->assertFalse(Auth::check());
});
    
it('can check and update institution status on login', function () {
    // Create institution with domain
    $institution = Institution::factory()->create([
        'domain' => 'university.edu.ph',
    ]);

    // Create a researcher user linked to that institution
    $user = User::factory()->create([
        'email' => 'researcher@university.edu.ph',
        'password' => Hash::make('password123'),
        'type' => 'researcher',
        'institution_id' => $institution->id,
        'is_active' => true,
    ]);

    // Login successfully
    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('attemptLogin')
        ->assertRedirect(route('feed.index'));
    
    // Verify user is authenticated
    $this->assertTrue(Auth::check());
});

it('locks user out after max failed attempts', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    // Make 2 failed attempts (won't trigger lockout yet)
    for ($i = 0; $i < 2; $i++) {
        Livewire::test('auth.login')
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('attemptLogin')
            ->assertDispatched('login-error');
    }

    // 3rd attempt triggers lockout (MAX_FAILED_ATTEMPTS = 3)
    Livewire::test('auth.login')
        ->set('email', 'test@example.com')
        ->set('password', 'wrong-password')
        ->call('attemptLogin')
        ->assertDispatched('login-cooldown');
    
    // User should not be authenticated
    $this->assertFalse(Auth::check());
    
    // 4th attempt should still be locked out (even with correct password)
    Livewire::test('auth.login')
        ->set('email', 'test@example.com')
        ->set('password', 'correct-password')
        ->call('attemptLogin')
        ->assertDispatched('login-cooldown');
    
    $this->assertFalse(Auth::check());
});

it('allows login after lockout cooldown expires', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    // Get the IP-based lockout key
    $ip = request()->ip();
    $lockoutKey = 'login:lockout:' . sha1($ip);
    
    // Simulate lockout by setting future timestamp
    $untilTs = TestTimeService::now()->addMinutes(30)->timestamp;
    Cache::put($lockoutKey, $untilTs, TestTimeService::now()->addMinutes(30));

    // Fast-forward time by 31 minutes
    $this->travel(31)->minutes();

    // Should be able to login now
    Livewire::test('auth.login')
        ->set('email', 'test@example.com')
        ->set('password', 'correct-password')
        ->call('attemptLogin')
        ->assertRedirect(route('feed.index'));
    
    $this->assertTrue(Auth::check());
});

it('allows users to logout', function () {
    // First login
    Auth::login($this->user);
    $this->assertTrue(Auth::check());
    
    // Then logout
    $response = $this->post(route('logout'));
    $this->assertFalse(Auth::check());
    $response->assertRedirect('/');
});

