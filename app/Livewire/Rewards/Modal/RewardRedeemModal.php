<?php

namespace App\Livewire\Rewards\Modal;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Voucher; // Import Voucher model
use App\Models\UserVoucher; // Import UserVoucher model
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log

class RewardRedeemModal extends Component
{
    public $reward;
    public $redeemQuantity = 1;
    public $gcashNumber = '';
    public $confirmGcashCorrect = false;

    // Add validation rules
    protected function rules()
    {
        $rules = [
            'redeemQuantity' => 'required|integer|min:1',
        ];
        
        // Add GCash validation only for monetary rewards
        if ($this->reward && $this->reward->type === 'monetary') {
            $rules['gcashNumber'] = 'required|numeric|digits:11';
            $rules['confirmGcashCorrect'] = 'accepted';
        }
        
        return $rules;
    }
    
    protected $messages = [
        'confirmGcashCorrect.accepted' => 'Please confirm your GCash number is correct before proceeding.',
    ];

    // Mount the component with the selected reward
    public function mount($reward)
    {
        $this->reward = $reward;
    }

    public function confirmRedemption()
    {
        if (!$this->reward || !Auth::check()) {
            $this->dispatch('redemptionError', 'Could not process redemption. Please try again.');
            $this->dispatch('close-modal', name: 'reward-redeem-modal'); // Use named parameters for clarity
            return;
        }

        $user = Auth::user();
        $quantityToRedeem = ($this->reward->type === 'system') ? (int)$this->redeemQuantity : 1; // Vouchers/Monetary are 1 at a time
        $totalCost = $this->reward->cost * $quantityToRedeem;

        // Validate the input
        if ($this->reward->type === 'monetary') {
            $this->validate(); // Validates gcashNumber and confirmGcashCorrect
        } elseif ($this->reward->type === 'system') {
            $this->validate(['redeemQuantity' => 'required|integer|min:1']);
        }
        // No specific validation for voucher type beyond general checks, quantity is 1

        if ($quantityToRedeem <= 0 && $this->reward->type === 'system') { // Only system rewards can have variable quantity > 0
            $this->dispatch('redemptionError', 'Quantity must be at least 1.');
            // $this->dispatch('close-modal', name: 'reward-redeem-modal'); // Keep modal open to show error
            return;
        }

        if ($user->points < $totalCost) {
            $this->dispatch('redemptionError', 'Not enough points to redeem this reward.');
            // $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }
        
        // For non-system rewards, quantity check is against the reward itself.
        // For system rewards with quantity != -1, it's against reward->quantity.
        // For voucher rewards, we also need to check if a specific voucher instance is available.
        if ($this->reward->type !== 'system' && $this->reward->quantity != -1 && $this->reward->quantity < 1) {
             $this->dispatch('redemptionError', 'This reward is out of stock.');
             return;
        }
        if ($this->reward->type === 'system' && $this->reward->quantity != -1 && $this->reward->quantity < $quantityToRedeem) {
            $this->dispatch('redemptionError', 'Not enough stock available for this quantity.');
            // $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }


        try {
            DB::transaction(function () use ($user, $quantityToRedeem, $totalCost) {
                $createdRedemption = null; // To store the created redemption record

                // Deduct points
                $user->points -= $totalCost;

                $additionalMessage = '';
                $levelUpMessage = '';
                $leveledUp = false;
                $newLevel = 0;
                $newTitle = '';
                $redemptionStatus = RewardRedemption::STATUS_COMPLETED; // Default to completed

                if ($this->reward->type === 'system') {
                    if ($this->reward->name === 'Experience Level Increase') {
                        $xpToAdd = 500 * $quantityToRedeem;
                        $result = $user->addExperiencePoints($xpToAdd);
                        $additionalMessage = "You gained {$xpToAdd} experience points!";
                        if ($result['leveled_up']) {
                            $leveledUp = true;
                            $newLevel = $result['current_level'];
                            $newTitle = $user->title;
                            $levelUpMessage = " You reached level {$result['current_level']} and earned the title: {$user->title}";
                            if (!empty($result['perks'])) {
                                $levelUpMessage .= ". Bonus: " . implode(', ', $result['perks']);
                            }
                        }
                    }
                    // Handle other system rewards
                } elseif ($this->reward->type === 'voucher') {
                    // Find an available voucher instance for this reward
                    $voucherInstance = Voucher::where('reward_id', $this->reward->id)
                                              ->where('availability', 'available')
                                              ->lockForUpdate() // Prevent race conditions
                                              ->first();

                    if (!$voucherInstance) {
                        throw new \Exception('Sorry, no specific vouchers are currently available for this reward. Please try again later.');
                    }

                    // Mark the specific voucher instance as unavailable (or used by system)
                    $voucherInstance->availability = 'unavailable'; // Or 'claimed_internal'
                    $voucherInstance->save();
                    
                    // The Reward's quantity (acting as a counter for available voucher *types*) should be decremented
                    // This is distinct from the Voucher instance's availability
                    if ($this->reward->quantity != -1) {
                        $this->reward->quantity -= 1; // Decrement by 1 as one voucher type is claimed
                        if ($this->reward->quantity <= 0) {
                            $this->reward->status = Reward::STATUS_SOLD_OUT;
                        }
                    }
                    // Note: RewardRedemption record will be created after user save.
                    // UserVoucher record will be created after RewardRedemption.
                    $redemptionStatus = RewardRedemption::STATUS_COMPLETED;

                } elseif ($this->reward->type === 'monetary') {
                    $redemptionStatus = RewardRedemption::STATUS_PENDING;
                     if ($this->reward->quantity != -1) {
                        $this->reward->quantity -= 1; 
                        if ($this->reward->quantity <= 0) {
                            $this->reward->status = Reward::STATUS_SOLD_OUT;
                        }
                    }
                }
                
                $user->save();
                $this->reward->save(); // Save changes to reward quantity/status

                // Create redemption record
                $redemptionData = [
                    'user_id' => $user->id,
                    'reward_id' => $this->reward->id,
                    'points_spent' => $totalCost,
                    'status' => $redemptionStatus,
                ];
                if ($this->reward->type === 'monetary') {
                    $redemptionData['gcash_number'] = $this->gcashNumber;
                }
                $createdRedemption = RewardRedemption::create($redemptionData);

                // If it was a voucher, create UserVoucher record
                if ($this->reward->type === 'voucher' && isset($voucherInstance)) {
                    UserVoucher::create([
                        'user_id' => $user->id,
                        'voucher_id' => $voucherInstance->id,
                        'reward_redemption_id' => $createdRedemption->id,
                        'status' => 'available', // Changed from 'active' to 'available'
                    ]);
                    $additionalMessage = "Voucher {$voucherInstance->reference_no} has been added to your account.";
                }


                $successMessage = "Reward redeemed successfully!";
                if ($redemptionStatus === RewardRedemption::STATUS_COMPLETED) {
                    // $successMessage .= " Your reward has been processed."; // Generic completed
                } elseif ($redemptionStatus === RewardRedemption::STATUS_PENDING) {
                    $successMessage .= " Your reward redemption is pending approval.";
                }
                
                if (!empty($additionalMessage)) {
                    $successMessage .= " {$additionalMessage}";
                }
                if (!empty($levelUpMessage)) {
                    $successMessage .= " {$levelUpMessage}";
                }
                
                session()->flash('redeem_success', $successMessage);
                
                if ($leveledUp) {
                    $this->dispatch('level-up', ['level' => $newLevel, 'title' => $newTitle]);
                }
            });

            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            $this->dispatch('rewardRedeemed'); // Refresh RewardIndex
            $this->dispatch('reward-purchased'); // For confetti or other UI feedback
            
        } catch (\Exception $e) {
            Log::error('Redemption Error: ' . $e->getMessage() . ' for user ' . Auth::id() . ' reward ' . $this->reward->id);
            // Ensure redeemQuantity is reset if modal stays open or for next attempt
            if ($this->reward && $this->reward->type === 'system') {
                // $this->redeemQuantity = 1; // Reset for system rewards
            }
            $this->dispatch('redemptionError', 'An error occurred: ' . $e->getMessage());
            // Optionally keep modal open by not dispatching close-modal here if error is recoverable by user
            // $this->dispatch('close-modal', name: 'reward-redeem-modal');
        }
    }

    public function render()
    {
        return view('livewire.rewards.modal.reward-redeem-modal');
    }
}
