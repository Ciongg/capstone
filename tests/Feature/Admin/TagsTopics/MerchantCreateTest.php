<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Merchants\Modal\CreateMerchantModal;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
});

it('super admin can create merchant with required fields', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('merchant_code', 'TESTMERCH123')
        ->set('partner_type', 'Merchant')
        ->call('createMerchant')
        ->assertDispatched('merchantCreated');
    
    $this->assertDatabaseHas('merchants', [
        'name' => 'Test Merchant',
        'merchant_code' => 'TESTMERCH123',
        'partner_type' => 'Merchant',
    ]);
});

it('super admin can create merchant with all fields', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Complete Merchant')
        ->set('merchant_code', 'COMPLETE123')
        ->set('partner_type', 'Merchant')
        ->set('description', 'A complete merchant description')
        ->set('email', 'merchant@example.com')
        ->set('contact_number', '+1234567890')
        ->call('createMerchant');
    
    $this->assertDatabaseHas('merchants', [
        'name' => 'Complete Merchant',
        'merchant_code' => 'COMPLETE123',
        'partner_type' => 'Merchant',
        'description' => 'A complete merchant description',
        'email' => 'merchant@example.com',
        'contact_number' => '+1234567890',
    ]);
});

it('super admin can create affiliate partner', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Affiliate')
        ->set('merchant_code', 'AFFILIATE123')
        ->set('partner_type', 'Affiliate')
        ->call('createMerchant');
    
    $this->assertDatabaseHas('merchants', [
        'name' => 'Test Affiliate',
        'partner_type' => 'Affiliate',
    ]);
});

it('validates merchant name is required', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('merchant_code', 'TESTMERCH123')
        ->set('partner_type', 'Merchant')
        ->call('createMerchant')
        ->assertHasErrors(['name' => 'required']);
});

it('validates merchant code is required', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('partner_type', 'Merchant')
        ->call('createMerchant')
        ->assertHasErrors(['merchant_code' => 'required']);
});

it('validates merchant code minimum length', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('merchant_code', 'SHORT')
        ->set('partner_type', 'Merchant')
        ->call('createMerchant')
        ->assertHasErrors(['merchant_code' => 'min']);
});

it('validates merchant code uniqueness', function () {
    Merchant::create([
        'name' => 'Existing Merchant',
        'merchant_code' => 'EXISTING123',
        'partner_type' => 'Merchant',
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('merchant_code', 'EXISTING123')
        ->set('partner_type', 'Merchant')
        ->call('createMerchant')
        ->assertHasErrors(['merchant_code' => 'unique']);
});

it('validates partner type is required', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('merchant_code', 'TESTMERCH123')
        ->set('partner_type', '')
        ->call('createMerchant')
        ->assertHasErrors(['partner_type' => 'required']);
});

it('validates email format', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('merchant_code', 'TESTMERCH123')
        ->set('partner_type', 'Merchant')
        ->set('email', 'invalid-email')
        ->call('createMerchant')
        ->assertHasErrors(['email' => 'email']);
});

it('closes modal after successful creation', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateMerchantModal::class)
        ->set('name', 'Test Merchant')
        ->set('merchant_code', 'TESTMERCH123')
        ->set('partner_type', 'Merchant')
        ->call('createMerchant')
        ->assertDispatched('close-modal');
});
