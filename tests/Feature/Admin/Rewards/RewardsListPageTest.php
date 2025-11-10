<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Reward;
use App\Models\Voucher;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\Vouchers\VoucherManager;

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
    
    // Create merchants
    $this->merchant1 = Merchant::create([
        'name' => 'Coffee Shop',
        'merchant_code' => 'COFFEE001',
        'description' => 'Best Coffee',
        'contact_number' => '1234567890',
        'email' => 'coffee@test.com',
    ]);
    
    $this->merchant2 = Merchant::create([
        'name' => 'Book Store',
        'merchant_code' => 'BOOK001',
        'description' => 'Best Books',
        'contact_number' => '0987654321',
        'email' => 'books@test.com',
    ]);
    
    // Create voucher rewards
    $this->voucherReward1 = Reward::create([
        'merchant_id' => $this->merchant1->id,
        'name' => 'Coffee Discount',
        'description' => '20% off all drinks',
        'cost' => 50,
        'rank_requirement' => 'silver',
        'status' => 'available',
        'quantity' => 10,
        'type' => 'voucher',
        'image_path' => 'rewards/coffee.jpg',
    ]);
    
    $this->voucherReward2 = Reward::create([
        'merchant_id' => $this->merchant2->id,
        'name' => 'Book Voucher',
        'description' => 'Free book',
        'cost' => 100,
        'rank_requirement' => 'gold',
        'status' => 'available',
        'quantity' => 5,
        'type' => 'voucher',
    ]);
    
    $this->voucherReward3 = Reward::create([
        'merchant_id' => $this->merchant1->id,
        'name' => 'Coffee Bundle',
        'description' => 'Buy 1 Get 1',
        'cost' => 75,
        'rank_requirement' => 'silver',
        'status' => 'sold_out',
        'quantity' => 0,
        'type' => 'voucher',
    ]);
    
    // Create system rewards
    $this->systemReward1 = Reward::create([
        'name' => 'Bronze Badge',
        'description' => 'Achievement badge',
        'cost' => 25,
        'rank_requirement' => 'silver',
        'status' => 'available',
        'quantity' => 100,
        'type' => 'system',
    ]);
    
    $this->systemReward2 = Reward::create([
        'name' => 'Gold Badge',
        'description' => 'Premium badge',
        'cost' => 150,
        'rank_requirement' => 'diamond',
        'status' => 'unavailable',
        'quantity' => 50,
        'type' => 'system',
    ]);
    
    // Create vouchers for voucher rewards
    foreach ([$this->voucherReward1, $this->voucherReward2] as $reward) {
        for ($i = 0; $i < $reward->quantity; $i++) {
            Voucher::create([
                'reward_id' => $reward->id,
                'reference_no' => "REF-{$reward->id}-{$i}",
                'promo' => $reward->name,
                'cost' => $reward->cost,
                'availability' => 'available',
                'expiry_date' => Carbon::now()->addDays(30),
                'merchant_id' => $reward->merchant_id,
            ]);
        }
    }
});

// Page Loading Tests
it('loads rewards list page', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    
    $component->assertStatus(200);
    
    $rewards = $component->viewData('rewards');
    expect($rewards->total())->toBe(5); // 3 voucher + 2 system rewards
});

it('displays all rewards by default', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    $rewardNames = $rewards->pluck('name')->toArray();
    expect($rewardNames)->toContain('Coffee Discount');
    expect($rewardNames)->toContain('Bronze Badge');
});

// Filter Tests
it('filters rewards by voucher type', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->call('filterByType', 'voucher')
        ->assertSet('typeFilter', 'voucher');
    
    $rewards = $component->viewData('rewards');
    foreach ($rewards as $reward) {
        expect($reward->type)->toBe('voucher');
    }
    expect($rewards->total())->toBe(3);
});

it('filters rewards by system type', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->call('filterByType', 'system')
        ->assertSet('typeFilter', 'system');
    
    $rewards = $component->viewData('rewards');
    foreach ($rewards as $reward) {
        expect($reward->type)->toBe('system');
    }
    expect($rewards->total())->toBe(2);
});

it('shows all rewards when filter is set to all', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->call('filterByType', 'voucher')
        ->call('filterByType', 'all')
        ->assertSet('typeFilter', 'all');
    
    $rewards = $component->viewData('rewards');
    expect($rewards->total())->toBe(5);
});

// Search Tests
it('searches rewards by name', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->set('searchTerm', 'Coffee');
    
    $rewards = $component->viewData('rewards');
    expect($rewards->total())->toBe(2); // Coffee Discount and Coffee Bundle
    
    foreach ($rewards as $reward) {
        expect($reward->name)->toContain('Coffee');
    }
});

it('searches rewards by merchant name', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->set('searchTerm', 'Book Store');
    
    $rewards = $component->viewData('rewards');
    expect($rewards->total())->toBe(1);
    expect($rewards->first()->name)->toBe('Book Voucher');
});

it('handles empty search results', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->set('searchTerm', 'NONEXISTENT');
    
    $rewards = $component->viewData('rewards');
    expect($rewards->total())->toBe(0);
});

// Display Tests
it('displays reward information correctly', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherManager::class)
        ->assertSee('Coffee Discount')
        ->assertSee('20% off all drinks')
        ->assertSee('50 points')
        ->assertSee('Coffee Shop');
});

it('displays reward status badges', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherManager::class)
        ->assertSee('Available')
        ->assertSee('Sold Out') // Now it will display with proper formatting
        ->assertSee('Unavailable');
});

it('displays reward type badges', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherManager::class)
        ->assertSee('voucher')
        ->assertSee('system');
});

// Pagination Tests
it('paginates rewards correctly', function () {
    Auth::login($this->superAdmin);
    
    // Create more rewards to test pagination
    for ($i = 1; $i <= 10; $i++) {
        Reward::create([
            'merchant_id' => $this->merchant1->id,
            'name' => "Extra Reward {$i}",
            'description' => "Extra reward {$i}",
            'cost' => 50,
            'rank_requirement' => 'silver',
            'status' => 'available',
            'quantity' => 5,
            'type' => 'voucher',
        ]);
    }
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    expect($rewards)->toHaveProperty('total');
    expect($rewards)->toHaveProperty('perPage');
    expect($rewards->perPage())->toBe(9);
    expect($rewards->total())->toBe(15);
});

// Selection Tests
it('can select reward for viewing', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherManager::class)
        ->set('selectedRewardId', $this->voucherReward1->id)
        ->assertSet('selectedRewardId', $this->voucherReward1->id);
});

// Merchant Display Tests
it('displays merchant name for voucher rewards', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    $voucherReward = $rewards->firstWhere('type', 'voucher');
    expect($voucherReward->merchant)->not->toBeNull();
});

it('does not display merchant for system rewards', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    $systemReward = $rewards->firstWhere('type', 'system');
    expect($systemReward->merchant_id)->toBeNull();
});

// Image Display Tests
it('displays reward image when available', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherManager::class)
        ->assertSee('coffee.jpg');
});

// Combined Filter and Search Tests
it('combines type filter and search', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class)
        ->set('searchTerm', 'Coffee')
        ->call('filterByType', 'voucher');
    
    $rewards = $component->viewData('rewards');
    expect($rewards->total())->toBe(2);
    
    foreach ($rewards as $reward) {
        expect($reward->type)->toBe('voucher');
        expect($reward->name)->toContain('Coffee');
    }
});

// Status Display Tests
it('displays available rewards', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    $availableRewards = $rewards->filter(fn($r) => $r->status === 'available');
    expect($availableRewards->count())->toBeGreaterThan(0);
});

it('displays sold out rewards', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    $soldOutRewards = $rewards->filter(fn($r) => $r->status === 'sold_out');
    expect($soldOutRewards->count())->toBeGreaterThan(0);
});

// Quantity Display Tests
it('displays reward quantities correctly', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(VoucherManager::class)
        ->assertSee('10') // Coffee Discount quantity
        ->assertSee('5') // Book Voucher quantity
        ->assertSee('0'); // Coffee Bundle sold out
});

// Sorting Tests
it('orders rewards by creation date descending', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(VoucherManager::class);
    $rewards = $component->viewData('rewards');
    
    $dates = $rewards->pluck('created_at')->toArray();
    $sortedDates = collect($dates)->sortDesc()->values()->toArray();
    
    expect($dates)->toEqual($sortedDates);
});
