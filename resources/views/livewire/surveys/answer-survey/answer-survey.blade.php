<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto">

        {{-- Back Button (Only in Preview Mode) --}}
        @if($isPreview)
            <div class="mb-4">
                <a href="{{ route('surveys.create', $survey->id) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded hover:bg-gray-300 transition"
                >
                    <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Back
                </a>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-8">
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            <h1 class="text-3xl font-bold mb-8">{{ $survey->title }}</h1>
            <form wire:submit.prevent="submit">
                <div x-data="{ navAction: 'submit' }">
                    @php $questionNumber = 1; @endphp
                    @foreach($survey->pages as $pageIndex => $page)
                        <div @if($pageIndex !== $currentPage) style="display:none" @endif>
                            @if($page->title)
                                <h2 class="text-2xl font-semibold mb-2">{{ $page->title }}</h2>
                            @endif
                            @if($page->subtitle)
                                <div class="text-gray-500 mb-4">{{ $page->subtitle }}</div>
                            @endif
                            <hr class="mb-6 border-gray-300">
                            @foreach($page->questions->sortBy('order') as $question)
                                <div class="mb-8">
                                    <label class="block font-medium mb-2 text-lg">
                                        {{ $questionNumber++ }}. {{ $question->question_text }}
                                        @if($question->required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    @if($question->question_type === 'multiple_choice')
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
                                                    otherSelected = otherChoiceId !== null && value[otherChoiceId] === true;
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

                                            @foreach($question->choices as $choice)
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
                                                            class="ml-2 border border-gray-300 rounded px-2 py-1 flex-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed text-sm"
                                                            :disabled="!otherSelected"
                                                        />
                                                    @endif
                                                </div>
                                            @endforeach
                                            @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
                                            @error('otherTexts.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    @elseif($question->question_type === 'radio')
                                        <div
                                            class="space-y-2"
                                            x-data="{
                                                otherSelected: {{ isset($answers[$question->id]) && $answers[$question->id] == $question->choices->firstWhere('is_other', true)?->id ? 'true' : 'false' }},
                                                otherChoiceId: {{ $question->choices->firstWhere('is_other', true)?->id ?? 'null' }}
                                            }"
                                            x-init="
                                                $watch('$wire.answers.{{ $question->id }}', value => {
                                                    otherSelected = otherChoiceId !== null && value == otherChoiceId;
                                                });
                                            "
                                        >
                                            @foreach($question->choices as $choice)
                                                <div class="flex items-center space-x-3 border border-gray-200 rounded p-3 hover:bg-gray-50 transition-colors duration-150">
                                                    <input
                                                        type="radio"
                                                        name="answers_{{ $question->id }}"
                                                        id="radio-{{ $question->id }}-{{ $choice->id }}"
                                                        wire:model="answers.{{ $question->id }}"
                                                        value="{{ $choice->id }}"
                                                        class="accent-blue-500 h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                    >
                                                    <label for="radio-{{ $question->id }}-{{ $choice->id }}" class="cursor-pointer flex-grow text-gray-700">{{ $choice->choice_text }}</label>

                                                    @if($choice->is_other)
                                                        <input
                                                            type="text"
                                                            wire:model.lazy="otherTexts.{{ $question->id }}"
                                                            placeholder="Please specify"
                                                            class="ml-2 border border-gray-300 rounded px-2 py-1 flex-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed text-sm"
                                                            :disabled="!otherSelected"
                                                        />
                                                    @endif
                                                </div>
                                            @endforeach
                                            @error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
                                            @error('otherTexts.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    @elseif($question->question_type === 'essay')
                                        <textarea 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                            rows="4"
                                        ></textarea>
                                    @elseif($question->question_type === 'short_text')
                                        <input 
                                            type="text" 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        >
                                    @elseif($question->question_type === 'date')
                                        <input 
                                            type="date" 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        >
                                    @elseif($question->question_type === 'rating')
                                        @php
                                            $starCount = $question->stars ?? 5;
                                        @endphp
                                        <div 
                                            x-data="{
                                                hover: 0,
                                                selected: @entangle('answers.' . $question->id),
                                                init() {
                                                    this.$watch('selected', value => this.selected = value);
                                                }
                                            }" 
                                            class="flex items-center space-x-1 mt-2"
                                        >
                                            @for ($i = 1; $i <= $starCount; $i++)
                                                <label class="cursor-pointer">
                                                    <input
                                                        type="radio"
                                                        wire:model="answers.{{ $question->id }}"
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
                                    @elseif($question->question_type === 'likert')
                                        @php
                                            $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
                                            $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
                                        @endphp
                                        <div class="overflow-x-auto mt-2">
                                            <table class="min-w-full text-center">
                                                <thead>
                                                    <tr>
                                                        <th class="bg-white w-52"></th>
                                                        @foreach($likertColumns as $colIndex => $column)
                                                            <th class="bg-white px-4 py-2 text-base font-medium">{{ $column }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($likertRows as $rowIndex => $row)
                                                        @php $rowBg = $loop->even ? 'bg-gray-50' : 'bg-white'; @endphp
                                                        <tr class="{{ $rowBg }}">
                                                            <td class="px-4 py-2 text-left text-base">{{ $row }}</td>
                                                            @foreach($likertColumns as $colIndex => $column)
                                                                <td class="px-4 py-2">
                                                                    <input
                                                                        type="radio"
                                                                        name="answers[{{ $question->id }}][{{ $rowIndex }}]"
                                                                        wire:model="answers.{{ $question->id }}.{{ $rowIndex }}"
                                                                        value="{{ $colIndex }}"
                                                                        class="accent-blue-500"
                                                                        style="width: 1.5em; height: 1.5em;"
                                                                    >
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        {{-- Add the error display specifically for the Likert block --}}
                                        @error('answers.' . $question->id)
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                        {{-- Display errors for individual rows if needed (though the general message might suffice) --}}
                                        @foreach($likertRows as $rowIndex => $row)
                                            @error('answers.' . $question->id . '.' . $rowIndex)
                                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                            @enderror
                                        @endforeach
                                    @else
                                        <input 
                                            type="text" 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        >
                                    @endif
                                    
                                    {{-- Remove Likert from this general error block check --}}
                                    @if(!in_array($question->question_type, ['multiple_choice', 'likert']))
                                        @error('answers.' . $question->id)
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            @endforeach

                            <div class="flex justify-between mt-8">
                                @if ($loop->first)
                                    <span></span>
                                @else
                                    <button
                                        type="button"
                                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                                        wire:click="$set('currentPage', {{ $pageIndex - 1 }})"
                                    >Previous</button>
                                @endif

                                @if ($loop->last)
                                    <button
                                        type="submit"
                                        class="cursor-pointer px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition ml-auto"
                                        wire:click="$set('navAction', 'submit')"
                                    >Submit</button>
                                @else
                                    <button
                                        type="submit"
                                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition ml-auto"
                                        wire:click="$set('navAction', 'next')"
                                    >Next</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</div>
