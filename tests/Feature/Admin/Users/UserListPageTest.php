<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Institution;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create two institutions
    $this->institution1 = Institution::factory()->create([
        'name' => 'Test University 1'
    ]);
    
    $this->institution2 = Institution::factory()->create([
        'name' => 'Test University 2'
    ]);
    
    // Create institution admin for institution 1
    $this->institutionAdmin = User::factory()->create([
        'email' => 'admin@institution1.com',
        'type' => 'institution_admin',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
        'first_name' => 'Admin',
        'last_name' => 'One',
    ]);
    
    // Create super admin
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@system.com',
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
        'first_name' => 'Super',
        'last_name' => 'Admin',
    ]);
    
    // Create researchers for institution 1
    $this->researcher1 = User::factory()->create([
        'email' => 'researcher1@institution1.com',
        'type' => 'researcher',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
        'first_name' => 'Researcher',
        'last_name' => 'One',
    ]);
    
    $this->researcher2 = User::factory()->create([
        'email' => 'researcher2@institution1.com',
        'type' => 'researcher',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
        'first_name' => 'Researcher',
        'last_name' => 'Two',
    ]);
    
    // Create deactivated researcher for institution 1
    $this->deactivatedResearcher = User::factory()->create([
        'email' => 'deactivated@institution1.com',
        'type' => 'researcher',
        'institution_id' => $this->institution1->id,
        'is_active' => false,
        'first_name' => 'Deactivated',
        'last_name' => 'User',
    ]);
    
    // Create users for institution 2
    $this->researcher3 = User::factory()->create([
        'email' => 'researcher3@institution2.com',
        'type' => 'researcher',
        'institution_id' => $this->institution2->id,
        'is_active' => true,
        'first_name' => 'Researcher',
        'last_name' => 'Three',
    ]);
    
    $this->respondent1 = User::factory()->create([
        'email' => 'respondent1@institution1.com',
        'type' => 'respondent',
        'institution_id' => $this->institution1->id,
        'is_active' => true,
        'first_name' => 'Respondent',
        'last_name' => 'One',
    ]);
    
    $this->respondent2 = User::factory()->create([
        'email' => 'respondent2@institution2.com',
        'type' => 'respondent',
        'institution_id' => $this->institution2->id,
        'is_active' => true,
        'first_name' => 'Respondent',
        'last_name' => 'Two',
    ]);
});

// Institution-based User Query Tests
it('can query users by institution', function () {
    $institution1Users = User::where('institution_id', $this->institution1->id)->get();
    
    expect($institution1Users->count())->toBeGreaterThanOrEqual(5); // admin + 2 researchers + 1 respondent + 1 deactivated
    
    foreach ($institution1Users as $user) {
        expect($user->institution_id)->toBe($this->institution1->id);
    }
});

it('institution users do not include users from other institutions', function () {
    $institution1Users = User::where('institution_id', $this->institution1->id)->get();
    $institution1UserEmails = $institution1Users->pluck('email')->toArray();
    
    expect($institution1UserEmails)->not->toContain('researcher3@institution2.com');
    expect($institution1UserEmails)->not->toContain('respondent2@institution2.com');
});

it('can filter users by type', function () {
    $researchers = User::where('type', 'researcher')->get();
    
    expect($researchers->count())->toBeGreaterThanOrEqual(4); // 3 active + 1 inactive
    
    foreach ($researchers as $researcher) {
        expect($researcher->type)->toBe('researcher');
    }
});

it('can filter users by active status', function () {
    $inactiveUsers = User::where('is_active', false)->get();
    
    expect($inactiveUsers->count())->toBeGreaterThanOrEqual(1);
    
    foreach ($inactiveUsers as $user) {
        expect($user->is_active)->toBeFalse();
    }
});

it('can search users by email', function () {
    $users = User::where('email', 'like', '%researcher1@institution1.com%')->get();
    
    expect($users->count())->toBe(1);
    expect($users->first()->email)->toBe('researcher1@institution1.com');
});

it('can count users by type for institution', function () {
    $researcherCount = User::where('institution_id', $this->institution1->id)
        ->where('type', 'researcher')
        ->count();
    
    $respondentCount = User::where('institution_id', $this->institution1->id)
        ->where('type', 'respondent')
        ->count();
    
    expect($researcherCount)->toBeGreaterThanOrEqual(3); // 2 active + 1 inactive
    expect($respondentCount)->toBeGreaterThanOrEqual(1);
});

// Super Admin User Query Tests
it('super admin can query all users regardless of institution', function () {
    $allUsers = User::all();
    
    expect($allUsers->count())->toBeGreaterThanOrEqual(8); // All created users
    
    $userEmails = $allUsers->pluck('email')->toArray();
    expect($userEmails)->toContain('researcher1@institution1.com');
    expect($userEmails)->toContain('researcher3@institution2.com');
    expect($userEmails)->toContain('respondent1@institution1.com');
    expect($userEmails)->toContain('respondent2@institution2.com');
});

it('can filter all users by type', function () {
    $researchers = User::where('type', 'researcher')->get();
    
    foreach ($researchers as $researcher) {
        expect($researcher->type)->toBe('researcher');
    }
    
    expect($researchers->count())->toBeGreaterThanOrEqual(4);
});

it('can filter all users by institution', function () {
    $institution1Users = User::where('institution_id', $this->institution1->id)->get();
    
    foreach ($institution1Users as $user) {
        expect($user->institution_id)->toBe($this->institution1->id);
    }
});

it('can count all users by type', function () {
    $researcherCount = User::where('type', 'researcher')->count();
    $respondentCount = User::where('type', 'respondent')->count();
    $adminCount = User::where('type', 'institution_admin')->count();
    
    expect($researcherCount)->toBeGreaterThanOrEqual(4);
    expect($respondentCount)->toBeGreaterThanOrEqual(2);
    expect($adminCount)->toBeGreaterThanOrEqual(1);
});

// Data Isolation Tests
it('institution query does not return users from other institutions', function () {
    $institution1Users = User::where('institution_id', $this->institution1->id)->get();
    $institution2UserIds = [$this->researcher3->id, $this->respondent2->id];
    $institution1UserIds = $institution1Users->pluck('id')->toArray();
    
    foreach ($institution2UserIds as $userId) {
        expect($institution1UserIds)->not->toContain($userId);
    }
});

it('can query users from multiple institutions', function () {
    $allInstitutionUsers = User::whereIn('institution_id', [
        $this->institution1->id,
        $this->institution2->id
    ])->get();
    
    $userIds = $allInstitutionUsers->pluck('id')->toArray();
    
    expect($userIds)->toContain($this->researcher1->id);
    expect($userIds)->toContain($this->researcher3->id);
});

// User Relationship Tests
it('user belongs to institution', function () {
    expect($this->researcher1->institution)->not->toBeNull();
    expect($this->researcher1->institution->id)->toBe($this->institution1->id);
    expect($this->researcher1->institution->name)->toBe('Test University 1');
});

it('institution has many users', function () {
    $users = $this->institution1->users;
    
    expect($users->count())->toBeGreaterThanOrEqual(5);
    expect($users->pluck('id')->toArray())->toContain($this->researcher1->id);
    expect($users->pluck('id')->toArray())->toContain($this->institutionAdmin->id);
});

it('super admin has no institution', function () {
    expect($this->superAdmin->institution_id)->toBeNull();
    expect($this->superAdmin->institution)->toBeNull();
});
