<?php

use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use App\Services\BrevoService;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user
    $this->user = User::factory()->create([
        'email' => 'resettest@example.com',
        'password' => Hash::make('originalpassword'),
    ]);
    
    // Mock the BrevoService
    $this->mockBrevoService = Mockery::mock(BrevoService::class);
    $this->mockBrevoService->shouldReceive('sendPasswordResetEmail')->andReturn(true);
    
    // Bind the mock to the container
    app()->instance(BrevoService::class, $this->mockBrevoService);
});

it('can render forgot password modal from login page', function () {
    // First load the login page
    $response = $this->get(route('login'));
    $response->assertStatus(200);
    
    // Check that the forgot password component is included in the page
    $response->assertSeeLivewire('auth.forgot-password');
});

it('can request a password reset', function () {
    Livewire::test('auth.forgot-password')
        ->set('email', 'resettest@example.com')
        ->call('sendResetEmail')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 'otp');
        
    // Verify that an OTP record was created
    $this->assertDatabaseHas('email_verifications', [
        'email' => 'resettest@example.com',
    ]);
});

it('shows an error for non-existent email', function () {
    Livewire::test('auth.forgot-password')
        ->set('email', 'nonexistent@example.com')
        ->call('sendResetEmail')
        ->assertHasErrors('email');
        
    // Verify no OTP was created
    $this->assertDatabaseMissing('email_verifications', [
        'email' => 'nonexistent@example.com',
    ]);
});

it('can verify OTP and move to password reset step', function () {
    // Create an OTP verification record
    $verification = EmailVerification::create([
        'email' => 'resettest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    Livewire::test('auth.forgot-password')
        ->set('email', 'resettest@example.com')
        ->set('otp_code', '123456')
        ->call('verifyOtp')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 'password');
});

it('shows an error for invalid OTP', function () {
    // Create an OTP verification record
    $verification = EmailVerification::create([
        'email' => 'resettest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    Livewire::test('auth.forgot-password')
        ->set('email', 'resettest@example.com')
        ->set('otp_code', '654321') // Wrong OTP
        ->call('verifyOtp')
        ->assertHasErrors('otp_code');
});

it('shows an error for expired OTP', function () {
    // Create an expired OTP verification record
    $verification = EmailVerification::create([
        'email' => 'resettest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->subMinutes(10), // Expired
    ]);
    
    Livewire::test('auth.forgot-password')
        ->set('email', 'resettest@example.com')
        ->set('otp_code', '123456')
        ->call('verifyOtp')
        ->assertHasErrors('otp_code');
});

it('can reset password with valid data', function () {
    // Create an OTP verification record
    $verification = EmailVerification::create([
        'email' => 'resettest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    // Set the current step to password
    Livewire::test('auth.forgot-password')
        ->set('email', 'resettest@example.com')
        ->set('currentStep', 'password')
        ->set('new_password', 'newpassword123')
        ->set('new_password_confirmation', 'newpassword123')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertSet('showSuccess', true);
    
    // Verify the password was updated
    $this->user->refresh();
    $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    
    // Verify the OTP record was deleted
    $this->assertDatabaseMissing('email_verifications', [
        'email' => 'resettest@example.com',
    ]);
});

it('enforces password confirmation', function () {
    Livewire::test('auth.forgot-password')
        ->set('email', 'resettest@example.com')
        ->set('currentStep', 'password')
        ->set('new_password', 'newpassword123')
        ->set('new_password_confirmation', 'different-password')
        ->call('resetPassword')
        ->assertHasErrors(['new_password']);
});
