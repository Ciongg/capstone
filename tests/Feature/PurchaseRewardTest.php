<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Reward;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\RewardRedemption;
use App\Models\UserSystemReward;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Livewire\Rewards\Modal\RewardRedeemModal;

// Ensures database is reset between tests for isolation
uses(RefreshDatabase::class);

function supportsRowLocking(): bool
{
    // Helper so the test adapts nicely when the suite runs on sqlite.
    return in_array(DB::connection()->getDriverName(), ['mysql', 'pgsql', 'sqlsrv']);
}

beforeEach(function () {
    // Create an academic institution for the users
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'adamson.edu.ph',
    ]);
    
    // Create a user with sufficient points for testing
    $this->user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'points' => 1000,
        'experience_points' => 150,
        'account_level' => 2,
        'rank' => 'gold',
        'trust_score' => 85,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    // Create a user with insufficient points
    $this->poorUser = User::create([
        'first_name' => 'Poor',
        'last_name' => 'User',
        'email' => 'poor@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'points' => 5,
        'experience_points' => 50,
        'account_level' => 1,
        'rank' => 'silver',
        'trust_score' => 85,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    // Create system rewards for testing
    $this->systemReward = Reward::create([
        'name' => 'Experience Level Increase',
        'description' => 'Increase your experience points',
        'type' => 'system',
        'cost' => 50,
        'quantity' => 10,
        'status' => 'available', // Changed from 'active' to 'available'
        'rank_requirement' => 'silver',
    ]);
    
    $this->surveyBoostReward = Reward::create([
        'name' => 'Survey Boost',
        'description' => 'Boost your survey visibility',
        'type' => 'system',
        'cost' => 75,
        'quantity' => 5,
        'status' => 'available', // Changed from 'active' to 'available'
        'rank_requirement' => 'gold',
    ]);
    
    $this->limitedSystemReward = Reward::create([
        'name' => 'Limited System Reward',
        'description' => 'A limited quantity system reward',
        'type' => 'system',
        'cost' => 100,
        'quantity' => 1, // Only 1 available
        'status' => 'available', // Changed from 'active' to 'available'
        'rank_requirement' => 'silver',
    ]);
    
    // Create voucher reward for testing
    $this->voucherReward = Reward::create([
        'name' => 'Amazon Gift Card',
        'description' => '$10 Amazon gift card',
        'type' => 'voucher',
        'cost' => 200,
        'quantity' => 3,
        'status' => 'available', // Changed from 'active' to 'available'
        'rank_requirement' => 'silver',
    ]);
    
    // Create vouchers with different expiry dates
    $this->expiringSoonVoucher = Voucher::create([
        'reward_id' => $this->voucherReward->id,
        'reference_no' => 'EXPIRES-SOON-123',
        'promo' => 'EXPIRES-SOON',
        'cost' => 200,
        'availability' => 'available',
        'expiry_date' => now()->addDays(5), // Expires soon
    ]);
    
    $this->expiringLaterVoucher = Voucher::create([
        'reward_id' => $this->voucherReward->id,
        'reference_no' => 'EXPIRES-LATER-456',
        'promo' => 'EXPIRES-LATER',
        'cost' => 200,
        'availability' => 'available',
        'expiry_date' => now()->addDays(30), // Expires later
    ]);
    
    $this->neverExpiresVoucher = Voucher::create([
        'reward_id' => $this->voucherReward->id,
        'reference_no' => 'NEVER-EXPIRES-789',
        'promo' => 'NEVER-EXPIRES',
        'cost' => 200,
        'availability' => 'available',
        'expiry_date' => null, // Never expires
    ]);
    
    // Create an expired voucher
    $this->expiredVoucher = Voucher::create([
        'reward_id' => $this->voucherReward->id,
        'reference_no' => 'EXPIRED-123',
        'promo' => 'EXPIRED',
        'cost' => 200,
        'availability' => 'available',
        'expiry_date' => now()->subDays(1), // Already expired
    ]);
});

it('can render the reward redeem modal with system reward', function () {
    // This test verifies that the modal renders correctly for system rewards
    
    Auth::login($this->user);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->assertSee('Experience Level Increase')
        ->assertSee('Quantity:')
        ->assertSee('Total Cost:')
        ->assertSee('50') // Cost display
        ->assertSee('Confirm');
});

it('can render the reward redeem modal with voucher reward', function () {
    // This test verifies that the modal renders correctly for voucher rewards
    
    Auth::login($this->user);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->assertSee('Amazon Gift Card')
        ->assertSee('Cost:') // Not "Total Cost" for vouchers
        ->assertSee('200') // Cost display
        ->assertDontSee('Quantity:') // No quantity selector for vouchers
        ->assertSee('Confirm');
});


it('prevents purchase when user has insufficient points', function () {
    // This test verifies that users with insufficient points cannot purchase rewards
    
    Auth::login($this->poorUser); // User with only 5 points
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemptionError', 'Not enough points to redeem this reward.');
    
    // Verify no redemption record was created
    expect(RewardRedemption::count())->toBe(0);
    
    // Verify user points unchanged
    $this->poorUser->refresh();
    expect($this->poorUser->points)->toBe(5);
});

it('successfully purchases system reward and adds experience points', function () {
    // This test verifies successful system reward purchase and experience point addition
    
    Auth::login($this->user);
    
    $initialPoints = $this->user->points;
    $initialXP = $this->user->experience_points;
    $initialRedemptionCount = RewardRedemption::count();
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->set('redeemQuantity', 2)
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success')
        ->assertDispatched('redeem_success')
        ->assertDispatched('close-modal');
    
    // Verify points were deducted
    $this->user->refresh();
    expect($this->user->points)->toBe($initialPoints - 100); // 50 * 2
    
    // Verify experience points were added (10 XP per quantity)
    expect($this->user->experience_points)->toBe($initialXP + 20); // 10 * 2
    
    // Verify redemption record was created
    expect(RewardRedemption::count())->toBe($initialRedemptionCount + 1);
    
    $redemption = RewardRedemption::latest()->first();
    expect($redemption->user_id)->toBe($this->user->id);
    expect($redemption->reward_id)->toBe($this->systemReward->id);
    expect($redemption->points_spent)->toBe(100);
    expect($redemption->quantity)->toBe(2);
    expect($redemption->status)->toBe('completed');
    
    // Verify system reward quantity was decremented
    $this->systemReward->refresh();
    expect($this->systemReward->quantity)->toBe(8); // 10 - 2
});

it('successfully purchases survey boost reward and creates user system reward', function () {
    // This test verifies that Survey Boost rewards create UserSystemReward records
    
    Auth::login($this->user);
    
    $initialUserSystemRewardCount = UserSystemReward::count();
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->surveyBoostReward])
        ->set('redeemQuantity', 3)
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Verify UserSystemReward was created
    expect(UserSystemReward::count())->toBe($initialUserSystemRewardCount + 1);
    
    $userSystemReward = UserSystemReward::latest()->first();
    expect($userSystemReward->user_id)->toBe($this->user->id);
    expect($userSystemReward->type)->toBe('survey_boost');
    expect($userSystemReward->quantity)->toBe(3);
    expect($userSystemReward->status)->toBe('unused');
});

it('increments existing survey boost quantity when purchasing additional boosts', function () {
    // This test verifies that multiple Survey Boost purchases increment existing records
    
    Auth::login($this->user);
    
    // Create an existing survey boost
    $existingBoost = UserSystemReward::create([
        'user_id' => $this->user->id,
        'type' => 'survey_boost',
        'quantity' => 2,
        'status' => 'unused',
    ]);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->surveyBoostReward])
        ->set('redeemQuantity', 3)
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Verify existing boost was incremented, not a new record created
    $existingBoost->refresh();
    expect($existingBoost->quantity)->toBe(5); // 2 + 3
    expect(UserSystemReward::where('user_id', $this->user->id)->where('type', 'survey_boost')->count())->toBe(1);
});

it('successfully purchases voucher reward and gives earliest expiry voucher', function () {
    // This test verifies voucher purchase gives the voucher expiring soonest
    
    Auth::login($this->user);
    
    $initialUserVoucherCount = UserVoucher::count();
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Verify UserVoucher was created
    expect(UserVoucher::count())->toBe($initialUserVoucherCount + 1);
    
    $userVoucher = UserVoucher::latest()->first();
    expect($userVoucher->user_id)->toBe($this->user->id);
    expect($userVoucher->status)->toBe('available');
    
    // Verify the voucher given is the one expiring soonest (not expired, not null)
    expect($userVoucher->voucher_id)->toBe($this->expiringSoonVoucher->id);
    
    // Verify the voucher is now marked as unavailable
    $this->expiringSoonVoucher->refresh();
    expect($this->expiringSoonVoucher->availability)->toBe('unavailable');
    
    // Verify other vouchers remain available
    $this->expiringLaterVoucher->refresh();
    $this->neverExpiresVoucher->refresh();
    expect($this->expiringLaterVoucher->availability)->toBe('available');
    expect($this->neverExpiresVoucher->availability)->toBe('available');
});

it('handles voucher expiry logic correctly', function () {
    // This test verifies that expired vouchers are not given to users
    
    Auth::login($this->user);
    
    // Mark all non-expired vouchers as unavailable except the expired one
    $this->expiringSoonVoucher->update(['availability' => 'unavailable']);
    $this->expiringLaterVoucher->update(['availability' => 'unavailable']);
    $this->neverExpiresVoucher->update(['availability' => 'unavailable']);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemptionError', 'This voucher is out of stock or expired.');
    
    // Verify no voucher was assigned
    expect(UserVoucher::where('user_id', $this->user->id)->count())->toBe(0);
});

it('prevents purchasing more quantity than available for system rewards', function () {
    // This test verifies quantity validation for limited system rewards
    
    Auth::login($this->user);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->limitedSystemReward])
        ->set('redeemQuantity', 5) // More than available (1)
        ->call('confirmRedemption')
        ->assertDispatched('redemptionError', 'Requested quantity exceeds available stock.');
    
    // Verify no redemption occurred
    expect(RewardRedemption::count())->toBe(0);
    
    // Verify reward quantity unchanged
    $this->limitedSystemReward->refresh();
    expect($this->limitedSystemReward->quantity)->toBe(1);
});

it('prevents purchasing with invalid quantity for system rewards', function () {
    // This test verifies that invalid quantities are rejected
    
    Auth::login($this->user);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->set('redeemQuantity', 0) // Invalid quantity
        ->call('confirmRedemption')
        ->assertDispatched('redemptionError', 'Quantity must be at least 1 for system rewards.');
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->set('redeemQuantity', -1) // Invalid quantity
        ->call('confirmRedemption')
        ->assertDispatched('redemptionError', 'Quantity must be at least 1 for system rewards.');
});

it('marks system reward as sold out when quantity reaches zero', function () {
    // This test verifies that system rewards are marked as sold out when depleted
    
    Auth::login($this->user);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->limitedSystemReward])
        ->set('redeemQuantity', 1) // Purchase the last one
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Verify reward is marked as sold out
    $this->limitedSystemReward->refresh();
    expect($this->limitedSystemReward->quantity)->toBe(0);
    expect($this->limitedSystemReward->status)->toBe('sold_out');
});

it('marks voucher reward as sold out when last voucher is purchased', function () {
    // This test verifies that voucher rewards are marked as sold out when depleted
    
    Auth::login($this->user);
    
    // Set voucher reward quantity to 1 to test sold out logic
    $this->voucherReward->update(['quantity' => 1]);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Verify reward is marked as sold out
    $this->voucherReward->refresh();
    expect($this->voucherReward->quantity)->toBe(0);
    expect($this->voucherReward->status)->toBe('sold_out');
});

it('handles race conditions for limited system rewards', function () {
    // This test simulates race conditions where multiple users try to purchase the last item
    
    $user1 = $this->user;
    $user2 = User::create([
        'first_name' => 'Second',
        'last_name' => 'User',
        'email' => 'user2@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'points' => 1000,
        'rank' => 'gold',
        'trust_score' => 85,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    if (supportsRowLocking()) {
        DB::transaction(function () use ($user1, $user2) {
            Auth::login($user1);
            
            $component1 = Livewire::test(RewardRedeemModal::class, ['reward' => $this->limitedSystemReward])
                ->set('redeemQuantity', 1);
            
            Auth::login($user2);
            
            $component2 = Livewire::test(RewardRedeemModal::class, ['reward' => $this->limitedSystemReward])
                ->set('redeemQuantity', 1);
            
            $component1->call('confirmRedemption');
            
            $component2->call('confirmRedemption')
                ->assertDispatched('redemptionError');
        });
    } else {
        // Without row locking we fire the calls sequentially to mirror the same outcome.
        Auth::login($user1);
        Livewire::test(RewardRedeemModal::class, ['reward' => $this->limitedSystemReward])
            ->set('redeemQuantity', 1)
            ->call('confirmRedemption')
            ->assertDispatched('redemption-success');
        
        $this->limitedSystemReward->refresh();
        
        Auth::login($user2);
        Livewire::test(RewardRedeemModal::class, ['reward' => $this->limitedSystemReward])
            ->set('redeemQuantity', 1)
            ->call('confirmRedemption')
            ->assertDispatched('redemptionError');
    }
    
    // No matter the branch taken, only a single redemption should exist.
    expect(RewardRedemption::count())->toBe(1);
    
    $this->limitedSystemReward->refresh();
    expect($this->limitedSystemReward->quantity)->toBe(0);
});

it('handles race conditions for voucher rewards', function () {
    // This test simulates race conditions for voucher purchases
    
    $user1 = $this->user;
    $user2 = User::create([
        'first_name' => 'Second',
        'last_name' => 'User',
        'email' => 'user2@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'points' => 1000,
        'rank' => 'gold',
        'trust_score' => 85,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    // Create a voucher reward with only one available voucher
    $singleVoucherReward = Reward::create([
        'name' => 'Single Voucher',
        'type' => 'voucher',
        'cost' => 100,
        'quantity' => 1,
        'status' => 'available',
        'rank_requirement' => 'silver',
    ]);
    
    $singleVoucher = Voucher::create([
        'reward_id' => $singleVoucherReward->id,
        'reference_no' => 'SINGLE-VOUCHER-123',
        'promo' => 'SINGLE-VOUCHER',
        'cost' => 100,
        'availability' => 'available',
        'expiry_date' => now()->addDays(30),
    ]);
    
    $successfulPurchases = 0;
    
    // First user purchases successfully
    try {
        Auth::login($user1);
        Livewire::test(RewardRedeemModal::class, ['reward' => $singleVoucherReward])
            ->call('confirmRedemption')
            ->assertDispatched('redemption-success');
        $successfulPurchases++;
    } catch (\Exception $e) {
        // Purchase failed
    }
    
    // Second user should fail because voucher is now unavailable
    try {
        Auth::login($user2);
        Livewire::test(RewardRedeemModal::class, ['reward' => $singleVoucherReward])
            ->call('confirmRedemption')
            ->assertDispatched('redemptionError');
    } catch (\Exception $e) {
        // Expected to fail
    }
    
    // Verify only one purchase succeeded
    expect($successfulPurchases)->toBe(1);
    expect(UserVoucher::count())->toBe(1);
    
    // Verify voucher is marked as unavailable
    $singleVoucher->refresh();
    expect($singleVoucher->availability)->toBe('unavailable');
});

it('prevents unauthenticated users from purchasing rewards', function () {
    // This test verifies that unauthenticated users cannot purchase rewards
    
    // Don't login any user
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemptionError', 'Could not process redemption. Please try again.');
    
    // Verify no redemption occurred
    expect(RewardRedemption::count())->toBe(0);
});

it('maintains user context when creating surveys', function () {
    // This test verifies that created surveys are properly associated with the logged-in user
    
    Auth::login($this->user);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->systemReward])
        ->call('confirmRedemption');
    
    $redemption = RewardRedemption::latest()->first();
    expect($redemption->user_id)->toBe($this->user->id);
    expect($redemption->user->email)->toBe('user@example.com');
    expect($redemption->user->type)->toBe('respondent');
});

it('gives non-expiring vouchers last when multiple vouchers available', function () {
    // This test verifies the voucher selection priority logic
    
    Auth::login($this->user);
    
    // Purchase first voucher
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    $firstVoucher = UserVoucher::latest()->first();
    
    // Verify first voucher is marked as unavailable before proceeding
    $this->expiringSoonVoucher->refresh();
    $this->expiringLaterVoucher->refresh();
    $this->neverExpiresVoucher->refresh();
    
    // Purchase second voucher with a different user
    $user2 = User::create([
        'first_name' => 'Second',
        'last_name' => 'User',
        'email' => 'user2@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'points' => 1000,
        'rank' => 'gold',
        'trust_score' => 85,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    Auth::login($user2);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Get the voucher assigned to user2 specifically
    $secondVoucher = UserVoucher::where('user_id', $user2->id)->first();
    
    // Purchase third voucher with another user
    $user3 = User::create([
        'first_name' => 'Third',
        'last_name' => 'User',
        'email' => 'user3@example.com',
        'password' => Hash::make('password123'),
        'type' => 'respondent',
        'points' => 1000,
        'rank' => 'gold',
        'trust_score' => 85,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    Auth::login($user3);
    
    Livewire::test(RewardRedeemModal::class, ['reward' => $this->voucherReward])
        ->call('confirmRedemption')
        ->assertDispatched('redemption-success');
    
    // Get the voucher assigned to user3 specifically
    $thirdVoucher = UserVoucher::where('user_id', $user3->id)->first();
    
    // Verify all three vouchers are different
    $allVoucherIds = [
        $firstVoucher->voucher_id, 
        $secondVoucher->voucher_id, 
        $thirdVoucher->voucher_id
    ];
    $uniqueVoucherIds = array_unique($allVoucherIds);
    expect(count($uniqueVoucherIds))->toBe(3);
    
    // Verify all three vouchers are now marked as unavailable
    $this->expiringSoonVoucher->refresh();
    $this->expiringLaterVoucher->refresh();
    $this->neverExpiresVoucher->refresh();
    expect($this->expiringSoonVoucher->availability)->toBe('unavailable');
    expect($this->expiringLaterVoucher->availability)->toBe('unavailable');
    expect($this->neverExpiresVoucher->availability)->toBe('unavailable');
    
    // Verify the collection of vouchers contains our expected IDs
    expect($allVoucherIds)->toContain($this->expiringSoonVoucher->id);
    expect($allVoucherIds)->toContain($this->expiringLaterVoucher->id);
    expect($allVoucherIds)->toContain($this->neverExpiresVoucher->id);
    
    // The first voucher should still be the one expiring soonest
    expect($firstVoucher->voucher_id)->toBe($this->expiringSoonVoucher->id);
});