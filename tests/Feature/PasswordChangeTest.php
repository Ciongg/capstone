<?php

use App\Models\User;
use App\Models\Institution;
use App\Models\EmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use App\Services\BrevoService;
use App\Livewire\Auth\ChangePasswordModal;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test institution
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test-university.edu'
    ]);

    // Create a test user
    $this->user = User::create([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'email' => 'changetest@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
        'password' => Hash::make('CurrentPassword123'),
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true
    ]);
    
    // Mock the BrevoService
    $this->mockBrevoService = Mockery::mock(BrevoService::class);
    $this->mockBrevoService->shouldReceive('sendPasswordChangeEmail')->andReturn(true);
    
    // Bind the mock to the container
    app()->instance(BrevoService::class, $this->mockBrevoService);
});

it('can render change password modal when authenticated', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->assertSuccessful()
        ->assertSet('currentStep', 'verify')
        ->assertSee('Verify Your Identity')
        ->assertSee('Current Password');
});

it('verifies current password before sending OTP', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('current_password', 'CurrentPassword123')
        ->call('sendVerificationCode')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 'otp')
        ->assertDispatched('otp-sent');
        
    // Verify that an OTP record was created
    $this->assertDatabaseHas('email_verifications', [
        'email' => 'changetest@example.com',
    ]);
});

it('shows error for incorrect current password', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('current_password', 'WrongPassword123')
        ->call('sendVerificationCode')
        ->assertDispatched('current-password-error')
        ->assertSet('currentStep', 'verify'); // Should stay on verify step
        
    // Verify no OTP was created
    $this->assertDatabaseMissing('email_verifications', [
        'email' => 'changetest@example.com',
    ]);
});

it('requires current password field to be filled', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('current_password', '')
        ->call('sendVerificationCode')
        ->assertDispatched('validation-error');
});

it('can verify OTP and move to password change step', function () {
    $this->actingAs($this->user);
    
    // Create an OTP verification record
    EmailVerification::create([
        'email' => 'changetest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('otp_code', '123456')
        ->set('currentStep', 'otp')
        ->call('verifyOtp')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 'password');
});

it('shows error for invalid OTP', function () {
    $this->actingAs($this->user);
    
    // Create an OTP verification record
    EmailVerification::create([
        'email' => 'changetest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('otp_code', '654321') // Wrong OTP
        ->set('currentStep', 'otp')
        ->call('verifyOtp')
        ->assertDispatched('otp-error')
        ->assertSet('currentStep', 'otp'); // Step should not change
});

it('shows error for expired OTP', function () {
    $this->actingAs($this->user);
    
    // Create an expired OTP verification record
    EmailVerification::create([
        'email' => 'changetest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->subMinutes(10), // Expired
    ]);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('otp_code', '123456')
        ->set('currentStep', 'otp')
        ->call('verifyOtp')
        ->assertDispatched('otp-error')
        ->assertSet('currentStep', 'otp'); // Step should not change
});

it('can change password with valid data', function () {
    $this->actingAs($this->user);
    
    // Create an OTP verification record
    EmailVerification::create([
        'email' => 'changetest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'password')
        ->set('new_password', 'NewPassword123')
        ->set('new_password_confirmation', 'NewPassword123')
        ->call('changePassword')
        ->assertHasNoErrors()
        ->assertDispatched('password-changed-success')
        ->assertSet('showSuccess', true);
    
    // Verify the password was updated
    $this->user->refresh();
    expect(Hash::check('NewPassword123', $this->user->password))->toBeTrue();
    expect(Hash::check('CurrentPassword123', $this->user->password))->toBeFalse();
    
    // Verify the OTP record was deleted
    $this->assertDatabaseMissing('email_verifications', [
        'email' => 'changetest@example.com',
    ]);
});

it('enforces password confirmation match', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'password')
        ->set('new_password', 'NewPassword123')
        ->set('new_password_confirmation', 'DifferentPassword123')
        ->call('changePassword')
        ->assertDispatched('password-mismatch')
        ->assertSet('currentStep', 'password'); // Step should not change
});

it('enforces minimum password length', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'password')
        ->set('new_password', 'Short1')
        ->set('new_password_confirmation', 'Short1')
        ->call('changePassword')
        ->assertDispatched('password-length-error')
        ->assertSet('currentStep', 'password'); // Step should not change
});

it('prevents setting new password same as current password', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'password')
        ->set('new_password', 'CurrentPassword123')
        ->set('new_password_confirmation', 'CurrentPassword123')
        ->call('changePassword')
        ->assertDispatched('password-same-as-old')
        ->assertSet('currentStep', 'password'); // Step should not change
    
    // Verify password was not changed
    $this->user->refresh();
    expect(Hash::check('CurrentPassword123', $this->user->password))->toBeTrue();
});

it('can resend OTP code', function () {
    $this->actingAs($this->user);
    
    // Create initial OTP
    EmailVerification::create([
        'email' => 'changetest@example.com',
        'otp_code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'otp')
        ->call('resendOtp')
        ->assertDispatched('otp-sent')
        ->assertSet('resendCooldown', true);
    
    // Verify a new OTP was created
    $this->assertDatabaseHas('email_verifications', [
        'email' => 'changetest@example.com',
    ]);
});

it('enforces cooldown on resend OTP', function () {
    $this->actingAs($this->user);
    
    $component = Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'otp')
        ->set('resendCooldown', true)
        ->set('resendCooldownSeconds', 60)
        ->call('resendOtp');
    
    // Should not dispatch otp-sent when cooldown is active
    // The resendCooldown should still be true
    expect($component->get('resendCooldown'))->toBeTrue();
});

it('decrements cooldown timer correctly', function () {
    $this->actingAs($this->user);
    
    $component = Livewire::test(ChangePasswordModal::class)
        ->set('resendCooldown', true)
        ->set('resendCooldownSeconds', 5);
    
    // Call decrementCooldown multiple times
    $component->call('decrementCooldown');
    expect($component->get('resendCooldownSeconds'))->toBe(4);
    
    $component->call('decrementCooldown');
    expect($component->get('resendCooldownSeconds'))->toBe(3);
    
    // Decrement to zero
    $component->call('decrementCooldown');
    $component->call('decrementCooldown');
    $component->call('decrementCooldown');
    
    expect($component->get('resendCooldownSeconds'))->toBe(0);
    expect($component->get('resendCooldown'))->toBeFalse();
});

it('resets form correctly', function () {
    $this->actingAs($this->user);
    
    $component = Livewire::test(ChangePasswordModal::class)
        ->set('current_password', 'SomePassword')
        ->set('otp_code', '123456')
        ->set('new_password', 'NewPassword')
        ->set('new_password_confirmation', 'NewPassword')
        ->set('currentStep', 'password')
        ->set('showSuccess', true)
        ->call('resetForm');
    
    expect($component->get('current_password'))->toBe('');
    expect($component->get('otp_code'))->toBe('');
    expect($component->get('new_password'))->toBe('');
    expect($component->get('new_password_confirmation'))->toBe('');
    expect($component->get('currentStep'))->toBe('verify');
    expect($component->get('showSuccess'))->toBeFalse();
    expect($component->get('resendCooldown'))->toBeFalse();
});

it('validates OTP code format', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'otp')
        ->set('otp_code', '12345') // Only 5 digits
        ->call('verifyOtp')
        ->assertDispatched('validation-error');
    
    Livewire::test(ChangePasswordModal::class)
        ->set('currentStep', 'otp')
        ->set('otp_code', '1234567') // 7 digits
        ->call('verifyOtp')
        ->assertDispatched('validation-error');
});

it('handles complete password change flow', function () {
    $this->actingAs($this->user);
    
    // Step 1: Send verification code
    $component = Livewire::test(ChangePasswordModal::class)
        ->set('current_password', 'CurrentPassword123')
        ->call('sendVerificationCode')
        ->assertSet('currentStep', 'otp')
        ->assertDispatched('otp-sent');
    
    // Get the OTP from database
    $verification = EmailVerification::where('email', 'changetest@example.com')->first();
    expect($verification)->not->toBeNull();
    
    // Step 2: Verify OTP
    $component
        ->set('otp_code', $verification->otp_code)
        ->call('verifyOtp')
        ->assertSet('currentStep', 'password');
    
    // Step 3: Set new password
    $component
        ->set('new_password', 'BrandNewPassword123')
        ->set('new_password_confirmation', 'BrandNewPassword123')
        ->call('changePassword')
        ->assertDispatched('password-changed-success')
        ->assertSet('showSuccess', true);
    
    // Verify password was changed
    $this->user->refresh();
    expect(Hash::check('BrandNewPassword123', $this->user->password))->toBeTrue();
    
    // Verify OTP was cleaned up
    $this->assertDatabaseMissing('email_verifications', [
        'email' => 'changetest@example.com',
    ]);
});
