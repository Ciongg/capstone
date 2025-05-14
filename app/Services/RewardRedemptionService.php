<?php

namespace App\Services;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class RewardRedemptionService
{
    /**
     * Process a reward redemption
     *
     * @param User $user
     * @param Reward $reward
     * @return RewardRedemption
     * @throws Exception
     */
    public function processRedemption(User $user, Reward $reward)
    {
        // Check if user has enough points
        if ($user->points < $reward->cost) {
            throw new Exception("You don't have enough points to redeem this reward.");
        }

        // Check if reward is available
        if ($reward->status !== Reward::STATUS_AVAILABLE) {
            throw new Exception("This reward is no longer available.");
        }

        // Check if reward is in stock (if quantity is limited)
        if ($reward->quantity !== -1 && $reward->quantity <= 0) {
            throw new Exception("This reward is out of stock.");
        }

        try {
            DB::beginTransaction();

            // Deduct points from user
            $user->points -= $reward->cost;
            $user->save();

            // Decrement reward quantity if it's not infinite
            if ($reward->quantity !== -1) {
                $reward->quantity--;
                $reward->save();
            }

            // Set status based on reward type
            // Monetary rewards need manual approval (pending)
            // System and voucher rewards are automatically completed
            $status = ($reward->type === 'monetary') 
                ? RewardRedemption::STATUS_PENDING 
                : RewardRedemption::STATUS_COMPLETED;

            // Create redemption record
            $redemption = new RewardRedemption([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->cost,
                'reward_type' => $reward->type,
                'status' => $status,
            ]);
            
            $redemption->save();

            DB::commit();
            return $redemption;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
