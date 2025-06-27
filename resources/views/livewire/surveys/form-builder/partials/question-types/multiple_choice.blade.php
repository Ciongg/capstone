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
                class="text-red-500 hover:text-red-700 flex items-center"
                aria-label="Remove choice"
                wire:loading.attr="disabled"
                wire:target="removeItem('choice', {{ $choice->id }})"
            >
                <span wire:loading.remove wire:target="removeItem('choice', {{ $choice->id }})">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
                <span wire:loading wire:target="removeItem('choice', {{ $choice->id }})">
                    <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
            {{-- Reorder Buttons (Hide when question not selected) --}}
            <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="flex flex-col">
                <button
                    wire:click.stop="moveChoiceUp({{ $choice->id }})"
                    type="button"
                    class="px-1 py-0 text-xs rounded-t {{ $loop->first ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }} flex items-center justify-center"
                    {{ $loop->first ? 'disabled' : '' }}
                    aria-label="Move choice up"
                    wire:loading.attr="disabled"
                    wire:target="moveChoiceUp({{ $choice->id }})"
                >
                    <span wire:loading.remove wire:target="moveChoiceUp({{ $choice->id }})">▲</span>
                    <span wire:loading wire:target="moveChoiceUp({{ $choice->id }})">
                        <svg class="animate-spin h-3 w-3 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </button>
                <button
                    wire:click.stop="moveChoiceDown({{ $choice->id }})"
                    type="button"
                    class="px-1 py-0 text-xs rounded-b {{ $loop->iteration === $totalReorderable ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }} flex items-center justify-center"
                    {{ $loop->iteration === $totalReorderable ? 'disabled' : '' }}
                    aria-label="Move choice down"
                    wire:loading.attr="disabled"
                    wire:target="moveChoiceDown({{ $choice->id }})"
                >
                    <span wire:loading.remove wire:target="moveChoiceDown({{ $choice->id }})">▼</span>
                    <span wire:loading wire:target="moveChoiceDown({{ $choice->id }})">
                       <svg class="animate-spin h-3 w-3 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
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
                class="text-red-500 hover:text-red-700 flex items-center"
                aria-label="Remove 'Other' option"
                wire:loading.attr="disabled"
                wire:target="removeItem('choice', {{ $otherChoice->id }})"
            >
                <span wire:loading.remove wire:target="removeItem('choice', {{ $otherChoice->id }})">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
                <span wire:loading wire:target="removeItem('choice', {{ $otherChoice->id }})">
                    <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
             {{-- Placeholder for alignment (Hide when question not selected) --}}
            <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="flex flex-col w-[20px]"></div>
        </div>
    @endif

    {{-- Add Choice / Add Other Buttons (Only show when question is selected) --}}
    <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="pt-2 flex space-x-2">
        <button 
            wire:click="addItem('choice', {{ $question->id }})" 
            class="text-blue-500 hover:text-blue-700 text-sm flex items-center space-x-1"
            wire:loading.attr="disabled"
            wire:target="addItem('choice', {{ $question->id }})"
        >
            <span wire:loading.remove wire:target="addItem('choice', {{ $question->id }})">+ Add Choice</span>
            <span wire:loading wire:target="addItem('choice', {{ $question->id }})" class="flex items-center space-x-1">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        </button>
        @if(!$otherChoice)
            <button 
                wire:click="addItem('otherOption', {{ $question->id }})" 
                class="text-blue-500 hover:text-blue-700 text-sm flex items-center space-x-1"
                wire:loading.attr="disabled"
                wire:target="addItem('otherOption', {{ $question->id }})"
            >
                <span wire:loading.remove wire:target="addItem('otherOption', {{ $question->id }})">+ Add "Other"</span>
                <span wire:loading wire:target="addItem('otherOption', {{ $question->id }})" class="flex items-center space-x-1">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        @endif
    </div>
</div>
