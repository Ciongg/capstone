<?php

use App\Livewire\Vouchers\VoucherVerify;
use App\Livewire\Vouchers\Modal\ShowRedeemVoucher;
use App\Models\Merchant;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\TestTimeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// Helper function to replace TestTimeService::setTestNow()
function mockCurrentTime($dateTime)
{
    Carbon::setTestNow($dateTime);
    TestTimeService::mock($dateTime); // Assuming TestTimeService::mock() exists
}

beforeEach(function () {
    // Create base data that can be used across tests
    $this->merchant = Merchant::create([
        'name' => 'Test Merchant',
        'merchant_code' => 'TESTCODE123'
    ]);
    
    $this->user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'testuser@example.com',
        'password' => bcrypt('password'),
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'respondent',
    ]);
    
    // Update to use correct field names and values from the database schema
    $this->reward = Reward::create([
        'name' => 'Test Reward',
        'description' => 'Test Description',
        'cost' => 50,
        'quantity' => 10,
        'type' => 'voucher', // Lowercase to match database enum constraint
        'status' => Reward::STATUS_AVAILABLE,
    ]);
});


it('shows valid result for active voucher with correct merchant code', function () {
    // Create redemption record with correct fields
    $redemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 50,
        'status' => RewardRedemption::STATUS_COMPLETED,
    ]);
    
    // Create a voucher
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Create a user voucher that's recently activated (still valid)
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $voucher->id,
        'reward_redemption_id' => $redemption->id,
        'status' => 'active',
        'activated_at' => Carbon::now()->subMinutes(5), // Activated 5 minutes ago
        'expires_at' => Carbon::now()->addMinutes(25), // Expires in 25 minutes
    ]);

    // Don't use TestTimeService::setTestNow() - just rely on Carbon::now()
    // We don't need to mock time for this test
    
    // Test verification with correct merchant code
    $component = Livewire::test(VoucherVerify::class, ['reference_no' => $voucher->reference_no])
        ->set('merchantCodeInput', $this->merchant->merchant_code)
        ->call('submitMerchantCode');
    
    // Refresh database records
    $userVoucher->refresh();
    $voucher->refresh();
    
    // Verify component state and database updates
    expect($component->get('valid'))->toBeTrue();
    expect($component->get('message'))->toBe('Valid! This voucher is real and has been marked as used.');
    expect($component->get('redeemed'))->toBeTrue();
    expect($userVoucher->status)->toBe('used');
    expect($voucher->availability)->toBe('used');
});

it('shows invalid result for already used voucher', function () {
    // Create redemption record with correct fields - removed reward_type
    $redemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 50,
        'status' => RewardRedemption::STATUS_COMPLETED,
    ]);
    
    // Create a used voucher
    $usedAt = Carbon::now()->subMinutes(30);
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'used',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Create a user voucher that's already been used
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $voucher->id,
        'reward_redemption_id' => $redemption->id,
        'status' => 'used',
        'activated_at' => Carbon::now()->subMinutes(60),
        'used_at' => $usedAt,
    ]);

    // Test verification of used voucher
    $component = Livewire::test(VoucherVerify::class, ['reference_no' => $voucher->reference_no])
        ->set('merchantCodeInput', $this->merchant->merchant_code)
        ->call('submitMerchantCode');
    
    // Verify component state
    expect($component->get('valid'))->toBeFalse();
    expect($component->get('message'))->toBe('Invalid! This voucher was used before.');
    expect($component->get('usedAt')->toDateTimeString())->toBe($usedAt->toDateTimeString());
});

it('returns error message for invalid merchant code', function () {
    // Create a voucher
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);

    // Test verification with wrong merchant code
    $component = Livewire::test(VoucherVerify::class, ['reference_no' => $voucher->reference_no])
        ->set('merchantCodeInput', 'WRONGCODE')
        ->call('submitMerchantCode');
    
    // Verify error message
    expect($component->get('valid'))->toBeFalse();
    expect($component->get('message'))->toBe('Incorrect merchant code for this voucher. Please check the code and try again.');
    expect($component->get('merchantCodeValidated'))->toBeFalse();
});

it('redirects QR code scan to correct verification page', function () {
    // Create a voucher
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Visit the verification URL
    $response = $this->get('/voucher/verify/TEST123456');
    
    // Verify response
    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toContain('Voucher Verification');
});

// New test for explicitly testing the verifyVoucher method
it('correctly verifies voucher status using verifyVoucher method', function () {
    // Create redemption record - removed reward_type
    $redemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 50,
        'status' => RewardRedemption::STATUS_COMPLETED,
    ]);
    
    // Create an active voucher
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Create an active user voucher
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $voucher->id,
        'reward_redemption_id' => $redemption->id,
        'status' => 'active',
        'activated_at' => Carbon::now()->subMinutes(5),
        'expires_at' => Carbon::now()->addMinutes(25),
    ]);

    // Explicitly test the verifyVoucher method
    $component = Livewire::test(VoucherVerify::class, ['reference_no' => $voucher->reference_no]);
    
    // Set voucher and merchantCodeValidated properties directly
    $component->set('voucher', $voucher);
    $component->set('merchantCodeValidated', true);
    
    // Call verifyVoucher directly
    $component->call('verifyVoucher');
    
    // Refresh the user voucher and voucher
    $userVoucher->refresh();
    $voucher->refresh();
    
    // Verify the voucher was marked as used
    expect($component->get('valid'))->toBeTrue();
    expect($component->get('message'))->toBe('Valid! This voucher is real and has been marked as used.');
    expect($component->get('redeemed'))->toBeTrue();
    expect($userVoucher->status)->toBe('used');
    expect($voucher->availability)->toBe('used');
});

// New test for testing the redeemVoucher method in ShowRedeemVoucher
it('activates voucher with redeemVoucher method', function () {
    // Create redemption record - removed reward_type
    $redemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 50,
        'status' => RewardRedemption::STATUS_COMPLETED,
    ]);
    
    // Create an available voucher
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Create an available user voucher
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $voucher->id,
        'reward_redemption_id' => $redemption->id,
        'status' => 'available',
    ]);

    // Test the redeemVoucher method
    $component = Livewire::test(ShowRedeemVoucher::class, ['userVoucherId' => $userVoucher->id]);
    $component->call('redeemVoucher');
    
    // Refresh the user voucher
    $userVoucher->refresh();
    
    // Verify the voucher was activated
    expect($userVoucher->status)->toBe('active');
    expect($userVoucher->activated_at)->not->toBeNull();
    expect($userVoucher->expires_at)->not->toBeNull();
    expect($component->get('showQrCodeView'))->toBeTrue();
});

// New test for full flow: redeem then verify voucher
it('completes full voucher flow from redeeming to verifying', function () {
    // Create redemption record - removed reward_type
    $redemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 50,
        'status' => RewardRedemption::STATUS_COMPLETED,
    ]);
    
    // Create an available voucher
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Create an available user voucher
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $voucher->id,
        'reward_redemption_id' => $redemption->id,
        'status' => 'available',
    ]);

    // STEP 1: Redeem the voucher (activate it)
    $redeemComponent = Livewire::test(ShowRedeemVoucher::class, ['userVoucherId' => $userVoucher->id]);
    $redeemComponent->call('redeemVoucher');
    
    // Refresh the user voucher
    $userVoucher->refresh();
    
    // Verify the voucher was activated
    expect($userVoucher->status)->toBe('active');
    expect($userVoucher->activated_at)->not->toBeNull();
    expect($userVoucher->expires_at)->not->toBeNull();

    // STEP 2: Verify the voucher at merchant
    $verifyComponent = Livewire::test(VoucherVerify::class, ['reference_no' => $voucher->reference_no])
        ->set('merchantCodeInput', $this->merchant->merchant_code)
        ->call('submitMerchantCode');
    
    // Refresh database records
    $userVoucher->refresh();
    $voucher->refresh();
    
    // Verify the voucher was successfully verified and used
    expect($verifyComponent->get('valid'))->toBeTrue();
    expect($verifyComponent->get('message'))->toBe('Valid! This voucher is real and has been marked as used.');
    expect($verifyComponent->get('redeemed'))->toBeTrue();
    expect($userVoucher->status)->toBe('used');
    expect($voucher->availability)->toBe('used');
});

it('identifies voucher as expired when expires_at timestamp is passed', function () {
    // Create redemption record
    $redemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 50,
        'status' => RewardRedemption::STATUS_COMPLETED,
    ]);
    
    // Create a voucher with correct fields
    $voucher = Voucher::create([
        'reference_no' => 'TEST123456',
        'reward_id' => $this->reward->id,
        'merchant_id' => $this->merchant->id,
        'availability' => 'available',
        'promo' => 'Test Promo',
        'cost' => 50,
    ]);
    
    // Mock current time for consistent testing
    $now = Carbon::parse('2023-01-01 12:00:00');
    mockCurrentTime($now);
    
    // Create a user voucher that's activated recently (only 15 minutes ago)
    // but has an expires_at timestamp that's already passed
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $voucher->id,
        'reward_redemption_id' => $redemption->id,
        'status' => 'active',
        'activated_at' => $now->copy()->subMinutes(15), // Only activated 15 minutes ago (should be valid based on 30-min rule)
        'expires_at' => $now->copy()->subMinutes(5), // But explicitly set to expire 5 minutes ago
    ]);
    
    // Test the voucher verification
    $verifyComponent = Livewire::test(VoucherVerify::class, ['reference_no' => $voucher->reference_no]);
    $verifyComponent->set('voucher', $voucher);
    $verifyComponent->set('merchantCodeValidated', true);
    $verifyComponent->call('verifyVoucher');
    
    // Refresh models from database
    $userVoucher->refresh();
    $voucher->refresh();
    
    // Verify the component shows expired status
    expect($verifyComponent->get('valid'))->toBeFalse();
    expect($verifyComponent->get('message'))->toBe('Invalid! This voucher has expired.');
    expect($verifyComponent->get('redeemed'))->toBeFalse();
    
    // Both should be marked as expired
    expect($userVoucher->status)->toBe('expired');
    expect($voucher->availability)->toBe('expired');
    
    // Reset Carbon mock
    Carbon::setTestNow(null);
});
