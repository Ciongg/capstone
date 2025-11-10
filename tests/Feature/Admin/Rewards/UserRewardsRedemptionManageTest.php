<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\RewardRedemptions\Modal\RewardRedemptionModal;

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
    
    // Create user
    $this->user = User::factory()->create([
        'email' => 'user@institution.com',
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);
    
    // Mock storage for file uploads
    Storage::fake('public');
    
    // Create reward with image
    $this->rewardWithImage = Reward::create([
        'name' => 'Premium Badge',
        'type' => 'system',
        'points_cost' => 100,
        'cost' => 100,
        'quantity' => 50,
        'description' => 'Premium reward with image',
        'image_path' => 'rewards/test-image.jpg'
    ]);
    
    // Create reward without image
    $this->rewardWithoutImage = Reward::create([
        'name' => 'Basic Badge',
        'type' => 'voucher',
        'points_cost' => 50,
        'cost' => 50,
        'quantity' => 100,
        'description' => 'Basic reward without image',
        'image_path' => null
    ]);
    
    // Create redemption with completed status
    $this->completedRedemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->rewardWithImage->id,
        'points_spent' => 100,
        'quantity' => 1,
        'status' => 'completed',
        'created_at' => Carbon::now()->subDays(5)
    ]);
    
    // Create redemption with pending status
    $this->pendingRedemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->rewardWithoutImage->id,
        'points_spent' => 50,
        'quantity' => 2,
        'status' => 'pending',
        'created_at' => Carbon::now()->subDay()
    ]);
    
    // Login as super admin
    Auth::login($this->superAdmin);
});

// Redemption Modal Display Tests
it('loads redemption details in modal', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    
    expect($component->get('redemption'))->not->toBeNull();
    expect($component->get('redemption')->id)->toBe($this->completedRedemption->id);
});

it('displays correct user information in modal', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->user->id)->toBe($this->user->id);
    expect($redemption->user->name)->toBe('Test User');
    expect($redemption->user->uuid)->not->toBeNull();
});

it('displays correct reward information in modal', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->reward->name)->toBe('Premium Badge');
    expect($redemption->reward->type)->toBe('system');
    expect($redemption->reward->cost)->toBe(100); // Changed from points_cost to cost
});

it('displays redemption status correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->status)->toBe('completed');
});

it('displays points spent correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->points_spent)->toBe(100);
});

it('displays redemption quantity correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->pendingRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->quantity)->toBe(2);
});

it('displays creation date correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->created_at)->toBeInstanceOf(Carbon::class);
    // Use the actual created_at from the redemption instead of calculating from now()
    $expectedDate = $this->completedRedemption->created_at->format('Y-m-d');
    expect($redemption->created_at->format('Y-m-d'))->toBe($expectedDate);
});

it('shows reward image when available', function () {
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id])
        ->assertSee($this->rewardWithImage->image_path);
});

it('shows placeholder icon when reward has no image', function () {
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->pendingRedemption->id])
        ->assertSee('h-16 w-16'); // SVG icon classes
});

it('displays system reward type correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->reward->type)->toBe('system');
});

it('displays voucher reward type correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->pendingRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->reward->type)->toBe('voucher');
});

it('shows pending status badge correctly', function () {
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->pendingRedemption->id])
        ->assertSee('bg-yellow-200')
        ->assertSee('Pending');
});

it('shows completed status badge correctly', function () {
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id])
        ->assertSee('bg-green-200')
        ->assertSee('Completed');
});

it('shows rejected status badge correctly', function () {
    $rejectedRedemption = RewardRedemption::create([
        'user_id' => $this->user->id,
        'reward_id' => $this->rewardWithoutImage->id,
        'points_spent' => 50,
        'quantity' => 1,
        'status' => 'rejected',
    ]);
    
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $rejectedRedemption->id])
        ->assertSee('bg-red-200')
        ->assertSee('Rejected');
});

it('displays user UUID in modal', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id])
        ->assertSee($redemption->user->uuid);
});

it('displays redemption ID in modal', function () {
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id])
        ->assertSee($this->completedRedemption->id);
});

it('loads redemption with all relationships', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    // Verify relationships are loaded
    expect($redemption->user)->not->toBeNull();
    expect($redemption->reward)->not->toBeNull();
    expect($redemption->relationLoaded('user'))->toBeTrue();
    expect($redemption->relationLoaded('reward'))->toBeTrue();
});

it('displays formatted creation date', function () {
    Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id])
        ->assertSee($this->completedRedemption->created_at->format('M d, Y h:i A'));
});

it('shows reward description when available', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->completedRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->reward->description)->toBe('Premium reward with image');
});

it('handles multiple quantity redemptions correctly', function () {
    $component = Livewire::test(RewardRedemptionModal::class, ['redemptionId' => $this->pendingRedemption->id]);
    $redemption = $component->get('redemption');
    
    expect($redemption->quantity)->toBe(2);
    expect($redemption->points_spent)->toBe(50); // Total points for 2 items
});
