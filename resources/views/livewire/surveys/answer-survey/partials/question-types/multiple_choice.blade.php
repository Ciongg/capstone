<div class="space-y-2">
    {{-- Show limit instructions if applicable without the count display --}}
    @if($question->limit_condition === 'at_most' && $question->max_answers)
        <p class="text-sm text-gray-500 mb-2">
            Please select up to {{ $question->max_answers }} options.
        </p>
    @elseif($question->limit_condition === 'equal_to' && $question->max_answers)
        <p class="text-sm text-gray-500 mb-2">
            Please select exactly {{ $question->max_answers }} options.
        </p>
    @endif

    {{-- Main Alpine component with simplified data model --}}
    <div 
        x-data="{
            selectedCount: 0,
            maxAllowed: {{ $question->max_answers > 0 ? $question->max_answers : 9999 }},
            limitReached: false,
            hasLimit: {{ $question->limit_condition && $question->max_answers > 0 ? 'true' : 'false' }},
            questionId: {{ $question->id }},
            
            init() {
                this.countSelected();
                
                // Initial check for disabled state
                this.$nextTick(() => {
                    this.countSelected();
                });
                
                this.$watch('selectedCount', () => {
                    this.limitReached = this.hasLimit && this.selectedCount >= this.maxAllowed;
                });
            },
            
            countSelected() {
                // Count selected checkboxes
                this.selectedCount = Object.values($wire.answers[this.questionId] || {}).filter(val => val === true).length;
            },
            
            isDisabled(choiceId) {
                // Disable checkbox if limit reached and this one isn't already checked
                return this.limitReached && !$wire.answers[this.questionId][choiceId];
            },
            
            isOtherSelected(choiceId) {
                return $wire.answers[this.questionId] && $wire.answers[this.questionId][choiceId] === true;
            },
            
            clearAllResponses() {
                // Get all checkboxes for this question
                const checkboxes = document.querySelectorAll(`[id^='checkbox-${this.questionId}-']`);
                
                // Uncheck all checkboxes
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Update Livewire model
                if ($wire.answers[this.questionId]) {
                    Object.keys($wire.answers[this.questionId]).forEach(choiceId => {
                        $wire.set(`answers.${this.questionId}.${choiceId}`, false);
                    });
                }
                
          
                $wire.set(`otherTexts.${this.questionId}`, null);
                
                // Reset counter
                this.selectedCount = 0;
                this.limitReached = false;
            }
        }"
        @change="countSelected()"
    >
        {{-- Warning message when too many options selected --}}
        <div x-show="selectedCount > maxAllowed" x-cloak class="text-red-500 text-sm mb-2">
            You've selected too many options. Please unselect some choices first.
        </div>
        
        {{-- List of choices --}}
        @foreach($question->choices->sortBy(['is_other', 'order']) as $choice)
            <div class="border border-gray-200 rounded p-3 hover:bg-gray-50 transition-colors duration-150"
                 :class="{'opacity-50': isDisabled({{ $choice->id }})}">
                <div class="flex items-center space-x-3">
                    {{-- Checkbox input --}}
                    <input
                        type="checkbox"
                        id="checkbox-{{ $question->id }}-{{ $choice->id }}"
                        wire:model.live="answers.{{ $question->id }}.{{ $choice->id }}"
                        class="accent-blue-500 h-5 w-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300"
                        :disabled="isDisabled({{ $choice->id }})"
                        @change="$nextTick(() => countSelected())"
                    >
                    
                    {{-- Choice label --}}
                    <label for="checkbox-{{ $question->id }}-{{ $choice->id }}" 
                          class="cursor-pointer flex-grow text-gray-700">
                        {{ $choice->choice_text }}
                    </label>

                    {{-- "Other" text input if applicable --}}
                    @if($choice->is_other)
                        <input
                            type="text"
                            wire:model="otherTexts.{{ $question->id }}"
                            placeholder="Please specify"
                            class="ml-2 border border-gray-300 rounded px-2 py-1 flex-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            :class="{'bg-gray-100': !isOtherSelected({{ $choice->id }})}"
                            x-bind:disabled="!isOtherSelected({{ $choice->id }})"
                            @click.stop
                        >
                    @endif
                </div>
            </div>
        @endforeach
        
        {{-- Clear All Responses button - Matches likert style --}}
        <div x-show="selectedCount > 0" class="mt-2">
            <button type="button" 
                    @click="clearAllResponses()"
                    class="text-blue-600 text-sm hover:underline">
                Clear all responses
            </button>
        </div>
        
        {{-- Error messages --}}
        @error('answers.' . $question->id) 
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div> 
        @enderror
        @error('otherTexts.' . $question->id) 
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div> 
        @enderror
    </div>
</div>
