<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Institution;
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
    
    // Create test institutions
    $this->institution1 = Institution::create([
        'name' => 'University One',
        'domain' => 'university1.edu',
    ]);
    
    $this->institution2 = Institution::create([
        'name' => 'University Two',
        'domain' => 'university2.edu',
    ]);
    
    $this->institution3 = Institution::create([
        'name' => 'College Three',
        'domain' => 'college3.edu',
    ]);
});

it('can query all institutions', function () {
    $institutions = Institution::all();
    
    expect($institutions->count())->toBeGreaterThanOrEqual(3);
    expect($institutions->pluck('id')->toArray())->toContain($this->institution1->id);
    expect($institutions->pluck('id')->toArray())->toContain($this->institution2->id);
});

it('can search institutions by name', function () {
    $results = Institution::where('name', 'like', '%University One%')->get();
    
    expect($results->count())->toBe(1);
    expect($results->first()->name)->toBe('University One');
});

it('can search institutions by domain', function () {
    $results = Institution::where('domain', 'like', '%university1.edu%')->get();
    
    expect($results->count())->toBe(1);
    expect($results->first()->domain)->toBe('university1.edu');
});

it('can search institutions by partial name', function () {
    $results = Institution::where('name', 'like', '%University%')->get();
    
    expect($results->count())->toBeGreaterThanOrEqual(2);
});

it('can order institutions by creation date', function () {
    $institutions = Institution::orderBy('created_at', 'desc')->get();
    
    expect($institutions->first()->created_at->gte($institutions->last()->created_at))->toBeTrue();
});

it('can query institution with users relationship', function () {
    $user = User::factory()->create([
        'institution_id' => $this->institution1->id,
    ]);
    
    $institution = Institution::with('users')->find($this->institution1->id);
    
    expect($institution)->not->toBeNull();
    expect($institution->relationLoaded('users'))->toBeTrue();
    expect($institution->users->count())->toBeGreaterThanOrEqual(1);
});

it('can count users per institution', function () {
    User::factory()->count(3)->create([
        'institution_id' => $this->institution1->id,
    ]);
    
    User::factory()->count(2)->create([
        'institution_id' => $this->institution2->id,
    ]);
    
    $institution1 = Institution::withCount('users')->find($this->institution1->id);
    $institution2 = Institution::withCount('users')->find($this->institution2->id);
    
    expect($institution1->users_count)->toBeGreaterThanOrEqual(3);
    expect($institution2->users_count)->toBeGreaterThanOrEqual(2);
});

it('can filter institutions with users', function () {
    User::factory()->create(['institution_id' => $this->institution1->id]);
    
    $institutionsWithUsers = Institution::has('users')->get();
    
    expect($institutionsWithUsers->pluck('id')->toArray())->toContain($this->institution1->id);
});
