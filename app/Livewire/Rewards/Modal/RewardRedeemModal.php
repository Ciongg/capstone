<?php

namespace App\Livewire\Rewards\Modal;

use App\Models\Reward;
use App\Models\RewardRedemption;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $quantityToRedeem = (int)$this->redeemQuantity;
        $totalCost = $this->reward->cost * $quantityToRedeem;

        // Validate the input
        if ($this->reward->type === 'monetary') {
            $this->validate();
        } else {
            $this->validate([
                'redeemQuantity' => 'required|integer|min:1'
            ]);
        }

        if ($quantityToRedeem <= 0) {
            $this->dispatch('redemptionError', 'Quantity must be at least 1.');
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }

        if ($user->points < $totalCost) {
            $this->dispatch('redemptionError', 'Not enough points to redeem this quantity.');
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }

        if ($this->reward->quantity != -1 && $this->reward->quantity < $quantityToRedeem) {
            $this->dispatch('redemptionError', 'Not enough stock available for this quantity.');
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            return;
        }

        try {
            DB::transaction(function () use ($user, $quantityToRedeem, $totalCost) {
                // Deduct points
                $user->points -= $totalCost;

                // Handle specific reward types
                $additionalMessage = '';
                $levelUpMessage = '';
                $leveledUp = false;
                $newLevel = 0;
                $newTitle = '';

                if ($this->reward->name === 'Experience Level Increase') {
                    // Add experience points (500 per quantity redeemed)
                    $xpToAdd = 500 * $quantityToRedeem;
                    $result = $user->addExperiencePoints($xpToAdd);

                    $additionalMessage = "You gained {$xpToAdd} experience points!";

                    // If leveled up, add more info and prepare data for animation
                    if ($result['leveled_up']) {
                        $leveledUp = true;
                        $newLevel = $result['current_level'];
                        $newTitle = $user->title;
                        
                        $levelUpMessage = " You reached level {$result['current_level']} and earned the title: {$user->title}";

                        // If perks were earned
                        if (!empty($result['perks'])) {
                            $levelUpMessage .= ". Bonus: " . implode(', ', $result['perks']);
                        }
                    }
                }
                // Handle other reward types here as needed

                $user->save();

                // Decrement reward quantity if not infinite
                if ($this->reward->quantity != -1) {
                    $this->reward->quantity -= $quantityToRedeem;
                    if ($this->reward->quantity <= 0) {
                        $this->reward->status = 'sold_out';
                    }
                    $this->reward->save();
                }

                // Set status based on reward type
                $status = ($this->reward->type === 'monetary') 
                    ? RewardRedemption::STATUS_PENDING 
                    : RewardRedemption::STATUS_COMPLETED;

                // Create redemption record with determined status and GCash number if applicable
                $redemptionData = [
                    'user_id' => $user->id,
                    'reward_id' => $this->reward->id,
                    'points_spent' => $totalCost,
                    'status' => $status,
                ];

                // Add GCash number if this is a monetary reward
                if ($this->reward->type === 'monetary') {
                    $redemptionData['gcash_number'] = $this->gcashNumber;
                }

                RewardRedemption::create($redemptionData);

                // Set success message with any additional info
                $successMessage = "Reward redeemed successfully!";
                
                // Add status-specific message
                if ($status === RewardRedemption::STATUS_COMPLETED) {
                    $successMessage .= " Your reward has been completed automatically.";
                } else {
                    $successMessage .= " Your reward redemption is pending approval.";
                }
                
                if (!empty($additionalMessage)) {
                    $successMessage .= " {$additionalMessage}";
                }
                if (!empty($levelUpMessage)) {
                    $successMessage .= " {$levelUpMessage}";
                }
                
                session()->flash('redeem_success', $successMessage);
                
                // If user leveled up, dispatch event to show the animation
                if ($leveledUp) {
                    $this->dispatch('level-up', [
                        'level' => $newLevel,
                        'title' => $newTitle
                    ]);
                }
            });

            // Close modal first before any other dispatches to ensure it closes
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
            
            // Now that the modal is closing, dispatch other events
            session()->flash('redeem_success', 'Reward redeemed successfully!');
            $this->dispatch('rewardRedeemed');
            
            // Add new dispatch for confetti animation
            $this->dispatch('reward-purchased');
            
        } catch (\Exception $e) {
            $this->dispatch('redemptionError', 'An error occurred: ' . $e->getMessage());
            $this->dispatch('close-modal', name: 'reward-redeem-modal');
        }
    }

    public function render()
    {
        return view('livewire.rewards.modal.reward-redeem-modal');
    }
}
