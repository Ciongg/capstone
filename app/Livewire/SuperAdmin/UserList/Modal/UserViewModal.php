<?php

namespace App\Livewire\SuperAdmin\UserList\Modal;

use App\Models\User;
use App\Models\Response;
use App\Models\Survey;
use App\Models\RewardRedemption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserViewModal extends Component
{
    public $user = null;
    public $userId;
    public $activities = [];
    public $activitiesLoaded = false;
    public $isInstitutionAdmin = false;
    public $institutionId = null;
    public $trustScore = 100; // New property for editing trust score
    
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
            $this->trustScore = $user->trust_score; // Initialize trust score from user
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
        
        // Store old value for message
        $oldScore = $this->user->trust_score;
        
        // Update the user's trust score
        $this->user->trust_score = $this->trustScore;
        $this->user->save();
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
        
        $this->user->is_active = !$this->user->is_active;
        $this->user->save();
        
        $status = $this->user->is_active ? 'activated' : 'deactivated';
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
        
        $this->user->delete(); // Soft delete
        $this->user->refresh(); // Refresh the model to get the updated deleted_at timestamp
        
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
                    'created_at' => $response->created_at, // This is already a Carbon instance
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
                    'created_at' => $redemption->created_at, // This is already a Carbon instance
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
                    'created_at' => $survey->created_at, // This is already a Carbon instance
                ];
            });
            
        // Combine all activities
        $allActivities = collect([...$responses, ...$redemptions, ...$surveys])
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
