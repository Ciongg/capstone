<?php

namespace App\Livewire\Rewards;

use App\Models\Reward;
use App\Services\UserExperienceService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RewardIndex extends Component
{
    public $activeTab = 'system';
    public $selectedRewardId = null;

    protected function getListeners()
    {
        return [
            'redemptionError' => 'handleRedemptionError',
            'rewardRedeemed' => '$refresh',
            'redeem_success' => 'handleRedemptionSuccess',
        ];
    }

    // Get rewards from database
    public function getSystemRewardsProperty()
    {
        return Reward::where('type', 'system')
            ->get(); // Show all system rewards regardless of availability
    }
    
    public function getVoucherRewardsProperty()
    {
        return Reward::where('type', 'voucher')
            ->get(); // Show all voucher rewards regardless of availability
    }

  
    // Updated method for fetching selected reward
    public function getSelectedRewardProperty()
    {
        if (!$this->selectedRewardId) {
            return null;
        }
        
        return Reward::find($this->selectedRewardId);
    }

    // Switch between tabs
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
 * Check if the reward redeem button should be disabled
 * 
 * @param Reward $reward
 * @return bool
 */
public function isRewardDisabled($reward): bool
{
    if (!Auth::check()) {
        return true;
    }
    
    $user = Auth::user();
    
    // Check if user has enough points
    if ($user->points < $reward->cost) {
        return true;
    }
    
    // Check if reward is available
    if ($reward->quantity != -1 && $reward->quantity <= 0) {
        return true;
    }
    
    // If user's rank doesn't meet the minimum required rank for the reward
    if ($reward->min_rank) {
        $rankPriority = [
            'silver' => 1,
            'gold' => 2,
            'diamond' => 3
        ];
        
        $userRankPriority = $rankPriority[$user->rank] ?? 0;
        $requiredRankPriority = $rankPriority[$reward->min_rank] ?? 999;
        
        if ($userRankPriority < $requiredRankPriority) {
            return true;
        }
    }
    
    // All checks passed, button should be enabled
    return false;
}


    public function handleRedemptionError($message)
    {
        session()->flash('redeem_error', $message);
    }

      public function handleRedemptionSuccess($message)
    {
        session()->flash('redeem_success', $message);
    }
    
    public function render()
    {
        $user = Auth::user();
        
        // Calculate user level and XP progress
        $userLevel = $user ? $user->getLevel() : 1;
        $levelProgress = $user ? $user->getLevelProgressPercentage() : 0;
        $xpForNextLevel = $user ? $user->getXpRequiredForNextLevel() : 100;
        
        return view('livewire.rewards.reward-index', [
            'user' => $user,
            'userPoints' => $user?->points ?? 0,
            'userExperience' => $user?->experience_points ?? 0,
            'userTrustScore' => $user?->trust_score ?? 0,
            'userLevel' => $userLevel,
            'levelProgress' => $levelProgress,
            'xpForNextLevel' => $xpForNextLevel,
            'systemRewards' => $this->systemRewards,
            'voucherRewards' => $this->voucherRewards,
            // 'monetaryRewards' => $this->monetaryRewards,
            'selectedReward' => $this->selectedReward
        ]);
    }
    /**
     * Add points to the current user (for testing purposes)
     */
    public function addPoints($amount)
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        $user->points = $user->points + $amount;
        $user->save();
        
        // Flash message for feedback
        session()->flash('message', "+{$amount} points added!");
    }
    
    /**
     * Subtract points from the current user (for testing purposes)
     */
    public function subtractPoints($amount)
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        // Make sure we don't go below 0 points
        $user->points = max(0, $user->points - $amount);
        $user->save();
        
        // Flash message for feedback
        session()->flash('message', "-{$amount} points subtracted!");
    }

    /**
     * Level up the user immediately (for testing purposes)
     */
    public function levelUp()
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        $currentLevel = $user->getLevel();
        
        // Calculate exact XP needed to reach the next level
        // This calculates the total XP needed for the next level, then subtracts current XP
        $xpNeeded = $user->getXpRequiredForNextLevel() - ($user->experience_points % $user->getXpRequiredForNextLevel());
        
        // If somehow the calculation gives us 0, add 1 XP to trigger the level up
        if ($xpNeeded <= 0) {
            $xpNeeded = 1;
        }
        
        // Add XP to level up
        $result = $user->addExperiencePoints($xpNeeded);
        
        // Force refresh user data
        $user = $user->fresh();
        
        // Get the new level and rank AFTER adding XP
        $newLevel = $user->getLevel();
        $newRank = $user->rank;
        
        // Dispatch level-up event with the NEW level
        $this->dispatch('level-up', [
            'level' => $newLevel,
            'rank' => $newRank
        ]);
        
        // Show success message
        session()->flash('message', "Leveled up to level {$newLevel}! Your new rank is: " . ucfirst($newRank));
        
        // Force UI refresh
        $this->dispatch('$refresh');
    }
    
    /**
     * Reset user to level 1 (for testing purposes)
     */
    public function resetLevel()
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        $user->experience_points = 0;
        
        // Reset rank to Silver (Level 1)
        $user->rank = UserExperienceService::getRankForLevel(1);
        
        $user->save();
        
        // Update public properties
        $this->userExperience = 0;
        $this->userLevel = 1;
        $this->levelProgress = 0;
        $this->xpForNextLevel = $user->getXpRequiredForNextLevel();
        
        session()->flash('message', "Reset to level 1 with rank: " . ucfirst($user->rank));
    }

    public function addXp($amount)
{
    if (!Auth::check()) {
        return;
    }
    
    $user = Auth::user();
    $prevLevel = $user->getLevel();
    
    // Add XP directly
    $result = $user->addExperiencePoints($amount);
    
    // Force refresh user data
    $user = $user->fresh();
    
    // Get the new level and XP details
    $newLevel = $user->getLevel();
    
    // Check if user leveled up
    if ($result['leveled_up']) {
        $newRank = $user->rank;
        
        // Dispatch level-up event
        $this->dispatch('level-up', [
            'level' => $newLevel,
            'rank' => $newRank
        ]);
        
        // Show level up message
        session()->flash('message', "Added {$amount} XP and leveled up to level {$newLevel}! Your rank is: " . ucfirst($newRank));
    } else {
        // Regular XP update message
        session()->flash('message', "Added {$amount} XP. Current level: {$newLevel}");
    }
    
    // Force UI refresh
    $this->dispatch('$refresh');
}

    

    
}
