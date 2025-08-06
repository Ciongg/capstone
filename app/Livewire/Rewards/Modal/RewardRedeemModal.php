<?php

namespace App\Livewire\Rewards\Modal;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TestTimeService;

class RewardRedeemModal extends Component
{
    public $reward;
    public $redeemQuantity = 1;

    /**
     * Initialize component with selected reward
     */
    public function mount($reward)
    {
        $this->reward = $reward;
    }

  

    /**
     * Process reward redemption
     */
    public function confirmRedemption()
    {
        if (!$this->reward || !Auth::check()) {
            $this->dispatch('redemptionError', 'Could not process redemption. Please try again.');
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }

        $user = Auth::user();
        $quantityToRedeem = ($this->reward->type === 'system') ? (int)$this->redeemQuantity : 1;
        $totalCost = $this->reward->cost * $quantityToRedeem;

        $now = TestTimeService::now();

        // Pre-transaction validation
        // 1. Check if user has enough points
        if ($user->points < $totalCost) {
            $this->dispatch('redemptionError', 'Not enough points to redeem this reward.');
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }
        
        // 2. Check for system rewards quantity
        if ($this->reward->type === 'system') {
            if ($this->redeemQuantity <= 0) {
                $this->dispatch('redemptionError', 'Quantity must be at least 1 for system rewards.');
                $this->dispatch('close-modal', name: 'reward-redeem-modal');
                return;
            }
            
            if ($this->reward->quantity != -1 && $this->redeemQuantity > $this->reward->quantity) {
                $this->dispatch('redemptionError', 'Requested quantity exceeds available stock.');
                $this->dispatch('close-modal', name: 'reward-redeem-modal');
                return;
            }
        }
        
        // 3. Check voucher availability
        if ($this->reward->type === 'voucher') {
            // Only allow if there is at least one available voucher with expiry_date not passed
            $voucherInstance = Voucher::where('reward_id', $this->reward->id)
                ->where('availability', 'available')
                ->where(function($q) use ($now) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>', $now);
                })
                ->lockForUpdate()
                ->first();

            if (!$voucherInstance) {
                $this->dispatch('redemptionError', 'This voucher is out of stock or expired.');
                $this->dispatch('close-modal', name: 'reward-redeem-modal');
                return;
            }
        }

        try {
            DB::transaction(function () use ($user, $quantityToRedeem, $totalCost, $now) {
                // Deduct points from user
                $user->points -= $totalCost;
                
                $levelUpMessage = '';
                $leveledUp = false;
                $newLevel = 0;
                $newTitle = '';
                $redemptionStatus = RewardRedemption::STATUS_COMPLETED; //same as "completed"

                // Process based on reward type
                if ($this->reward->type === 'system') {

                    // Handle system reward (e.g., Experience Level Increase)
                    if ($this->reward->name === 'Experience Level Increase') {
                        $xpToAdd = 10 * $quantityToRedeem;
                        $oldRank = $user->rank; // Store old rank before adding XP
                        $result = $user->addExperiencePoints($xpToAdd);
                        if ($result['leveled_up']) {
                            $leveledUp = true;
                            $newLevel = $result['new_level'];
                            $newRank = $result['new_rank'] ?? $user->rank;
                        }
                    }
                    
                    // Handle Survey Boost system reward
                    if ($this->reward->name === 'Survey Boost') {
                        // Create or update user's survey boost rewards
                        $existingBoost = \App\Models\UserSystemReward::where('user_id', $user->id)
                            ->where('type', 'survey_boost')
                            ->where('status', 'unused')
                            ->first();
                            
                        if ($existingBoost) {
                            $existingBoost->quantity += $quantityToRedeem;
                            $existingBoost->save();
                        } else {
                            \App\Models\UserSystemReward::create([
                                'user_id' => $user->id,
                                'type' => 'survey_boost',
                                'quantity' => $quantityToRedeem,
                                'status' => 'unused'
                            ]);
                        }
                    }
                    
                    // Decrement system reward quantity if limited
                    if ($this->reward->quantity != -1) {
                        $this->reward->quantity -= $quantityToRedeem;
                        if ($this->reward->quantity <= 0) {
                            $this->reward->status = Reward::STATUS_SOLD_OUT;
                        }
                    }
                } else { // Voucher type
                    // Find and claim a voucher instance (repeat the expiry check inside transaction)
                    $voucherInstance = Voucher::where('reward_id', $this->reward->id)
                        ->where('availability', 'available')
                        ->where(function($q) use ($now) {
                            $q->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>', $now);
                        })
                        ->lockForUpdate()
                        ->first();

                    if (!$voucherInstance) {
                        throw new \Exception('No valid vouchers are available for this reward. Please try again later.');
                    }

                    // Mark voucher as unavailable
                    $voucherInstance->availability = 'unavailable';
                    $voucherInstance->save();

                    // Decrement voucher reward quantity if limited
                    if ($this->reward->quantity != -1) {
                        $this->reward->quantity -= 1;
                        if ($this->reward->quantity <= 0) {
                            $this->reward->status = Reward::STATUS_SOLD_OUT;
                        }
                    }
                }
                
                // Save changes
                $user->save();
                $this->reward->save();

                // Create redemption record
                $redemptionData = [
                    'user_id' => $user->id,
                    'reward_id' => $this->reward->id,
                    'points_spent' => $totalCost,
                    'status' => $redemptionStatus,
                    'quantity'  => $quantityToRedeem,
                ];
                
                $createdRedemption = RewardRedemption::create($redemptionData);

                //create user voucher connection if applicable
                if ($this->reward->type === 'voucher' && isset($voucherInstance)) {
                    UserVoucher::create([
                        'user_id' => $user->id,
                        'voucher_id' => $voucherInstance->id,
                        'reward_redemption_id' => $createdRedemption->id,
                        'status' => 'available',
                    ]);
                }
                
                //success in modal
                $this->dispatch('redemption-success', 'Reward redeemed successfully!');
                // Keep the existing event for compatibility
                $this->dispatch('redeem_success', 'Success Purchase!');

                // Dispatch level-up event if applicable
                if ($leveledUp) {
                    $this->dispatch('level-up', [
                        'level' => $newLevel,
                        'rank' => $newRank,
                        'old_rank' => $oldRank ?? $user->rank
                    ]);
                }
            });

            // Close modal and dispatch completion events
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            $this->dispatch('reward-purchased');
            
        } catch (\Exception $e) {
            // Log::error('Redemption Error: ' . $e->getMessage() . ' for user ' . Auth::id() . ' reward ' . $this->reward->id);
            $this->dispatch('redemptionError', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Calculate total cost
     */
    public function getTotalCost()
    {
        if (!$this->reward) return 0;
        return $this->reward->cost * ($this->reward->type === 'system' ? max(1, (int)$this->redeemQuantity) : 1);
    }
    
    /**
     * Get the actual available quantity for display and validation
     */
    public function getActualAvailableQuantity()
    {
        if (!$this->reward) return 0;
        
        // For voucher rewards, count actual available vouchers
        if ($this->reward->type === 'voucher') {
            return Voucher::where('reward_id', $this->reward->id)
                ->where('availability', 'available')
                ->count();
        }
        
        // For system rewards, use the quantity field
        return $this->reward->quantity;
    }
    
    /**
     * Check if button should be disabled
     */
    public function isButtonDisabled()
    {
        if (!$this->reward) return true;
        
        $user = Auth::user();
        $calculatedCost = $this->getTotalCost();
        $availableQuantity = $this->getActualAvailableQuantity();
        
        // Check for conditions that would disable the button
        if ($user->points < $calculatedCost) {
            return true;
        }
        
        if ($this->reward->type === 'system') {
            if ($this->redeemQuantity <= 0) {
                return true;
            }
            
            if ($this->reward->quantity != -1 && $this->redeemQuantity > $availableQuantity) {
                return true;
            }
        } else { // Voucher type
            if ($availableQuantity <= 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get validation error message based on current state
     */
    public function getErrorMessage()
    {
        if (!$this->reward) return '';
        
        $user = Auth::user();
        $calculatedCost = $this->getTotalCost();
        $availableQuantity = $this->getActualAvailableQuantity();
        
        if ($user->points < $calculatedCost) {
            return "You don't have enough points.";
        }
        
        if ($this->reward->type === 'system') {
            if ($this->reward->quantity != -1 && $this->redeemQuantity > $availableQuantity) {
                return "Requested quantity exceeds available stock.";
            }
            
            if ($this->redeemQuantity <= 0) {
                return "Quantity must be at least 1 for system rewards.";
            }
        } else { // Voucher type
            if ($availableQuantity <= 0) {
                return "This voucher is currently out of stock.";
            }
        }
        
        return '';
    }

    public function render()
    {
        $availableQuantity = $this->getActualAvailableQuantity();
        
        return view('livewire.rewards.modal.reward-redeem-modal', [
            'availableQuantity' => $availableQuantity
        ]);
    }
}
