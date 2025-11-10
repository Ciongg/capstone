<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create test merchants
    $this->merchant1 = Merchant::create([
        'name' => 'Merchant One',
        'merchant_code' => 'MERCHANT001',
        'partner_type' => 'Merchant',
    ]);
    
    $this->merchant2 = Merchant::create([
        'name' => 'Merchant Two',
        'merchant_code' => 'MERCHANT002',
        'partner_type' => 'Merchant',
    ]);
    
    $this->affiliate1 = Merchant::create([
        'name' => 'Affiliate One',
        'merchant_code' => 'AFFILIATE001',
        'partner_type' => 'Affiliate',
    ]);
});

it('can query all merchants', function () {
    $merchants = Merchant::all();
    
    expect($merchants->count())->toBeGreaterThanOrEqual(3);
    expect($merchants->pluck('id')->toArray())->toContain($this->merchant1->id);
    expect($merchants->pluck('id')->toArray())->toContain($this->merchant2->id);
});

it('can filter merchants by partner type', function () {
    $merchants = Merchant::where('partner_type', 'Merchant')->get();
    
    expect($merchants->count())->toBeGreaterThanOrEqual(2);
    foreach ($merchants as $merchant) {
        expect($merchant->partner_type)->toBe('Merchant');
    }
});

it('can filter affiliates by partner type', function () {
    $affiliates = Merchant::where('partner_type', 'Affiliate')->get();
    
    expect($affiliates->count())->toBeGreaterThanOrEqual(1);
    foreach ($affiliates as $affiliate) {
        expect($affiliate->partner_type)->toBe('Affiliate');
    }
});

it('can search merchants by name', function () {
    $results = Merchant::where('name', 'like', '%Merchant One%')->get();
    
    expect($results->count())->toBe(1);
    expect($results->first()->name)->toBe('Merchant One');
});

it('can search merchants by merchant code', function () {
    $results = Merchant::where('merchant_code', 'like', '%MERCHANT001%')->get();
    
    expect($results->count())->toBe(1);
    expect($results->first()->merchant_code)->toBe('MERCHANT001');
});

it('can count merchants by partner type', function () {
    $merchantCount = Merchant::where('partner_type', 'Merchant')->count();
    $affiliateCount = Merchant::where('partner_type', 'Affiliate')->count();
    
    expect($merchantCount)->toBeGreaterThanOrEqual(2);
    expect($affiliateCount)->toBeGreaterThanOrEqual(1);
});

it('can order merchants by creation date', function () {
    $merchants = Merchant::orderBy('created_at', 'desc')->get();
    
    expect($merchants->first()->created_at->gte($merchants->last()->created_at))->toBeTrue();
});

it('can query merchant with rewards relationship', function () {
    $merchant = Merchant::with('rewards')->find($this->merchant1->id);
    
    expect($merchant)->not->toBeNull();
    expect($merchant->relationLoaded('rewards'))->toBeTrue();
});
