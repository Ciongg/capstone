<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Reward;
use App\Models\Merchant;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\Vouchers\VoucherInventoryIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create two institutions
    $this->institution1 = Institution::create([
        'name' => 'Test University 1',
        'domain' => 'test1.edu'
    ]);
    
    $this->institution2 = Institution::create([
        'name' => 'Test University 2',
        'domain' => 'test2.edu'
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
    
    // Create merchants
    $this->merchant1 = Merchant::create([
        'name' => 'Store 1',
        'merchant_code' => 'STORE001',
        'description' => 'Test Store 1',
        'contact_number' => '1234567890',
        'email' => 'store1@test.com',
    ]);
    
    $this->merchant2 = Merchant::create([
        'name' => 'Store 2',
        'merchant_code' => 'STORE002',
        'description' => 'Test Store 2',
        'contact_number' => '0987654321',
        'email' => 'store2@test.com',
    ]);
    
    // Create rewards - use lowercase 'voucher' to match database constraint
    $this->reward1 = Reward::create([
        'merchant_id' => $this->merchant1->id,
        'name' => 'Reward 1',
        'description' => 'Test Reward 1',
        'cost' => 100,
        'rank_requirement' => 'silver',
        'status' => 'available',
        'quantity' => 10,
        'type' => 'voucher', // Changed from 'Voucher' to 'voucher'
    ]);
    
    $this->reward2 = Reward::create([
        'merchant_id' => $this->merchant2->id,
        'name' => 'Reward 2',
        'description' => 'Test Reward 2',
        'cost' => 200,
        'rank_requirement' => 'gold',
        'status' => 'available',
        'quantity' => 10,
        'type' => 'voucher', // Changed from 'Voucher' to 'voucher'
    ]);
    
    // Create vouchers with different statuses
    $this->voucher1 = Voucher::create([
        'reward_id' => $this->reward1->id,
        'reference_no' => 'REF001',
        'promo' => '10% Discount',
        'cost' => 100,
        'availability' => 'available',
        'expiry_date' => Carbon::now()->addDays(30),
    ]);
    
    $this->voucher2 = Voucher::create([
        'reward_id' => $this->reward1->id,
        'reference_no' => 'REF002',
        'promo' => '20% Discount',
        'cost' => 150,
        'availability' => 'used',
        'expiry_date' => Carbon::now()->addDays(30),
    ]);
    
    $this->voucher3 = Voucher::create([
        'reward_id' => $this->reward1->id,
        'reference_no' => 'REF003',
        'promo' => 'Free Shipping',
        'cost' => 50,
        'availability' => 'expired',
        'expiry_date' => Carbon::now()->subDays(5),
    ]);
    
    $this->voucher4 = Voucher::create([
        'reward_id' => $this->reward2->id,
        'reference_no' => 'REF004',
        'promo' => 'Buy 1 Get 1',
        'cost' => 200,
        'availability' => 'available',
        'expiry_date' => Carbon::now()->addDays(60),
    ]);
    
    $this->voucher5 = Voucher::create([
        'reward_id' => $this->reward1->id,
        'reference_no' => 'REF005',
        'promo' => '50% Off',
        'cost' => 250,
        'availability' => 'unavailable',
        'expiry_date' => null,
    ]);
});

// Super Admin Voucher List Tests
it('loads super admin voucher inventory page', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class);
    
    $component->assertStatus(200);
    
    $vouchers = $component->viewData('vouchers');
    expect($vouchers->total())->toBe(5);
});

it('super admin sees vouchers from all merchants', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class);
    $vouchers = $component->viewData('vouchers');
    
    $voucherReferences = $vouchers->pluck('reference_no')->toArray();
    
    // Should see vouchers from both merchants
    expect($voucherReferences)->toContain('REF001');
    expect($voucherReferences)->toContain('REF004');
});

it('filters vouchers by availability status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class)
        ->call('filterByAvailability', 'available')
        ->assertSet('availabilityFilter', 'available');
    
    $vouchers = $component->viewData('vouchers');
    foreach ($vouchers as $voucher) {
        expect($voucher->availability)->toBe('available');
    }
});

it('filters used vouchers', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class)
        ->call('filterByAvailability', 'used')
        ->assertSet('availabilityFilter', 'used');
    
    $vouchers = $component->viewData('vouchers');
    foreach ($vouchers as $voucher) {
        expect($voucher->availability)->toBe('used');
    }
    expect($vouchers->total())->toBe(1);
});

it('filters expired vouchers', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class)
        ->call('filterByAvailability', 'expired')
        ->assertSet('availabilityFilter', 'expired');
    
    $vouchers = $component->viewData('vouchers');
    foreach ($vouchers as $voucher) {
        expect($voucher->availability)->toBe('expired');
    }
    expect($vouchers->total())->toBe(1);
});

it('searches vouchers by reference number', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class)
        ->set('searchTerm', 'REF001');
    
    $vouchers = $component->viewData('vouchers');
    expect($vouchers->total())->toBe(1);
    expect($vouchers->first()->reference_no)->toBe('REF001');
});

it('searches vouchers by store name', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class)
        ->set('searchTerm', 'Store 1');
    
    $vouchers = $component->viewData('vouchers');
    expect($vouchers->total())->toBeGreaterThanOrEqual(3);
    
    // All results should be from Store 1
    foreach ($vouchers as $voucher) {
        expect($voucher->reward->merchant->name)->toBe('Store 1');
    }
});

it('shows correct voucher counts by status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class);
    
    expect($component->viewData('availableCount'))->toBe(2);
    expect($component->viewData('usedCount'))->toBe(1);
    expect($component->viewData('expiredCount'))->toBe(1);
    expect($component->viewData('unavailableCount'))->toBe(1);
});

it('displays voucher information correctly', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherInventoryIndex::class)
        ->assertSee('REF001')
        ->assertSee('Store 1')
        ->assertSee('10% Discount')
        ->assertSee('100');
});

it('shows all vouchers when filter is set to all', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherInventoryIndex::class)
        ->call('filterByAvailability', 'all')
        ->assertSet('availabilityFilter', 'all');
    
    $vouchers = $component->viewData('vouchers');
    expect($vouchers->total())->toBe(5);
});

it('can select voucher for viewing', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherInventoryIndex::class)
        ->set('selectedVoucherId', $this->voucher1->id)
        ->assertSet('selectedVoucherId', $this->voucher1->id);
});
