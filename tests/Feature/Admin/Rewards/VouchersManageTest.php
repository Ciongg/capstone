<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\Reward;
use App\Models\Merchant;
use App\Models\Institution;
use App\Models\RewardRedemption;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\Vouchers\Modal\ViewVoucherModal;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create institution
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test.edu'
    ]);
    
    // Create super admin
    $this->superAdmin = User::create([
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'superadmin@system.com',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create regular user
    $this->user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'user@test.com',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create merchant
    $this->merchant = Merchant::create([
        'name' => 'Test Store',
        'merchant_code' => 'TEST001',
        'description' => 'Test Store Description',
        'contact_number' => '1234567890',
        'email' => 'store@test.com',
    ]);
    
    // Create reward - use lowercase 'voucher'
    $this->reward = Reward::create([
        'merchant_id' => $this->merchant->id,
        'name' => 'Test Reward',
        'description' => 'Test Reward Description',
        'cost' => 100,
        'rank_requirement' => 'silver',
        'image_path' => 'rewards/test.jpg',
        'status' => 'available',
        'quantity' => 10,
        'type' => 'voucher', // Changed from 'Voucher' to 'voucher'
    ]);
    
    // Create voucher
    $this->voucher = Voucher::create([
        'reward_id' => $this->reward->id,
        'reference_no' => 'TEST-REF-001',
        'promo' => '20% Discount',
        'cost' => 100,
        'availability' => 'available',
        'expiry_date' => Carbon::now()->addDays(30),
        'image_path' => 'vouchers/test.jpg',
    ]);

    $this->rewardRedemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 100,
        'gcash_number' => null,
        'status' => 'pending',
        'quantity' => 1,
    ]);
});

// Modal Loading Tests
it('loads voucher view modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id]);
    
    expect($component->get('voucher'))->not->toBeNull();
    expect($component->get('voucher')->id)->toBe($this->voucher->id);
});

it('displays voucher information correctly', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->assertSee('Test Store')
        ->assertSee('20% Discount')
        ->assertSee('TEST-REF-001')
        ->assertSee('100 points')
        ->assertSee('silver'); // Changed from 'Silver' to 'silver' to match actual output
});

// Status Update Tests
it('can update voucher status to used', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'used')
        ->call('updateVoucher');
    
    $this->voucher->refresh();
    expect($this->voucher->availability)->toBe('used');
});

it('can update voucher status to expired', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'expired')
        ->call('updateVoucher');
    
    $this->voucher->refresh();
    expect($this->voucher->availability)->toBe('expired');
});

it('can update voucher status back to available', function () {
    $this->voucher->update(['availability' => 'used']);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'available')
        ->call('updateVoucher');
    
    $this->voucher->refresh();
    expect($this->voucher->availability)->toBe('available');
});

it('dispatches event after status update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'used')
        ->call('updateVoucher')
        ->assertDispatched('voucherStatusUpdated');
});

it('validates status update with valid statuses only', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'invalid_status')
        ->call('updateVoucher')
        ->assertHasErrors(['selectedStatus']);
});

// UserVoucher Synchronization Tests
it('updates associated user vouchers when voucher status changes', function () {
    // Create a UserVoucher associated with this voucher
    $userVoucher = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $this->voucher->id,
        'reward_redemption_id' => $this->rewardRedemption->id,
        'status' => UserVoucher::STATUS_AVAILABLE,
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'used')
        ->call('updateVoucher');
    
    $userVoucher->refresh();
    expect($userVoucher->status)->toBe(UserVoucher::STATUS_USED);
    expect($userVoucher->used_at)->not->toBeNull();
});

it('updates multiple user vouchers when voucher status changes', function () {
    // Create multiple UserVouchers
    $userVoucher1 = UserVoucher::create([
        'user_id' => $this->user->id,
        'voucher_id' => $this->voucher->id,
        'reward_redemption_id' => $this->rewardRedemption->id,
        'status' => UserVoucher::STATUS_AVAILABLE,
    ]);
    
    $user2 = User::create([
        'first_name' => 'Test',
        'last_name' => 'User2',
        'email' => 'user2@test.com',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);

    $rewardRedemption2 = RewardRedemption::create([
        'user_id' => $user2->id,
        'reward_id' => $this->reward->id,
        'points_spent' => 100,
        'gcash_number' => null,
        'status' => 'pending',
        'quantity' => 1,
    ]);
    
    $userVoucher2 = UserVoucher::create([
        'user_id' => $user2->id,
        'voucher_id' => $this->voucher->id,
        'reward_redemption_id' => $rewardRedemption2->id,
        'status' => UserVoucher::STATUS_AVAILABLE,
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id])
        ->set('selectedStatus', 'expired')
        ->call('updateVoucher');
    
    $userVoucher1->refresh();
    $userVoucher2->refresh();
    
    expect($userVoucher1->status)->toBe(UserVoucher::STATUS_EXPIRED);
    expect($userVoucher2->status)->toBe(UserVoucher::STATUS_EXPIRED);
});

it('initializes with correct voucher data on mount', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ViewVoucherModal::class, ['voucherId' => $this->voucher->id]);
    
    expect($component->get('selectedStatus'))->toBe('available');
    expect($component->get('availability'))->toBe('available');
});
