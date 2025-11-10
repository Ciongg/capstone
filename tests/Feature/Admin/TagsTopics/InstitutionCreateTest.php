<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Institutions\Modal\CreateInstitutionModal;

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

it('super admin can create institution', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution')
        ->assertDispatched('institution-created');
    
    $this->assertDatabaseHas('institutions', [
        'name' => 'Test University',
        'domain' => 'testuniversity.edu',
    ]);
});

it('validates institution name is required', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution')
        ->assertHasErrors(['name' => 'required']);
});

it('validates institution domain is required', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->call('createInstitution')
        ->assertHasErrors(['domain' => 'required']);
});

it('validates institution name uniqueness', function () {
    Institution::create([
        'name' => 'Existing University',
        'domain' => 'existing.edu',
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Existing University')
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution')
        ->assertHasErrors(['name' => 'unique']);
});

it('validates institution domain uniqueness', function () {
    Institution::create([
        'name' => 'Existing University',
        'domain' => 'existing.edu',
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->set('domain', 'existing.edu')
        ->call('createInstitution')
        ->assertHasErrors(['domain' => 'unique']);
});

it('assigns users with matching domain to new institution', function () {
    $user = User::factory()->create([
        'email' => 'user@testuniversity.edu',
        'institution_id' => null,
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution');
    
    $user->refresh();
    $institution = Institution::where('domain', 'testuniversity.edu')->first();
    
    expect($user->institution_id)->toBe($institution->id);
});

it('logs out users when assigned to new institution', function () {
    $user = User::factory()->create([
        'email' => 'user@testuniversity.edu',
        'institution_id' => null,
    ]);
    
    // Create a session for the user
    \DB::table('sessions')->insert([
        'id' => 'test-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => 'test',
        'last_activity' => time(),
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution');
    
    $this->assertDatabaseMissing('sessions', [
        'user_id' => $user->id,
    ]);
});

it('closes modal after successful creation', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution')
        ->assertDispatched('close-modal');
});

it('dispatches refresh event after creation', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(CreateInstitutionModal::class)
        ->set('name', 'Test University')
        ->set('domain', 'testuniversity.edu')
        ->call('createInstitution')
        ->assertDispatched('refresh-institution-index');
});
