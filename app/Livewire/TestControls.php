<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\Auth;

class TestControls extends Component
{
    public $userExperience = 0;
    public $userLevel = 1;
    public $xpForNextLevel = 100;
    public $levelProgress = 0;
    public $rank = 'silver';
    public $userPoints = 0;

    // New properties for inbox testing
    public $inboxSubject = 'Test Message';
    public $inboxMessage = 'This is a test message for the inbox system.';
    public $inboxUrl = '';

    public function mount()
    {
        $this->refreshUserStats();
    }

    public function refreshUserStats()
    {
        $user = Auth::user();
        if ($user) {
            $this->userExperience = $user->experience_points ?? 0;
            $this->userLevel = $user->getLevel();
            $this->xpForNextLevel = $user->getXpRequiredForNextLevel();
            $this->levelProgress = $user->getLevelProgressPercentage();
            $this->rank = $user->rank ?? 'silver';
            $this->userPoints = $user->points ?? 0;
        }
    }
    
    public function render()
    {
        return view('livewire.test-controls');
    }
    
    // Points Controls
    public function addPoints($amount)
    {
        $user = Auth::user();
        $user->points = $user->points + $amount;
        $user->save();
        
        $this->refreshUserStats();
        session()->flash('message', "+{$amount} points added!");
    }
    
    public function subtractPoints($amount)
    {
        $user = Auth::user();
        $user->points = max(0, $user->points - $amount);
        $user->save();
        
        $this->refreshUserStats();
        session()->flash('message', "-{$amount} points subtracted!");
    }
    
    // XP Controls
    public function levelUp()
    {
        $user = Auth::user();
        $currentLevel = $user->getLevel();
        $oldRank = $user->rank ?? 'silver'; // Store old rank
        $newLevel = min($currentLevel + 1, 30); // Cap at level 30
        
        // Calculate XP needed for new level and set it
        $xpNeeded = \App\Services\UserExperienceService::xpRequiredForLevel($newLevel);
        $user->experience_points = $xpNeeded;
        $user->account_level = $newLevel;
        
        // Update rank based on new level
        $newRank = 'silver'; // Default
        if ($newLevel >= 21) {
            $newRank = 'diamond';
        } elseif ($newLevel >= 11) {
            $newRank = 'gold';
        }
        
        $user->rank = $newRank;
        $user->save();
        
        // Dispatch level-up event globally with old rank info
        $this->dispatch('level-up', [
            'level' => $newLevel,
            'rank' => $newRank,
            'old_rank' => $oldRank
        ])->to(null);
        
        $this->refreshUserStats();
        session()->flash('message', "Level increased to $newLevel!");
    }
    
    public function addXp($amount)
    {
        $user = Auth::user();
        $oldLevel = $user->getLevel();
        $oldRank = $user->rank ?? 'silver';
        $oldXp = $user->experience_points ?? 0;
        
        // Add XP directly
        $user->experience_points = $oldXp + $amount;
        
        // Calculate new level based on new XP
        $newLevel = \App\Services\UserExperienceService::calculateLevel($user->experience_points);
        
        // Check if user leveled up
        if ($newLevel > $oldLevel) {
            // Update account_level and rank if needed
            $user->account_level = $newLevel;
            
            // Update rank based on level thresholds
            $newRank = 'silver'; // Default
            if ($newLevel >= 21) {
                $newRank = 'diamond';
            } elseif ($newLevel >= 11) {
                $newRank = 'gold';
            }
            
            $user->rank = $newRank;
            $user->save();
            
            // Dispatch level-up event globally with old rank info
            $this->dispatch('level-up', [
                'level' => $newLevel,
                'rank' => $newRank,
                'old_rank' => $oldRank
            ])->to(null);
            
            if ($newLevel >= 30) {
                session()->flash('message', "Added $amount XP and reached MAX LEVEL {$newLevel}!");
            } else {
                session()->flash('message', "Added $amount XP and leveled up to {$newLevel}!");
            }
        } else {
            $user->save();
            session()->flash('message', "Added $amount XP!");
        }
        
        $this->refreshUserStats();
    }
    
    public function resetLevel()
    {
        $user = Auth::user();
        
        // Reset all XP and level data
        $user->experience_points = 0;
        $user->account_level = 1;
        $user->rank = 'silver';
        $user->save();
        
        $this->refreshUserStats();
        session()->flash('message', "Level and XP reset to 1!");
    }
    
    // New method to send test inbox message
    public function sendTestInboxMessage()
    {
        $user = Auth::user();
        
        InboxMessage::create([
            'sender_id' => $user->id,
            'recipient_id' => $user->id,
            'subject' => $this->inboxSubject,
            'message' => $this->inboxMessage,
            'url' => $this->inboxUrl ?: null,
        ]);
        
        // Emit an event to refresh the inbox component
        $this->dispatch('refreshInbox');
        
        session()->flash('message', 'Test message sent to your inbox!');
    }
}
