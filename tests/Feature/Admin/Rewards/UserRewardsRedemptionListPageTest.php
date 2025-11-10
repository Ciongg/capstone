<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\RewardRedemptions\RewardRedemptionIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create institution
    $this->institution = Institution::factory()->create([
        'name' => 'Test University'
    ]);
    
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create users
    $this->user1 = User::factory()->create([
        'email' => 'user1@institution.com',
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    $this->user2 = User::factory()->create([
        'email' => 'user2@institution.com',
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    $this->user3 = User::factory()->create([
        'email' => 'user3@institution.com',
        'type' => 'researcher',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create rewards
    $this->systemReward = Reward::create([
        'name' => 'Digital Badge',
        'type' => 'system',
        'points_cost' => 50,
        'cost' => 50,
        'quantity' => 100,
        'description' => 'System reward badge'
    ]);
    
    $this->voucherReward = Reward::create([
        'name' => 'Gift Voucher',
        'type' => 'voucher',
        'points_cost' => 100,
        'cost' => 100,
        'quantity' => 50,
        'description' => 'Gift voucher reward'
    ]);
    
    // Create redemptions with different statuses
    $this->redemption1 = RewardRedemption::create([
        'user_id' => $this->user1->id,
        'reward_id' => $this->systemReward->id,
        'points_spent' => 50,
        'quantity' => 1,
        'status' => 'completed',
        'created_at' => Carbon::now()->subDays(5)
    ]);
    
    $this->redemption2 = RewardRedemption::create([
        'user_id' => $this->user2->id,
        'reward_id' => $this->voucherReward->id,
        'points_spent' => 100,
        'quantity' => 1,
        'status' => 'pending',
        'created_at' => Carbon::now()->subDays(3)
    ]);
    
    $this->redemption3 = RewardRedemption::create([
        'user_id' => $this->user3->id,
        'reward_id' => $this->systemReward->id,
        'points_spent' => 50,
        'quantity' => 1,
        'status' => 'rejected',
        'created_at' => Carbon::now()->subDay()
    ]);
    
    $this->redemption4 = RewardRedemption::create([
        'user_id' => $this->user1->id,
        'reward_id' => $this->voucherReward->id,
        'points_spent' => 100,
        'quantity' => 2,
        'status' => 'completed',
        'created_at' => Carbon::now()
    ]);
});

// Super Admin Redemption List Tests
it('loads redemption list page for super admin', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    
    $component->assertStatus(200);
    
    $redemptions = $component->viewData('redemptions');
    expect($redemptions->total())->toBe(4);
});

it('displays all redemptions with correct information', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    // Check that all redemptions are displayed
    expect($redemptions->total())->toBe(4);
    
    // Verify redemption details are accessible
    foreach ($redemptions as $redemption) {
        expect($redemption->user)->not->toBeNull();
        expect($redemption->reward)->not->toBeNull();
        expect($redemption->points_spent)->toBeGreaterThan(0);
        expect($redemption->status)->toBeIn(['pending', 'completed', 'rejected']);
    }
});

it('filters redemptions by status', function () {
    Auth::login($this->superAdmin);
    
    // Filter by completed
    $component = Livewire::test(RewardRedemptionIndex::class)
        ->call('filterByStatus', 'completed')
        ->assertSet('statusFilter', 'completed');
    
    $redemptions = $component->viewData('redemptions');
    foreach ($redemptions as $redemption) {
        expect($redemption->status)->toBe('completed');
    }
});

it('filters redemptions by pending status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class)
        ->call('filterByStatus', 'pending')
        ->assertSet('statusFilter', 'pending');
    
    $redemptions = $component->viewData('redemptions');
    expect($redemptions->total())->toBe(1);
    expect($redemptions->first()->status)->toBe('pending');
});

it('filters redemptions by rejected status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class)
        ->call('filterByStatus', 'rejected')
        ->assertSet('statusFilter', 'rejected');
    
    $redemptions = $component->viewData('redemptions');
    expect($redemptions->total())->toBe(1);
    expect($redemptions->first()->status)->toBe('rejected');
});

it('searches redemptions by user UUID', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class)
        ->set('searchTerm', $this->user1->uuid);
    
    $redemptions = $component->viewData('redemptions');
    expect($redemptions->total())->toBe(2); // user1 has 2 redemptions
    
    foreach ($redemptions as $redemption) {
        expect($redemption->user_id)->toBe($this->user1->id);
    }
});

it('searches redemptions by reward name', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class)
        ->set('searchTerm', 'Digital Badge');
    
    $redemptions = $component->viewData('redemptions');
    expect($redemptions->total())->toBe(2); // 2 redemptions for Digital Badge
    
    foreach ($redemptions as $redemption) {
        expect($redemption->reward->name)->toContain('Digital Badge');
    }
});

it('shows correct redemption counts', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    
    expect($component->get('pendingCount'))->toBe(1);
    expect($component->get('completedCount'))->toBe(2);
    expect($component->get('rejectedCount'))->toBe(1);
});

it('displays redemption with user information', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    $redemption = $redemptions->first();
    expect($redemption->user->name)->not->toBeNull();
    expect($redemption->user->email)->not->toBeNull();
});

it('displays redemption with reward information', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    $redemption = $redemptions->first();
    expect($redemption->reward->name)->not->toBeNull();
    expect($redemption->reward->type)->toBeIn(['system', 'voucher']);
});

it('shows redemption quantity correctly', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    $multiQuantityRedemption = $redemptions->firstWhere('id', $this->redemption4->id);
    expect($multiQuantityRedemption->quantity)->toBe(2);
});

it('displays redemption creation date', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    foreach ($redemptions as $redemption) {
        expect($redemption->created_at)->toBeInstanceOf(Carbon::class);
    }
});

it('paginates redemption list correctly', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    expect($redemptions)->toHaveProperty('total');
    expect($redemptions)->toHaveProperty('perPage');
});

it('displays system reward redemptions', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    $systemRedemptions = $redemptions->filter(function($r) {
        return $r->reward->type === 'system';
    });
    
    expect($systemRedemptions->count())->toBeGreaterThan(0);
});

it('displays voucher reward redemptions', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    $voucherRedemptions = $redemptions->filter(function($r) {
        return $r->reward->type === 'voucher';
    });
    
    expect($voucherRedemptions->count())->toBeGreaterThan(0);
});

it('orders redemptions by creation date descending', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(RewardRedemptionIndex::class);
    $redemptions = $component->viewData('redemptions');
    
    $dates = $redemptions->pluck('created_at')->toArray();
    $sortedDates = collect($dates)->sortDesc()->values()->toArray();
    
    expect($dates)->toEqual($sortedDates);
});
