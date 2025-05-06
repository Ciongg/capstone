<div x-show="selectedQuestionId === {{ $question->id }}" x-cloak>
    <div class="flex items-center space-x-2 mt-2">
        <label class="text-sm text-gray-600">Required</label>
        <input
            type="checkbox"
            wire:model="questions.{{ $question->id }}.required"
            wire:change="updateQuestion({{ $question->id }})"
            class="form-checkbox h-5 w-5 text-blue-600"
            @if(isset($questions[$question->id]) && $questions[$question->id]['required']) checked @endif
        />

        {{-- Multiple Choice: Limit Answers Option --}}
        @if($question->question_type === 'multiple_choice')
            <label class="ml-4 text-sm text-gray-600 flex items-center space-x-1">
                <span>Limit answers</span>
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
                    class="ml-2 w-20 border rounded px-2 py-1"
                    placeholder="Max"
                />
            @endif
        @endif
    </div>
</div>
