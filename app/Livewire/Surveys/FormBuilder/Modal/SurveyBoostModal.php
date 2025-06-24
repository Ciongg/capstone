<?php

namespace App\Livewire\Surveys\FormBuilder\Modal;

use Livewire\Component;
use App\Models\Survey;
use App\Models\UserSystemReward;
use Illuminate\Support\Facades\Auth;

class SurveyBoostModal extends Component
{
    public Survey $survey;
    public $boostQuantity = 1;
    public $availableBoosts = 0;
    public $currentSurveyBoosts = 0;

    public function mount(Survey $survey)
    {
        $this->survey = $survey;
        $this->loadBoostData();
    }

    public function loadBoostData()
    {
        // Get available survey boosts for the current user
        $this->availableBoosts = UserSystemReward::where('user_id', Auth::id())
            ->where('type', 'survey_boost')
            ->where('status', 'unused')
            ->sum('quantity');

        // Get current boosts applied to this survey
        $this->currentSurveyBoosts = $this->survey->boost_count ?? 0;
    }

    public function rules()
    {
        $maxBoosts = 4 - ($this->survey->boost_count ?? 0);
        
        return [
            'boostQuantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . min($this->availableBoosts, $maxBoosts)
            ]
        ];
    }

    public function messages()
    {
        $maxBoosts = 4 - ($this->survey->boost_count ?? 0);
        $maxAllowed = min($this->availableBoosts, $maxBoosts);
        
        return [
            'boostQuantity.max' => $maxBoosts <= 0 
                ? 'This survey has already reached the maximum of 4 boosts.'
                : "You can only apply {$maxAllowed} more boost(s) to this survey.",
            'boostQuantity.min' => 'You must allocate at least 1 survey boost.',
            'boostQuantity.required' => 'Please enter the number of boosts to allocate.',
        ];
    }

    public function allocateBoosts()
    {
        // Check if survey has reached max boosts
        if (($this->survey->boost_count ?? 0) >= 4) {
            $this->addError('boostQuantity', 'This survey has already reached the maximum of 4 boosts.');
            return;
        }
        
        $this->validate();

        try {
            // Find and update the user's survey boosts
            $reward = UserSystemReward::where('user_id', Auth::id())
                ->where('type', 'survey_boost')
                ->where('status', 'unused')
                ->first();

            if (!$reward || $reward->quantity < $this->boostQuantity) {
                $this->addError('boostQuantity', 'Insufficient survey boosts available.');
                return;
            }

            // Deduct the boosts from user's rewards
            $reward->quantity -= $this->boostQuantity;
            if ($reward->quantity <= 0) {
                $reward->status = 'used';
            }
            $reward->save();

            // Apply boosts to survey: +5 points per boost
            $pointsToAdd = $this->boostQuantity * 5;
            $this->survey->points_allocated = ($this->survey->points_allocated ?? 0) + $pointsToAdd;
            $this->survey->boost_count = ($this->survey->boost_count ?? 0) + $this->boostQuantity;
            $this->survey->save();

            $this->loadBoostData(); // Refresh data
            $this->boostQuantity = 1; // Reset quantity

            $this->dispatch('close-modal', ['name' => 'survey-boost-modal-' . $this->survey->id]);
            $this->dispatch('boost-allocated', [
                'message' => "Successfully allocated {$this->boostQuantity} boost(s) (+{$pointsToAdd} points) to your survey!"
            ]);

            // Refresh the parent component and survey settings modal
            $this->dispatch('refresh-survey-data');
           

        } catch (\Exception $e) {
            $this->addError('boostQuantity', 'Failed to allocate boosts. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.modal.survey-boost-modal');
    }
}
