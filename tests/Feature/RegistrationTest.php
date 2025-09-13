<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\EmailVerification;
use App\Models\Institution;
use App\Services\BrevoService;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

// Set up test environment to mock email sending
beforeEach(function () {
    // Mock the BrevoService
    $this->mockBrevoService = Mockery::mock(BrevoService::class);
    $this->mockBrevoService->shouldReceive('sendOtpEmail')->andReturn(true);
    
    // Bind the mock to the container
    app()->instance(BrevoService::class, $this->mockBrevoService);
});



it('can render the register screen', function () {
    $response = $this->get(route('register'));
    $response->assertSeeLivewire('auth.register');  // checks that the Livewire registern component renders

});

it('redirects authenticated users accessing register to feed', function () {
    // Create and authenticate a user
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    //acts as a authenticated user or that user we created an instance for ontop
    $this->actingAs($user);
    
    // Try to access register page when already authenticated
    $response = $this->get(route('register'));
    
    $response->assertRedirect('/');
});

it('can register a new user and send OTP', function () {
    Livewire::test('auth.register')
        ->set('first_name', 'Jane')
        ->set('last_name', 'Doe')
        ->set('email', 'jane@example.com')
        ->set('phone_number', '09123456789')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('registerUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('email_verifications', [
        'email' => 'jane@example.com',
    ]);
});

it('can verify OTP and create user', function () {
    // Simulate OTP creation
    EmailVerification::create([
        'email' => 'john@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test('auth.register')
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('pendingEmail', 'john@example.com')
        ->set('otp_code', '123456')
        ->call('verifyOtp')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);
    
    // Assert that the user is authenticated after OTP verification
    $this->assertTrue(Auth::check());
});

it('assigns researcher role for verified institution domain', function () {
    // Create a verified institution with its domain
    Institution::factory()->create([
        'name' => 'Adamson University',
        'domain' => 'adamson.edu.ph',
    ]);

    Livewire::test('auth.register')
        ->set('first_name', 'School')
        ->set('last_name', 'Researcher')
        ->set('email', 'user@adamson.edu.ph')
        ->set('phone_number', '09123456780')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('registerUser')
        ->assertHasNoErrors();

    // Simulate OTP creation and verification
    EmailVerification::create([
        'email' => 'user@adamson.edu.ph',
        'otp_code' => '654321',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test('auth.register')
        ->set('pendingEmail', 'user@adamson.edu.ph')
        ->set('otp_code', '654321')
        ->call('verifyOtp')
        ->assertHasNoErrors();

    $user = User::where('email', 'user@adamson.edu.ph')->first();
    expect($user->type)->toBe('researcher');
});

it('assigns respondent role for non-school domain', function () {
    Livewire::test('auth.register')
        ->set('first_name', 'Normal')
        ->set('last_name', 'User')
        ->set('email', 'user@gmail.com')
        ->set('phone_number', '09123456781')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('registerUser')
        ->assertHasNoErrors();

    // Simulate OTP creation and verification
    EmailVerification::create([
        'email' => 'user@gmail.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test('auth.register')
        ->set('pendingEmail', 'user@gmail.com')
        ->set('otp_code', '123456')
        ->call('verifyOtp')
        ->assertHasNoErrors();

    $user = User::where('email', 'user@gmail.com')->first();
    expect($user->type)->toBe('respondent');
});

it('assigns respondent role for educational domain not in verified institutions list', function () {
    // No institution created for this domain in the database
    
    Livewire::test('auth.register')
        ->set('first_name', 'Edu')
        ->set('last_name', 'NonVerified')
        ->set('email', 'user@unverified-university.edu.ph')
        ->set('phone_number', '09123456782')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('registerUser')
        ->assertHasNoErrors();

    // Simulate OTP creation and verification
    EmailVerification::create([
        'email' => 'user@unverified-university.edu.ph',
        'otp_code' => '456789',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test('auth.register')
        ->set('pendingEmail', 'user@unverified-university.edu.ph')
        ->set('otp_code', '456789')
        ->call('verifyOtp')
        ->assertHasNoErrors();

    $user = User::where('email', 'user@unverified-university.edu.ph')->first();
    expect($user->type)->toBe('respondent');
});

it('assigns respondent role for similar but non-matching institution domain', function () {
    // Create a verified institution
    Institution::factory()->create([
        'name' => 'Adamson University',
        'domain' => 'adamson.edu.ph',
    ]);
    
    // Test with a similar but non-matching domain
    Livewire::test('auth.register')
        ->set('first_name', 'Similar')
        ->set('last_name', 'Domain')
        ->set('email', 'user@adamsonuniversity.edu.ph') // Similar but not matching 'adamson.edu.ph'
        ->set('phone_number', '09123456783')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('registerUser')
        ->assertHasNoErrors();

    // Simulate OTP creation and verification
    EmailVerification::create([
        'email' => 'user@adamsonuniversity.edu.ph',
        'otp_code' => '789012',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test('auth.register')
        ->set('pendingEmail', 'user@adamsonuniversity.edu.ph')
        ->set('otp_code', '789012')
        ->call('verifyOtp')
        ->assertHasNoErrors();

    $user = User::where('email', 'user@adamsonuniversity.edu.ph')->first();
    expect($user->type)->toBe('respondent');
});

it('redirects to feed after successful registration and verification', function () {
    // Simulate the OTP verification process
    EmailVerification::create([
        'email' => 'redirect@example.com',
        'otp_code' => '555555',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test('auth.register')
        ->set('first_name', 'Redirect')
        ->set('last_name', 'Test')
        ->set('pendingEmail', 'redirect@example.com')
        ->set('otp_code', '555555')
        ->call('verifyOtp')
        ->assertHasNoErrors();

    // Check that the user is logged in
    $this->assertTrue(Auth::check());
    
    // Navigate to any authenticated route
    $response = $this->get(route('feed.index'));
    $response->assertStatus(200);
});


it('prevents registration with duplicate credentials', function () {
    // Create a user first
    User::factory()->create([
        'email' => 'jane@example.com',
        'phone_number' => '09123456789',
    ]);

    // Try to register again with the same email + phone
    Livewire::test('auth.register')
        ->set('first_name', 'Jane2')
        ->set('last_name', 'Doe2')
        ->set('email', 'jane@example.com') // duplicate email
        ->set('phone_number', '09123456789') // duplicate phone
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('registerUser')
        ->assertHasErrors(['email', 'phone_number']);
});