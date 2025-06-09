@php
    $starCount = $question->stars ?? 5;
@endphp
<div 
    x-data="{
        hover: 0,
        selected: @entangle('answers.' . $question->id),
        
        init() {
            this.$watch('selected', value => this.selected = value);
        },
        
        clearRating() {
            this.selected = null;
            $wire.set('answers.{{ $question->id }}', null);
        }
    }" 
    class="space-y-2"
>
    <div class="flex items-center space-x-1">
        @for ($i = 1; $i <= $starCount; $i++)
            <label class="cursor-pointer">
                <input
                    type="radio"
                    wire:model.live="answers.{{ $question->id }}"
                    value="{{ $i }}"
                    class="hidden"
                    @click="selected = {{ $i }}"
                >
                <svg
                    @mouseover="hover = {{ $i }}"
                    @mouseleave="hover = 0"
                    :class="(hover >= {{ $i }} || (!hover && selected >= {{ $i }})) ? 'text-yellow-400' : 'text-gray-300'"
                    class="w-8 h-8 transition cursor-pointer"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                </svg>
            </label>
        @endfor
        <span class="ml-2 text-gray-500" x-text="selected ? selected : ''"></span>
    </div>
    
    {{-- Clear Selection button - only shows when stars are selected --}}
    <div x-show="selected !== null && selected !== undefined" class="mt-1">
        <button type="button" 
                @click="clearRating()"
                class="text-blue-600 text-sm hover:underline">
            Clear response
        </button>
    </div>
    
    @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
</div>