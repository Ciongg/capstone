<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Institutions\Modal\ManageInstitutionModal;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    // Create test institution
    $this->institution = Institution::create([
        'name' => 'Original University',
        'domain' => 'original.edu',
    ]);
});

it('loads institution data in modal', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id]);
    
    expect($component->get('name'))->toBe('Original University');
    expect($component->get('domain'))->toBe('original.edu');
});

it('super admin can update institution details', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->set('name', 'Updated University')
        ->set('domain', 'updated.edu')
        ->call('updateInstitution')
        ->assertDispatched('institution-updated');
    
    $this->institution->refresh();
    expect($this->institution->name)->toBe('Updated University');
    expect($this->institution->domain)->toBe('updated.edu');
});

it('validates institution name uniqueness on update', function () {
    Institution::create([
        'name' => 'Another University',
        'domain' => 'another.edu',
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->set('name', 'Another University')
        ->call('updateInstitution')
        ->assertHasErrors(['name' => 'unique']);
});

it('allows same institution name on update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->set('name', 'Original University')
        ->call('updateInstitution')
        ->assertHasNoErrors(['name']);
});

it('logs out users when institution domain changes', function () {
    $user = User::factory()->create([
        'email' => 'user@original.edu',
        'institution_id' => $this->institution->id,
    ]);
    
    \DB::table('sessions')->insert([
        'id' => 'test-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => 'test',
        'last_activity' => time(),
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->set('domain', 'updated.edu')
        ->call('updateInstitution');
    
    $this->assertDatabaseMissing('sessions', [
        'user_id' => $user->id,
    ]);
});

it('super admin can delete institution', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->call('deleteInstitution')
        ->assertDispatched('institution-deleted');
    
    $this->assertDatabaseMissing('institutions', [
        'id' => $this->institution->id,
    ]);
});

it('logs out all users when deleting institution', function () {
    $user1 = User::factory()->create(['institution_id' => $this->institution->id]);
    $user2 = User::factory()->create(['institution_id' => $this->institution->id]);
    
    \DB::table('sessions')->insert([
        ['id' => 'session1', 'user_id' => $user1->id, 'ip_address' => '127.0.0.1', 
         'user_agent' => 'test', 'payload' => 'test', 'last_activity' => time()],
        ['id' => 'session2', 'user_id' => $user2->id, 'ip_address' => '127.0.0.1', 
         'user_agent' => 'test', 'payload' => 'test', 'last_activity' => time()],
    ]);
    
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->call('deleteInstitution');
    
    $this->assertDatabaseMissing('sessions', ['user_id' => $user1->id]);
    $this->assertDatabaseMissing('sessions', ['user_id' => $user2->id]);
});

it('validates required fields on update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->set('name', '')
        ->call('updateInstitution')
        ->assertHasErrors(['name' => 'required']);
});

it('closes modal after update', function () {
    Auth::login($this->superAdmin);
    
    Livewire::test(ManageInstitutionModal::class, ['institutionId' => $this->institution->id])
        ->set('name', 'Updated Name')
        ->call('updateInstitution')
        ->assertDispatched('close-modal');
});
