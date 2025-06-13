<?php

namespace App\Livewire\Rewards;

use App\Models\Reward;
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
            'selectedReward' => $this->selectedReward
        ]);
    }
}
