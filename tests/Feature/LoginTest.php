<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

//before each test initialize these values
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
    $response->assertSeeLivewire('auth.login'); // checks that the Livewire login component renders
});

it('can authenticate users using login screen', function () {
    Livewire::test('auth.login')
        ->set('email', 'active@example.com') //sets the $email property on the Livewire component
        ->set('password', 'password123') //sets the $password
        ->call('attemptLogin') //calls the login function to use these variables
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
        ->assertHasErrors('email');
    
    $this->assertFalse(Auth::check());
});

it('reactivates inactive user accounts on login', function () {
    Livewire::test('auth.login')
    ->set('email', 'inactive@example.com')
    ->set('password', 'password123')
    ->call('attemptLogin');
    
    // Verify the user is authenticated
    $this->assertTrue(Auth::check());
    
    // Check that the user is now active
    $this->inactiveUser->refresh();
    expect($this->inactiveUser->is_active)->toBeTrue();
    
});

it('cannot authenticate with archived account', function () {
    // Direct check of the error response pattern instead of specific message
    Livewire::test('auth.login')
    ->set('email', 'archived@example.com')
        ->set('password', 'password123')
        ->call('attemptLogin')
        ->assertHasErrors(['email']);
        
        $this->assertFalse(Auth::check());
        
    });
    
    it('can check and update institution status on login', function () {
        // Create a user with researcher type but no matching institution
        $researcher = User::factory()->create([
            'email' => 'researcher@random.edu.ph',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
            'is_active' => true,
        ]);
        
        // Login and check if downgraded
        Livewire::test('auth.login')
        ->set('email', 'researcher@random.edu.ph')
        ->set('password', 'password123')
        ->call('attemptLogin')
        ->assertHasNoErrors();
        
        $researcher->refresh();
        expect($researcher->type)->toBe('respondent');
        expect(session('account-downgrade'))->not->toBeEmpty();
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