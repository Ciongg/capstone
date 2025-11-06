<?php

namespace App\Livewire\SuperAdmin\UserList\Modal;

use App\Models\User;
use App\Models\Response;
use App\Models\Survey;
use App\Models\RewardRedemption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\AuditLogService;
use Illuminate\Validation\Rule;

class UserViewModal extends Component
{
    public $user = null;
    public $userId;
    public $activities = [];
    public $activitiesLoaded = false;
    public $isInstitutionAdmin = false;
    public $institutionId = null;
    public $trustScore = 100;
    public $userType = 'respondent'; // New property for user type
    
    protected $rules = [
        'trustScore' => ['required', 'numeric', 'min:0', 'max:100'],
    ];
    
    public function mount($userId)
    {
        $this->userId = $userId;
        
        // Determine if the current user is an institution admin
        $currentUser = Auth::user();
        $this->isInstitutionAdmin = $currentUser->type === 'institution_admin';
        
        if ($this->isInstitutionAdmin) {
            $this->institutionId = $currentUser->institution_id;
        }
        
        $this->loadUser();
    }
    
    public function loadUser()
    {
        if ($this->userId) {
            // Include trashed users so we can view archived users
            $user = User::withTrashed()->find($this->userId);
            
            // If institution admin, ensure they can only view users from their institution
            if ($this->isInstitutionAdmin && $this->institutionId) {
                if (!$user || $user->institution_id !== $this->institutionId) {
                    // User doesn't exist or doesn't belong to this institution
                    session()->flash('error_message', 'You do not have permission to view this user.');
                    return;
                }
            }
            
            $this->user = $user;
            $this->trustScore = $user->trust_score;
            $this->userType = $user->type; // Initialize user type
        }
    }
    
    // New method to save trust score
    public function saveTrustScore()
    {
        if (!$this->user) {
            return;
        }
        
        // Validate the input
        $this->validate();
        
        // Store old value for audit log
        $oldScore = $this->user->trust_score;
        
        // Prevent no-op updates
        if ($oldScore == $this->trustScore) {
            return; // Silently skip if no change
        }
        
        // Update the user's trust score
        $this->user->trust_score = $this->trustScore;
        $this->user->save();

        // Audit log the trust score change
        AuditLogService::logUpdate(
            resourceType: 'User',
            resourceId: $this->user->id,
            before: ['trust_score' => $oldScore],
            after: ['trust_score' => $this->trustScore],
            message: "Updated trust score for user {$this->user->email} from {$oldScore} to {$this->trustScore}"
        );
    }
    
    // New method to save user type
    public function saveUserType()
    {
        if (!$this->user) {
            return;
        }
        
        $currentUser = Auth::user();
        
        // Security checks to prevent privilege escalation
        // 1. Cannot change own account type
        if ($this->user->id === $currentUser->id) {
            session()->flash('modal_message', 'You cannot change your own account type.');
            return;
        }
        
        // 2. Cannot change super_admin accounts at all
        if ($this->user->type === 'super_admin') {
            session()->flash('modal_message', 'Super Admin accounts cannot be modified.');
            return;
        }
        
        // 3. Cannot set anyone to super_admin
        if ($this->userType === 'super_admin') {
            session()->flash('modal_message', 'Cannot assign Super Admin role through this interface.');
            return;
        }
        
        // 4. Institution admins have restricted permissions
        if ($this->isInstitutionAdmin) {
            // Cannot modify institution_admin or researcher accounts
            if (in_array($this->user->type, ['institution_admin', 'researcher'])) {
                session()->flash('modal_message', 'You do not have permission to modify this user type.');
                return;
            }
            
            // Cannot promote users to institution_admin or researcher
            if (in_array($this->userType, ['institution_admin', 'researcher'])) {
                session()->flash('modal_message', 'You do not have permission to assign this user type.');
                return;
            }
        }
        
        // 5. Only super_admin can create/modify institution_admin and researcher accounts
        if ($currentUser->type !== 'super_admin' && in_array($this->userType, ['institution_admin', 'researcher'])) {
            session()->flash('modal_message', 'Only Super Admins can assign Institution Admin or Researcher roles.');
            return;
        }
        
        // Validate the input
        $this->validate([
            'userType' => [
                'required',
                Rule::in(['respondent', 'researcher', 'institution_admin'])
            ]
        ]);
        
        // Store old value for audit log
        $oldType = $this->user->type;
        
        // Prevent no-op updates
        if ($oldType === $this->userType) {
            return; // Silently skip if no change
        }
        
        // Update the user's type
        $this->user->type = $this->userType;
        $this->user->save();

        // Audit log the type change
        AuditLogService::logUpdate(
            resourceType: 'User',
            resourceId: $this->user->id,
            before: ['type' => $oldType],
            after: ['type' => $this->userType],
            message: "Changed user type for {$this->user->email} from {$oldType} to {$this->userType}"
        );
        
        $this->dispatch('userStatusUpdated');
    }
    
    // New method to handle saving both trust score and user type
    public function saveChanges()
    {
        if (!$this->user) {
            return;
        }
        
        // Save both trust score and user type
        $this->saveTrustScore();
        $this->saveUserType();
        
        // Dispatch success event
        $this->dispatch('changesSavedSuccessfully');
    }
    
    public function toggleActiveStatus()
    {
        if (!$this->user) {
            return;
        }
        
        // Don't allow deactivating your own account
        if ($this->user->id === auth()->id()) {
            session()->flash('modal_message', 'You cannot change your own account status.');
            return;
        }
        
        // Institution admins can't modify super_admin accounts
        if ($this->isInstitutionAdmin && $this->user->type === 'super_admin') {
            session()->flash('modal_message', 'You do not have permission to modify this user.');
            return;
        }

        // Store old status for audit log
        $oldStatus = $this->user->is_active;
        
        $this->user->is_active = !$this->user->is_active;
        $this->user->save();
        
        $status = $this->user->is_active ? 'activated' : 'deactivated';

        // Audit log the status change
        AuditLogService::logUpdate(
            resourceType: 'User',
            resourceId: $this->user->id,
            before: ['is_active' => $oldStatus],
            after: ['is_active' => $this->user->is_active],
            message: ucfirst($status) . " user account: {$this->user->email}"
        );

        session()->flash('modal_message', "User has been {$status} successfully.");
        
        // Force a full refresh to ensure the UI updates
        $this->dispatch('userStatusUpdated');
    }
    
    public function archiveUser()
    {
        if (!$this->user) {
            return;
        }
        
        // Don't allow archiving your own account
        if ($this->user->id === auth()->id()) {
            session()->flash('modal_message', 'You cannot archive your own account.');
            return;
        }
        
        // Institution admins can't modify super_admin accounts
        if ($this->isInstitutionAdmin && $this->user->type === 'super_admin') {
            session()->flash('modal_message', 'You do not have permission to modify this user.');
            return;
        }

        // Capture user data before archiving
        $userData = [
            'email' => $this->user->email,
            'name' => $this->user->name,
            'type' => $this->user->type,
            'institution_id' => $this->user->institution_id,
            'points' => $this->user->points,
            'trust_score' => $this->user->trust_score,
        ];
        
        $this->user->delete(); // Soft delete
        $this->user->refresh(); // Refresh the model to get the updated deleted_at timestamp

        // Audit log the archiving
        AuditLogService::logArchive(
            resourceType: 'User',
            resourceId: $this->user->id,
            message: "Archived user: {$userData['email']} ({$userData['type']})"
        );
        
        session()->flash('modal_message', 'User has been archived successfully.');
        $this->dispatch('userStatusUpdated');
    }
    
    public function restoreUser()
    {
        if (!$this->user || !$this->user->trashed()) {
            return;
        }
        
        // Institution admins can't modify super_admin accounts
        if ($this->isInstitutionAdmin && $this->user->type === 'super_admin') {
            session()->flash('modal_message', 'You do not have permission to modify this user.');
            return;
        }
        
        $this->user->restore();
        $this->user->refresh(); // Refresh the model to make sure deleted_at is null

        // Audit log the restoration
        AuditLogService::logRestore(
            resourceType: 'User',
            resourceId: $this->user->id,
            message: "Restored user: {$this->user->email} ({$this->user->type})"
        );
        
        session()->flash('modal_message', 'User has been restored successfully.');
        $this->dispatch('userStatusUpdated');
    }
    
    public function loadUserActivities()
    {
        if (!$this->user || $this->activitiesLoaded) {
            return;
        }
        
        // Get survey responses (surveys answered)
        $responses = Response::where('user_id', $this->user->id)
            ->with('survey:id,title')
            ->get()
            ->map(function ($response) {
                return [
                    'id' => $response->id,
                    'type' => 'survey_response',
                    'action' => 'Answered Survey',
                    'details' => $response->survey ? $response->survey->title : 'Unknown Survey',
                    'created_at' => $response->created_at,
                ];
            });
            
        // Get reward redemptions
        $redemptions = RewardRedemption::where('user_id', $this->user->id)
            ->with('reward:id,name,type')
            ->get()
            ->map(function ($redemption) {
                return [
                    'id' => $redemption->id,
                    'type' => 'reward_redemption',
                    'action' => 'Redeemed Reward',
                    'details' => $redemption->reward ? 
                        "{$redemption->reward->name} ({$redemption->reward->type}) - {$redemption->points_spent} points" : 
                        "Unknown Reward - {$redemption->points_spent} points",
                    'created_at' => $redemption->created_at,
                    'status' => $redemption->status,
                ];
            });
            
        // Get created surveys
        $surveys = Survey::where('user_id', $this->user->id)
            ->get()
            ->map(function ($survey) {
                return [
                    'id' => $survey->id,
                    'type' => 'survey_created',
                    'action' => 'Created Survey',
                    'details' => "{$survey->title} ({$survey->status})",
                    'created_at' => $survey->created_at,
                ];
            });
    
        // NEW: Get demographic updates
        $demographicUpdates = [];
        if ($this->user->demographic_tags_updated_at) {
            $demographicUpdates[] = [
                'id' => 'demographic_' . $this->user->id,
                'type' => 'demographic_update',
                'action' => 'Updated Demographics',
                'details' => 'User updated their demographic information',
                'created_at' => $this->user->demographic_tags_updated_at,
            ];
        }
        
        // NEW: Get reports received
        $reportsReceived = \App\Models\Report::where('respondent_id', $this->user->id)
            ->with(['survey:id,title', 'reporter:id,first_name,last_name'])
            ->get()
            ->map(function ($report) {
                return [
                    'id' => 'report_received_' . $report->id,
                    'type' => 'report_received',
                    'action' => 'Was Reported',
                    'details' => "Reported by {$report->reporter->name} on survey: {$report->survey->title} - {$report->reason}",
                    'created_at' => $report->created_at,
                    'status' => $report->status
                ];
            });
        
        // NEW: Get reports made
        $reportsMade = \App\Models\Report::where('reporter_id', $this->user->id)
            ->with(['survey:id,title', 'respondent:id,first_name,last_name'])
            ->get()
            ->map(function ($report) {
                $respondentName = $report->respondent ? $report->respondent->name : 'Unknown User';
                return [
                    'id' => 'report_made_' . $report->id,
                    'type' => 'report_made',
                    'action' => 'Reported User',
                    'details' => "Reported {$respondentName} on survey: {$report->survey->title} - {$report->reason}",
                    'created_at' => $report->created_at,
                    'status' => $report->status
                ];
            });
        
        // NEW: Get vouchers redeemed/activated
        $vouchers = \App\Models\UserVoucher::where('user_id', $this->user->id)
            ->with(['voucher.reward:id,name'])
            ->get()
            ->map(function ($userVoucher) {
                $actionType = '';
                $timestamp = $userVoucher->created_at;
                
                if ($userVoucher->status === 'used' && $userVoucher->used_at) {
                    $actionType = 'Used';
                    $timestamp = $userVoucher->used_at;
                } elseif ($userVoucher->status === 'active' && $userVoucher->activated_at) {
                    $actionType = 'Activated';
                    $timestamp = $userVoucher->activated_at;
                } else {
                    $actionType = 'Acquired';
                }
                
                $voucherName = $userVoucher->voucher && $userVoucher->voucher->reward ? 
                    $userVoucher->voucher->reward->name : 'Unknown Voucher';
                
                return [
                    'id' => 'voucher_' . $userVoucher->id,
                    'type' => 'voucher_activity',
                    'action' => $actionType . ' Voucher',
                    'details' => "{$voucherName} - Status: {$userVoucher->status}",
                    'created_at' => $timestamp ?: now(), // Fallback to current time if null
                ];
            });
            
        // Combine all activities
        $allActivities = collect([
            ...$responses, 
            ...$redemptions, 
            ...$surveys, 
            ...$demographicUpdates,
            ...$reportsReceived,
            ...$reportsMade,
            ...$vouchers
        ])
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
            
        $this->activities = $allActivities;
        $this->activitiesLoaded = true;
    }
    
    public function render()
    {
        return view('livewire.super-admin.user-list.modal.user-view-modal');
    }
}
