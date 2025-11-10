<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\SupportRequest;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\SupportRequests\SupportRequestsIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test.edu'
    ]);
    
    $this->superAdmin = User::create([
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'superadmin@system.com',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    $this->user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'user@test.edu',
        'password' => bcrypt('password'),
        'type' => 'respondent',
        'institution_id' => $this->institution->id,
        'is_active' => true,
    ]);
    
    // Create support requests with different statuses and types
    $this->pendingRequest = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Account Issue',
        'description' => 'Cannot login',
        'request_type' => 'account_issue',
        'status' => 'pending',
    ]);
    
    $this->inProgressRequest = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Survey Question',
        'description' => 'How to create survey',
        'request_type' => 'survey_question',
        'status' => 'in_progress',
    ]);
    
    $this->resolvedRequest = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Report Appeal',
        'description' => 'Appeal my report',
        'request_type' => 'report_appeal',
        'status' => 'resolved',
        'resolved_at' => now(),
    ]);
    
    $this->lockAppealRequest = SupportRequest::create([
        'user_id' => $this->user->id,
        'subject' => 'Survey Lock Appeal',
        'description' => 'Unlock my survey',
        'request_type' => 'survey_lock_appeal',
        'status' => 'pending',
    ]);
});

// Page Loading Tests
it('loads support requests list page', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class);
    
    $component->assertStatus(200);
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(4);
});

// Status Filter Tests
it('filters support requests by pending status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->call('filterByStatus', 'pending')
        ->assertSet('statusFilter', 'pending');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(2);
    foreach ($requests as $request) {
        expect($request->status)->toBe('pending');
    }
});

it('filters support requests by in_progress status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->call('filterByStatus', 'in_progress')
        ->assertSet('statusFilter', 'in_progress');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
    expect($requests->first()->status)->toBe('in_progress');
});

it('filters support requests by resolved status', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->call('filterByStatus', 'resolved')
        ->assertSet('statusFilter', 'resolved');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
    expect($requests->first()->status)->toBe('resolved');
});

// Request Type Filter Tests
it('filters support requests by account issue type', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->call('filterByType', 'account_issue')
        ->assertSet('requestTypeFilter', 'account_issue');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
    expect($requests->first()->request_type)->toBe('account_issue');
});

it('filters support requests by survey lock appeal type', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->call('filterByType', 'survey_lock_appeal')
        ->assertSet('requestTypeFilter', 'survey_lock_appeal');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
    expect($requests->first()->request_type)->toBe('survey_lock_appeal');
});

// Search Tests
it('searches support requests by subject', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->set('searchTerm', 'Account Issue');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
    expect($requests->first()->subject)->toBe('Account Issue');
});

it('searches support requests by user uuid', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->set('searchTerm', $this->user->uuid);
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(4);
});

it('handles empty search results', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->set('searchTerm', 'NONEXISTENT');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(0);
});

// Display Tests
it('displays status counts', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class);
    
    expect($component->viewData('pendingCount'))->toBe(2);
    expect($component->viewData('inProgressCount'))->toBe(1);
    expect($component->viewData('resolvedCount'))->toBe(1);
});

it('displays request type counts', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class);
    
    expect($component->viewData('accountIssueCount'))->toBe(1);
    expect($component->viewData('lockAppealCount'))->toBe(1);
    expect($component->viewData('reportAppealCount'))->toBe(1);
    expect($component->viewData('surveyQuestionCount'))->toBe(1);
});

// Pagination Tests
it('paginates support requests correctly', function () {
    Auth::login($this->superAdmin);
    
    // Create more requests
    for ($i = 1; $i <= 15; $i++) {
        SupportRequest::create([
            'user_id' => $this->user->id,
            'subject' => "Request {$i}",
            'description' => "Description {$i}",
            'request_type' => 'other',
            'status' => 'pending',
        ]);
    }
    
    $component = Livewire::test(SupportRequestsIndex::class);
    $requests = $component->viewData('supportRequests');
    
    expect($requests->perPage())->toBe(10);
    expect($requests->total())->toBe(19);
});

// Combined Filter Tests
it('combines status and type filters', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->call('filterByStatus', 'pending')
        ->call('filterByType', 'survey_lock_appeal');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
    expect($requests->first()->status)->toBe('pending');
    expect($requests->first()->request_type)->toBe('survey_lock_appeal');
});

it('combines filters with search', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class)
        ->set('searchTerm', 'Account')
        ->call('filterByStatus', 'pending');
    
    $requests = $component->viewData('supportRequests');
    expect($requests->total())->toBe(1);
});

// Sorting Tests
it('orders support requests by creation date descending', function () {
    Auth::login($this->superAdmin);
    
    $component = Livewire::test(SupportRequestsIndex::class);
    $requests = $component->viewData('supportRequests');
    
    $dates = $requests->pluck('created_at')->toArray();
    $sortedDates = collect($dates)->sortDesc()->values()->toArray();
    
    expect($dates)->toEqual($sortedDates);
});
