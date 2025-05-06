<div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="mt-4">
    {{-- Reorder, Required, Limit, and Delete Row --}}
    <div class="flex justify-between items-center">
        {{-- Left Group: Reorder, Required, Limit --}}
        <div class="flex items-center space-x-4"> {{-- Increased spacing between groups --}}

            {{-- Reorder Buttons --}}
            <div class="flex items-center space-x-1">
                <button
                    wire:click.stop="moveQuestionUp({{ $question->id }})"
                    type="button"
                    class="px-2 py-1 text-xs rounded {{ $qIndex === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-300 hover:bg-gray-400 text-gray-700 hover:text-gray-900' }}"
                    {{ $qIndex === 0 ? 'disabled' : '' }}
                    aria-label="Move question up"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                </button>
                <button
                    wire:click.stop="moveQuestionDown({{ $question->id }})"
                    type="button"
                    class="px-2 py-1 text-xs rounded {{ $qIndex === $totalQuestions - 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-300 hover:bg-gray-400 text-gray-700 hover:text-gray-900' }}"
                    {{ $qIndex === $totalQuestions - 1 ? 'disabled' : '' }}
                    aria-label="Move question down"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            {{-- Required Toggle (Moved Here) --}}
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

            {{-- Multiple Choice: Limit Answers Option (Moved Here) --}}
            @if($question->question_type === 'multiple_choice')
                <div class="flex items-center space-x-1"> {{-- Removed ml-4 --}}
                    <label class="text-sm text-gray-600 flex items-center space-x-1">
                        <span>Limit</span>
                        <input
                            type="checkbox"
                            wire:model.live="questions.{{ $question->id }}.limit_answers"
                            wire:change="updateQuestion({{ $question->id }})"
                            class="form-checkbox h-5 w-5 text-blue-600"
                        />
                    </label>
                    @if(isset($questions[$question->id]['limit_answers']) && $questions[$question->id]['limit_answers'])
                        <input
                            type="number"
                            min="1"
                            wire:model.live="questions.{{ $question->id }}.max_answers"
                            wire:change="updateQuestion({{ $question->id }})"
                            class="w-16 border rounded px-2 py-0.5 text-sm"
                            placeholder="Max"
                        />
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
                'action' => 'removeQuestion'
            ])
        </div>
    </div>
</div>
