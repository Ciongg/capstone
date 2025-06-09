<div class="space-y-2"
    x-data="{
        init() {
            // Immediate check on page load
            this.$nextTick(() => {
                this.checkSelection();
            });
            
            // Listen for changes to the answer
            $wire.$watch('answers.{{ $question->id }}', (newValue) => {
                this.hasSelection = newValue !== null && newValue !== undefined;
            });
        },
        
        hasSelection: false,
        
        // A more direct check for selection state
        checkSelection() {
            const answer = $wire.answers && $wire.answers[{{ $question->id }}];
            this.hasSelection = answer !== null && answer !== undefined;
            
            // Double-check with DOM state as well
            const checkedRadio = document.querySelector(`input[name='answers_{{ $question->id }}']:checked`);
            if ((checkedRadio && !this.hasSelection) || (!checkedRadio && this.hasSelection)) {
                // If DOM and data state are out of sync, trust the DOM
                this.hasSelection = checkedRadio !== null;
            }
        },
        
        clearSelection() {
            // Find all radio buttons for this question
            document.querySelectorAll(`input[name='answers_{{ $question->id }}']`).forEach(radio => {
                radio.checked = false;
                
            });
            
            // Clear the Livewire model
            $wire.set('answers.{{ $question->id }}', null);
            $wire.set('otherTexts.{{ $question->id }}', null);
            
            // Force our local state to update
            this.hasSelection = false;
        }
    }"
>
    @php
        // Sort choices: regular choices first (ordered by 'order'), then the "Other" option
        $sortedChoices = $question->choices->sortBy([
            ['is_other', 'asc'],
            ['order', 'asc']
        ]);
        
        // Get the "Other" choice if one exists
        $otherChoice = $question->choices->firstWhere('is_other', true);
        $otherChoiceId = $otherChoice ? $otherChoice->id : null;
        
        // Check if "Other" is currently selected
        $isOtherSelected = isset($answers[$question->id]) && $answers[$question->id] == $otherChoiceId;
    @endphp
    
    @foreach($sortedChoices as $choice)
        <div class="flex items-center space-x-3 border border-gray-200 rounded p-3 hover:bg-gray-50 transition-colors duration-150">
            <input
                type="radio"
                name="answers_{{ $question->id }}"
                id="radio-{{ $question->id }}-{{ $choice->id }}"
                wire:model.live="answers.{{ $question->id }}"
                value="{{ $choice->id }}"
                class="accent-blue-500 h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300"
                @change="hasSelection = true; checkSelection();"
                @if($choice->is_other)
                    wire:change="$set('answers.{{ $question->id }}', {{ $choice->id }})"
                @endif
            >
            <label for="radio-{{ $question->id }}-{{ $choice->id }}" class="cursor-pointer flex-grow text-gray-700">{{ $choice->choice_text }}</label>

            @if($choice->is_other)
                <input
                    type="text"
                    wire:model.live="otherTexts.{{ $question->id }}"
                    placeholder="Please specify"
                    class="ml-2 border border-gray-300 rounded px-2 py-1 flex-1 focus:ring-blue-500 focus:border-blue-500 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                    @if(!$isOtherSelected) disabled @endif
                    wire:click="$set('answers.{{ $question->id }}', {{ $choice->id }})"
                >
            @endif
        </div>
    @endforeach

    {{-- Clear Selection button - only shows when something is actually selected --}}
    <div x-show="hasSelection" class="mt-2">
        <button type="button" 
                @click="clearSelection()"
                class="text-blue-600 text-sm hover:underline">
            Clear response
        </button>
    </div>
    
    @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
    @error('otherTexts.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
</div>