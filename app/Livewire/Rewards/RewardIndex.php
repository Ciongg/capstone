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

    // Get rewards from database
    public function getSystemRewardsProperty()
    {
        return Reward::where('type', 'system')
            ->where('status', 'available')
            ->get();
    }
    
    public function getVoucherRewardsProperty()
    {
        return Reward::where('type', 'voucher')
            ->where('status', 'available')
            ->get();
    }

    public function getMonetaryRewardsProperty()
    {
        return Reward::where('type', 'monetary')
            ->where('status', 'available')
            ->get();
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
        $xpNeeded = $user->getXpRequiredForNextLevel() - ($user->experience_points % $user->getXpRequiredForNextLevel());
        
        // Add just enough XP to level up
        $result = $user->addExperiencePoints($xpNeeded);
        
        $newLevel = $user->getLevel();
        
        // Make sure title is updated
        if ($result['leveled_up'] && empty($user->title)) {
            $user->updateTitle();
        }
        
        $newTitle = $user->title ?: 'Newbie';
        
        // Show level up animation
        $this->dispatch('level-up', [
            'level' => $newLevel,
            'title' => $newTitle
        ]);
        
        session()->flash('message', "Leveled up to level {$newLevel}! Your new title is: {$newTitle}");
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
        
        // Reset title to Level 1 title
        $user->title = UserExperienceService::getTitleForLevel(1);
        
        $user->save();
        
        session()->flash('message', "Reset to level 1 with title: {$user->title}!");
    }
    
    protected function getListeners()
    {
        return [
            'open-modal' => 'openRedeemModal',
            'redemptionError' => 'handleRedemptionError',
            'rewardRedeemed' => '$refresh' 
        ];
    }

    public function openRedeemModal($data = null)
    {
        if ($data && isset($data['name']) && $data['name'] === 'reward-redeem-modal') {
            // We don't need to set the ID here as it's already set via Alpine
            // Just a fallback if rewardId is explicitly provided
            if (isset($data['rewardId'])) {
                $this->selectedRewardId = $data['rewardId'];
            }
        }
    }

    public function handleRedemptionError($message)
    {
        session()->flash('redeem_error', $message);
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
            'monetaryRewards' => $this->monetaryRewards,
            'selectedReward' => $this->selectedReward
        ]);
    }
}
