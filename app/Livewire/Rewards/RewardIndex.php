<?php

namespace App\Livewire\Rewards;

use App\Models\Reward;
use App\Models\Voucher;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RewardIndex extends Component
{
    public $activeTab = 'system';
    // to trakc which reward user clicked on done via livewire wire.set selectedRewardId in reward card
    public $selectedRewardId = null;

    //listens to dispatch events
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
        
        // Check if user's trust score is too low (70 or below)
        if ($user->trust_score <= 70) {
            return true;
        }
        
        // Check if user has enough points
        if ($user->points < $reward->cost) {
            return true;
        }
        
        // For voucher rewards, check actual available vouchers (not expired, not unavailable, not used)
        if ($reward->type == 'voucher' || $reward->type == 'Voucher') {
            $availableVouchers = \App\Models\Voucher::where('reward_id', $reward->id)
                ->where('availability', 'available')
                ->count();
            if ($availableVouchers <= 0) {
                return true;
            }
        }
        // For system rewards, check the quantity field
        else if ($reward->quantity != -1 && $reward->quantity <= 0) {
            return true;
        }
        
        // Check rank requirements
        $rankPriority = [
            'silver' => 1,
            'gold' => 2,
            'diamond' => 3
        ];
        
        $userRank = strtolower($user->rank ?? 'silver');
        $requiredRank = strtolower($reward->rank_requirement ?? 'silver');
        
        $userRankPriority = $rankPriority[$userRank] ?? 1;
        $requiredRankPriority = $rankPriority[$requiredRank] ?? 1;
        
        if ($userRankPriority < $requiredRankPriority) {
            return true;
        }
        
        // All checks passed, button should be enabled
        return false;
    }

    /**
     * Get the reason why a reward is disabled
     * 
     * @param Reward $reward
     * @return string
     */
    public function getDisabledReason($reward): string
    {
        if (!Auth::check()) {
            return "Please log in";
        }
        
        $user = Auth::user();
        
        // Check if user's trust score is too low
        if ($user->trust_score <= 70) {
            return "Low Trust Score";
        }
        
        // Check if user has enough points
        if ($user->points < $reward->cost) {
            return "Not Enough Points";
        }
        
        // For voucher rewards, check actual available vouchers
        if ($reward->type == 'voucher' || $reward->type == 'Voucher') {
            $availableVouchers = \App\Models\Voucher::where('reward_id', $reward->id)
                ->where('availability', 'available')
                ->count();
            if ($availableVouchers <= 0) {
                return "Out of Stock";
            }
        }
        // For system rewards, check the quantity field
        else if ($reward->quantity != -1 && $reward->quantity <= 0) {
            return "Out of Stock";
        }
        
        // Check rank requirements
        $rankPriority = [
            'silver' => 1,
            'gold' => 2,
            'diamond' => 3
        ];
        
        $userRank = strtolower($user->rank ?? 'silver');
        $requiredRank = strtolower($reward->rank_requirement ?? 'silver');
        
        $userRankPriority = $rankPriority[$userRank] ?? 1;
        $requiredRankPriority = $rankPriority[$requiredRank] ?? 1;
        
        if ($userRankPriority < $requiredRankPriority) {
            return ucfirst($requiredRank) . " Rank Required";
        }
        
        return "Redeem";
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
        if ($user) {
            $userLevel = $user->getLevel();
            $levelProgress = $user->getLevelProgressPercentage();
            $xpForNextLevel = $user->getXpRequiredForNextLevel();
        } else {
            $userLevel = 1;
            $levelProgress = 0;
            $xpForNextLevel = 100;
        }
        
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

