<div class="space-y-2">
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
    @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
    @error('otherTexts.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
</div>
