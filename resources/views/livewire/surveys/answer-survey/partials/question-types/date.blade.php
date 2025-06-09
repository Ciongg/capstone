<div x-data="{
    hasValue: {{ isset($answers[$question->id]) && $answers[$question->id] ? 'true' : 'false' }},
    
    init() {
        this.$watch('$wire.answers.{{ $question->id }}', value => {
            this.hasValue = value !== null && value !== '';
        });
    },
    
    clearDate() {
        // Clear the Livewire model
        $wire.set('answers.{{ $question->id }}', null);
        
        // Also directly clear the input field value in the DOM
        this.$refs.dateInput.value = '';
        
        // Update our state
        this.hasValue = false;
    }
}">
    <input 
        type="date" 
        x-ref="dateInput"
        wire:model.live="answers.{{ $question->id }}"
        class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm"
        x-on:change="hasValue = $event.target.value !== '' "
    >
    
    {{-- Clear Selection button - only shows when a date is selected --}}
    <div x-show="hasValue" class="mt-2">
        <button type="button" 
                x-on:click="clearDate()"
                class="text-blue-600 text-sm hover:underline">
            Clear response
        </button>
    </div>
    
    @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
</div>