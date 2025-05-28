<div class="mt-4 space-y-2">
    @php
        // Filter out the 'Other' option for reordering logic if it exists
        $reorderableChoices = $question->choices->where('is_other', false)->sortBy('order');
        $otherChoice = $question->choices->firstWhere('is_other', true);
        $totalReorderable = $reorderableChoices->count();
    @endphp

    @foreach ($reorderableChoices as $choice)
        <div wire:key="choice-{{ $choice->id }}" class="flex items-center space-x-2 group">
            <input type="checkbox" disabled class="form-checkbox h-5 w-5 text-gray-400"> {{-- Visual cue --}}
            <input
                type="text"
                wire:model.defer="choices.{{ $choice->id }}.choice_text"
                wire:blur="updateChoice({{ $choice->id }})"
                class="flex-grow p-1 border border-gray-300 rounded"
                placeholder="Choice text"
            />
            {{-- Delete Button (Hide when question not selected) --}}
            <button
                x-show="selectedQuestionId === {{ $question->id }}" x-cloak
                wire:click="removeItem('choice', {{ $choice->id }})"
                wire:confirm="Are you sure you want to remove this choice?"
                type="button"
                class="text-red-500 hover:text-red-700"
                aria-label="Remove choice"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            {{-- Reorder Buttons (Hide when question not selected) --}}
            <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="flex flex-col">
                <button
                    wire:click.stop="moveChoiceUp({{ $choice->id }})"
                    type="button"
                    class="px-1 py-0 text-xs rounded-t {{ $loop->first ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }}"
                    {{ $loop->first ? 'disabled' : '' }}
                    aria-label="Move choice up"
                >
                    ▲
                </button>
                <button
                    wire:click.stop="moveChoiceDown({{ $choice->id }})"
                    type="button"
                    class="px-1 py-0 text-xs rounded-b {{ $loop->iteration === $totalReorderable ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }}"
                    {{ $loop->iteration === $totalReorderable ? 'disabled' : '' }}
                    aria-label="Move choice down"
                >
                    ▼
                </button>
            </div>
        </div>
    @endforeach

    {{-- Display 'Other' option if it exists (non-reorderable) --}}
    @if($otherChoice)
        <div wire:key="choice-{{ $otherChoice->id }}" class="flex items-center space-x-2 group pl-7"> {{-- Indent slightly --}}
             <input
                type="text"
                wire:model.defer="choices.{{ $otherChoice->id }}.choice_text"
                wire:blur="updateChoice({{ $otherChoice->id }})"
                class="flex-grow p-1 border border-gray-300 rounded bg-gray-100" {{-- Slightly different bg --}}
                placeholder="Other option text"
            />
            {{-- Delete Button for 'Other' (Hide when question not selected) --}}
            <button
                x-show="selectedQuestionId === {{ $question->id }}" x-cloak
                wire:click="removeItem('choice', {{ $otherChoice->id }})"
                wire:confirm="Are you sure you want to remove the &quot;Other&quot; option?"
                type="button"
                class="text-red-500 hover:text-red-700"
                aria-label="Remove 'Other' option"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
             {{-- Placeholder for alignment (Hide when question not selected) --}}
            <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="flex flex-col w-[20px]"></div>
        </div>
    @endif

    {{-- Add Choice / Add Other Buttons (Only show when question is selected) --}}
    <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="pt-2 flex space-x-2">
        <button wire:click="addItem('choice', {{ $question->id }})" class="text-blue-500 hover:text-blue-700 text-sm">+ Add Choice</button>
        @if(!$otherChoice)
            <button wire:click="addItem('otherOption', {{ $question->id }})" class="text-blue-500 hover:text-blue-700 text-sm">+ Add "Other"</button>
        @endif
    </div>
</div>
