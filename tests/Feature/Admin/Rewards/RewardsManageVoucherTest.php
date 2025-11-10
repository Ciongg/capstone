<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Reward;
use App\Models\Voucher;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\Vouchers\Modal\ManageVoucherModal;

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
        'name' => 'Test Store',
        'merchant_code' => 'TEST001',
        'description' => 'Test Store Description',
        'contact_number' => '1234567890',
        'email' => 'store@test.com',
    ]);
    
    $this->merchant2 = Merchant::create([
        'name' => 'Another Store',
        'merchant_code' => 'ANOTHER001',
        'description' => 'Another Store Description',
        'contact_number' => '0987654321',
        'email' => 'another@test.com',
    ]);
    
    // Create voucher reward
    $this->voucherReward = Reward::create([
        'merchant_id' => $this->merchant1->id,
        'name' => 'Test Voucher Reward',
        'description' => 'Test Voucher Description',
        'cost' => 100,
        'rank_requirement' => 'silver',
        'status' => 'available',
        'quantity' => 5,
        'type' => 'voucher',
        'image_path' => 'rewards/test.jpg',
    ]);
    
    // Create vouchers for the reward
    for ($i = 1; $i <= 5; $i++) {
        Voucher::create([
            'reward_id' => $this->voucherReward->id,
            'reference_no' => "REF00{$i}",
            'promo' => $this->voucherReward->name,
            'cost' => $this->voucherReward->cost,
            'availability' => 'available',
            'expiry_date' => Carbon::now()->addDays(30),
            'image_path' => $this->voucherReward->image_path,
            'merchant_id' => $this->merchant1->id,
        ]);
    }
    
    // Create system reward
    $this->systemReward = Reward::create([
        'merchant_id' => null,
        'name' => 'System Badge',
        'description' => 'System Reward',
        'cost' => 50,
        'rank_requirement' => 'gold',
        'status' => 'available',
        'quantity' => 100,
        'type' => 'system',
    ]);
    
    // Fake storage
    Storage::fake('public');
});

// Modal Loading Tests
it('loads manage voucher modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id]);
    
    expect($component->get('name'))->toBe('Test Voucher Reward');
    expect($component->get('cost'))->toBe(100);
    expect($component->get('quantity'))->toBe(5);
    expect($component->get('type'))->toBe('voucher');
});

it('displays voucher counts for voucher rewards', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id]);
    
    expect($component->get('availableVouchers'))->toBe(5);
    expect($component->get('totalVouchers'))->toBe(5);
});

// Update Reward Tests
it('can update reward name', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('name', 'Updated Voucher Name')
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->name)->toBe('Updated Voucher Name');
});

it('can update reward description', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('description', 'Updated Description')
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->description)->toBe('Updated Description');
});

it('can update reward cost', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('cost', 150)
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->cost)->toBe(150);
    
    // Check vouchers were updated too
    $voucher = Voucher::where('reward_id', $this->voucherReward->id)->first();
    expect($voucher->cost)->toBe(150);
});

it('can update reward merchant', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('merchant_id', $this->merchant2->id)
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->merchant_id)->toBe($this->merchant2->id);
});

it('can update reward rank requirement', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('rank_requirement', 'diamond')
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->rank_requirement)->toBe('diamond');
});

it('can update reward status', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('status', 'unavailable')
        ->call('updateStatus');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->status)->toBe('unavailable');
});

// Image Management Tests
it('can upload new reward image', function () {
    Auth::login($this->superAdmin);
    
    // Use create() instead of image()
    $file = UploadedFile::fake()->create('new-voucher.jpg', 100, 'image/jpeg');
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('image', $file)
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->voucherReward->image_path);
});

it('can mark image for deletion', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->call('markImageForDeletion');
    
    expect($component->get('imageMarkedForDeletion'))->toBeTrue();
});

it('can cancel image deletion', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->call('markImageForDeletion')
        ->set('imageMarkedForDeletion', false);
    
    expect($component->get('imageMarkedForDeletion'))->toBeFalse();
});

it('deletes image when marked and updated', function () {
    Auth::login($this->superAdmin);
    
    $oldImagePath = $this->voucherReward->image_path;
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->call('markImageForDeletion')
        ->call('updateReward');
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->image_path)->toBeNull();
});

// Voucher Restock Tests
it('can restock vouchers', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('restockQuantity', 3)
        ->call('restockVouchers');
    
    expect(Voucher::where('reward_id', $this->voucherReward->id)->count())->toBe(8);
    
    $this->voucherReward->refresh();
    expect($this->voucherReward->quantity)->toBe(8);
});

it('validates restock quantity', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('restockQuantity', 0)
        ->call('restockVouchers')
        ->assertHasErrors(['restockQuantity']);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('restockQuantity', 101)
        ->call('restockVouchers')
        ->assertHasErrors(['restockQuantity']);
});

it('can restock vouchers with expiry date', function () {
    Auth::login($this->superAdmin);
    
    $expiryDate = Carbon::now()->addDays(60)->format('Y-m-d');
    
    $existingVoucherIds = Voucher::where('reward_id', $this->voucherReward->id)
        ->pluck('id')
        ->toArray();
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('restockQuantity', 2)
        ->set('voucherExpiryDate', $expiryDate)
        ->call('restockVouchers');
    
    $newVouchers = Voucher::where('reward_id', $this->voucherReward->id)
        ->whereNotIn('id', $existingVoucherIds)
        ->get();
    
    expect($newVouchers)->toHaveCount(2);
    foreach ($newVouchers as $voucher) {
        expect($voucher->expiry_date)->not->toBeNull();
        expect($voucher->expiry_date->format('Y-m-d'))->toBe($expiryDate);
    }
});

it('generates unique reference numbers when restocking', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('restockQuantity', 5)
        ->call('restockVouchers');
    
    $references = Voucher::where('reward_id', $this->voucherReward->id)
        ->pluck('reference_no');
    
    expect($references->unique()->count())->toBe($references->count());
});

// Status Update Tests
it('updates voucher availability when reward status changes to unavailable', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('status', 'unavailable')
        ->call('updateStatus');
    
    $availableVouchers = Voucher::where('reward_id', $this->voucherReward->id)
        ->where('availability', 'available')
        ->count();
    
    expect($availableVouchers)->toBe(0);
});

// Delete Reward Tests
it('can delete voucher reward', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->call('deleteReward');
    
    expect(Reward::find($this->voucherReward->id))->toBeNull();
    expect(Voucher::where('reward_id', $this->voucherReward->id)->count())->toBe(0);
});

// Validation Tests
it('validates reward update fields', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('name', '')
        ->set('cost', -10)
        ->call('updateReward')
        ->assertHasErrors(['name', 'cost']);
});

it('validates rank requirement values', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id])
        ->set('rank_requirement', 'invalid')
        ->call('updateReward')
        ->assertHasErrors(['rank_requirement']);
});

// Voucher Count Display Tests
it('shows correct available voucher count', function () {
    Auth::login($this->superAdmin);
    
    // Mark 2 vouchers as used
    Voucher::where('reward_id', $this->voucherReward->id)
        ->take(2)
        ->update(['availability' => 'used']);
    
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->voucherReward->id]);
    
    expect($component->get('availableVouchers'))->toBe(3);
    expect($component->get('totalVouchers'))->toBe(5);
});

// System Reward Tests
it('does not show voucher restock for system rewards', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->systemReward->id]);
    
    expect($component->get('type'))->toBe('system');
    expect($component->get('availableVouchers'))->toBeNull();
});

it('can update system reward quantity directly', function () {
    Auth::login($this->superAdmin);
    
    // For system rewards, the quantity field in the form should update directly
    $component = Livewire::test(ManageVoucherModal::class, ['rewardId' => $this->systemReward->id])
        ->set('quantity', 150)
        ->call('updateReward');
    
    $this->systemReward->refresh();
    // Check if the component has the updated quantity
    expect($component->get('quantity'))->toBe(150);
    expect($this->systemReward->quantity)->toBe(150);
});
