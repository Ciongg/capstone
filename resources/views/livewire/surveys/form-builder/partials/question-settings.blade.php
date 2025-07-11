<div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="mt-4">
    {{-- Reorder, Required, Limit, and Delete Row --}}
    <div class="flex flex-wrap gap-y-3 justify-between items-center">
        {{-- Left Group: Reorder, Required, Limit --}}
        <div class="flex flex-wrap items-center gap-3 sm:space-x-4"> {{-- Using gap for better stacking on mobile --}}

            {{-- Reorder Buttons --}}
            <div class="flex items-center space-x-1">
                <button
                    wire:click.stop="moveQuestionUp({{ $question->id }})"
                    type="button"
                    class="px-2 py-1 text-xs rounded {{ $qIndex === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-300 hover:bg-gray-400 text-gray-700 hover:text-gray-900' }} flex items-center justify-center"
                    {{ $qIndex === 0 ? 'disabled' : '' }}
                    aria-label="Move question up"
                    wire:loading.attr="disabled"
                    wire:target="moveQuestionUp({{ $question->id }})"
                >
                    <span wire:loading.remove wire:target="moveQuestionUp({{ $question->id }})">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="moveQuestionUp({{ $question->id }})">
                         <svg class="animate-spin h-4 w-4 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </button>
                <button
                    wire:click.stop="moveQuestionDown({{ $question->id }})"
                    type="button"
                    class="px-2 py-1 text-xs rounded {{ $qIndex === $totalQuestions - 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-300 hover:bg-gray-400 text-gray-700 hover:text-gray-900' }} flex items-center justify-center"
                    {{ $qIndex === $totalQuestions - 1 ? 'disabled' : '' }}
                    aria-label="Move question down"
                    wire:loading.attr="disabled"
                    wire:target="moveQuestionDown({{ $question->id }})"
                >
                    <span wire:loading.remove wire:target="moveQuestionDown({{ $question->id }})">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="moveQuestionDown({{ $question->id }})">
                         <svg class="animate-spin h-4 w-4 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </button>
            </div>

            {{-- Required Toggle --}}
            <div class="flex items-center space-x-1">
                <label class="text-sm text-gray-600">Required</label>
                <input
                    type="checkbox"
                    wire:model="questions.{{ $question->id }}.required"
                    wire:change="updateQuestion({{ $question->id }})"
                    class="form-checkbox h-5 w-5 text-blue-600"
                    @if(isset($questions[$question->id]) && $questions[$question->id]['required']) checked @endif
                />
            </div>

            {{-- Multiple Choice: Limit Condition Dropdown --}}
            @if($question->question_type === 'multiple_choice')
                <div class="flex items-center flex-wrap gap-1">
                    <label for="limit-condition-{{ $question->id }}" class="text-sm text-gray-600">Limit:</label>
                    <select
                        id="limit-condition-{{ $question->id }}"
                        wire:model="questions.{{ $question->id }}.limit_condition"
                        wire:change="updateQuestion({{ $question->id }})"
                        class="border rounded px-2 py-0.5 text-sm"
                    >
                        <option value="">No Limit</option>
                        <option value="at_most">At Most</option>
                        <option value="equal_to">Equal To</option>
                    </select>

                    {{-- Conditionally show Max Answers input --}}
                    @if(isset($questions[$question->id]['limit_condition']) && in_array($questions[$question->id]['limit_condition'], ['at_most', 'equal_to']))
                        <input
                            type="number"
                            min="1"
                            wire:model="questions.{{ $question->id }}.max_answers"
                            wire:change="updateQuestion({{ $question->id }})"
                            class="w-16 border rounded px-2 py-0.5 text-sm ml-1"
                            placeholder="Num"
                            required {{-- Make required if condition is set --}}
                        />
                        @error('questions.' . $question->id . '.max_answers') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Group: Delete Button --}}
        <div class="flex items-center">
            @include('livewire.surveys.form-builder.partials.delete-button', [
                'context' => 'question',
                'id' => $question->id,
                'confirmMessage' => 'Are you sure you want to remove this question?',
                'action' => 'removeItem',
                'type' => 'question'
            ])
        </div>
    </div>
</div>
