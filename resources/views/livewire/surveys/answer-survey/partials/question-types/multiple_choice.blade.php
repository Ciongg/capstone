<div
    class="space-y-2"
    x-data="{
        count: 0,
        limit: {{ in_array($question->limit_condition, ['at_most', 'equal_to']) && $question->max_answers > 0 ? $question->max_answers : 0 }},
        limitCondition: '{{ $question->limit_condition }}',
        answersData: @entangle('answers.' . $question->id),
        otherSelected: false,
        otherChoiceId: {{ $question->choices->firstWhere('is_other', true)?->id ?? 'null' }},
        showLimitWarning: false
    }"
    x-init="
        count = Object.values(answersData).filter(Boolean).length;
        otherSelected = otherChoiceId !== null && answersData[otherChoiceId] === true;
        
        $watch('answersData', value => {
            const newCount = Object.values(value).filter(Boolean).length;
            showLimitWarning = limit > 0 && newCount > limit;
            count = newCount;
            
            // Track if other option changed
            const wasSelected = otherSelected;
            otherSelected = otherChoiceId !== null && value[otherChoiceId] === true;
            
            // If other was deselected, clear the text
            if (wasSelected && !otherSelected) {
                $wire.set('otherTexts.{{ $question->id }}', '');
            }
        });
    "
>
    @if($question->limit_condition === 'at_most' && $question->max_answers)
        <p class="text-sm text-gray-500 mb-2">Please select up to {{ $question->max_answers }} options. (<span x-text="count"></span>/{{ $question->max_answers }} selected)</p>
    @elseif($question->limit_condition === 'equal_to' && $question->max_answers)
        <p class="text-sm text-gray-500 mb-2">Please select exactly {{ $question->max_answers }} options. (<span x-text="count"></span>/{{ $question->max_answers }} selected)</p>
    @endif

    <div x-show="showLimitWarning" x-cloak class="text-red-500 text-sm mb-2">
        You've selected too many options. Please unselect some choices first.
    </div>

    @php
        // Sort choices: regular choices first (ordered by 'order'), then the "Other" option
        $sortedChoices = $question->choices->sortBy([
            ['is_other', 'asc'],
            ['order', 'asc']
        ]);
    @endphp
    
    @foreach($sortedChoices as $choice)
        <div class="flex items-center space-x-3 border border-gray-200 rounded p-3 hover:bg-gray-50 transition-colors duration-150"
             :class="{ 'opacity-50 cursor-not-allowed': limit > 0 && count >= limit && !document.getElementById('checkbox-{{ $question->id }}-{{ $choice->id }}').checked }">
            <input
                type="checkbox"
                id="checkbox-{{ $question->id }}-{{ $choice->id }}"
                x-model="answersData[{{ $choice->id }}]"
                class="accent-blue-500 h-5 w-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300"
                wire:key="checkbox-{{ $question->id }}-{{ $choice->id }}"
                :disabled="limit > 0 && count >= limit && !answersData[{{ $choice->id }}]"
            >
            <label for="checkbox-{{ $question->id }}-{{ $choice->id }}" class="cursor-pointer flex-grow text-gray-700">{{ $choice->choice_text }}</label>

            @if($choice->is_other)
                <input
                    type="text"
                    wire:model.lazy="otherTexts.{{ $question->id }}"
                    placeholder="Please specify"
                    class="ml-2 border border-gray-300 rounded px-2 py-1 flex-1 focus:ring-blue-500 focus:border-blue-500 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :disabled="!otherSelected"
                />
            @endif
        </div>
    @endforeach
    @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
    @error('otherTexts.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
</div>
