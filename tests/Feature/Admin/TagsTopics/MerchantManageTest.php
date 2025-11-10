<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Voucher;
use App\Models\Reward;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Merchants\Modal\ManageMerchantModal;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create test merchant
    $this->merchant = Merchant::create([
        'name' => 'Original Merchant',
        'merchant_code' => 'ORIGINAL123',
        'partner_type' => 'Merchant',
        'description' => 'Original description',
        'email' => 'original@merchant.com',
        'contact_number' => '+1234567890',
    ]);
});

it('loads merchant data in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id]);
    
    expect($component->get('name'))->toBe('Original Merchant');
    expect($component->get('merchant_code'))->toBe('ORIGINAL123');
    expect($component->get('partner_type'))->toBe('Merchant');
    expect($component->get('email'))->toBe('original@merchant.com');
});

it('super admin can update merchant details', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->set('name', 'Updated Merchant')
        ->set('merchant_code', 'UPDATED123')
        ->set('description', 'Updated description')
        ->set('email', 'updated@merchant.com')
        ->call('updateMerchant')
        ->assertDispatched('merchantUpdated');
    
    $this->merchant->refresh();
    expect($this->merchant->name)->toBe('Updated Merchant');
    expect($this->merchant->merchant_code)->toBe('UPDATED123');
    expect($this->merchant->email)->toBe('updated@merchant.com');
});

it('super admin can change partner type', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->set('partner_type', 'Affiliate')
        ->call('updateMerchant');
    
    $this->merchant->refresh();
    expect($this->merchant->partner_type)->toBe('Affiliate');
});

it('validates merchant code uniqueness on update', function () {
    $anotherMerchant = Merchant::create([
        'name' => 'Another Merchant',
        'merchant_code' => 'ANOTHER123',
        'partner_type' => 'Merchant',
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->set('merchant_code', 'ANOTHER123')
        ->call('updateMerchant')
        ->assertHasErrors(['merchant_code' => 'unique']);
});

it('allows same merchant code on update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->set('merchant_code', 'ORIGINAL123')
        ->call('updateMerchant')
        ->assertHasNoErrors(['merchant_code']);
});

it('super admin can delete merchant', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->call('deleteMerchant')
        ->assertDispatched('merchantDeleted');
    
    $this->assertDatabaseMissing('merchants', [
        'id' => $this->merchant->id,
    ]);
});

it('deletes associated vouchers when deleting merchant', function () {
    // Create a reward first since vouchers need a reward_id
    $reward = Reward::create([
        'merchant_id' => $this->merchant->id,
        'name' => 'Test Reward for Voucher',
        'description' => 'Test Description',
        'cost' => 100,
        'rank_requirement' => 'bronze',
        'status' => 'available',
        'quantity' => 10,
        'type' => 'voucher',
    ]);
    
    $voucher = Voucher::create([
        'merchant_id' => $this->merchant->id,
        'reward_id' => $reward->id,
        'reference_no' => 'VOUCHER001',
        'promo' => '10% Discount',
        'cost' => 100,
        'availability' => 'available',
        'expiry_date' => now()->addDays(30),
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->call('deleteMerchant');
    
    $this->assertDatabaseMissing('vouchers', [
        'id' => $voucher->id,
    ]);
    
    $this->assertDatabaseMissing('rewards', [
        'id' => $reward->id,
    ]);
});

it('deletes associated rewards when deleting merchant', function () {
    $reward = Reward::create([
        'merchant_id' => $this->merchant->id,
        'name' => 'Test Reward',
        'description' => 'Test Description',
        'cost' => 100,
        'rank_requirement' => 'bronze',
        'status' => 'available',
        'quantity' => 10,
        'type' => 'voucher', // Changed from 'physical' to 'voucher'
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->call('deleteMerchant');
    
    $this->assertDatabaseMissing('rewards', [
        'id' => $reward->id,
    ]);
});

it('validates required fields on update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->set('name', '')
        ->call('updateMerchant')
        ->assertHasErrors(['name' => 'required']);
});

it('closes modal after update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageMerchantModal::class, ['merchantId' => $this->merchant->id])
        ->set('name', 'Updated Name')
        ->call('updateMerchant')
        ->assertDispatched('close-modal');
});
