<div class="mt-4 space-y-2">
    @php $hasOtherOption = $question->choices->contains('is_other', true); @endphp

    @foreach($question->choices->sortBy('order') as $choice)
        <div class="flex items-center space-x-2" wire:key="choice-{{ $choice->id }}">
            <span class="inline-block w-4 h-4 rounded border-2 border-gray-400 mr-2 self-start mt-2"></span> {{-- Align icon --}}
            {{-- If it's the 'Other' option, display text, otherwise show textarea --}}
            @if($choice->is_other)
                <span class="flex-1 p-2 text-gray-500 italic">Other</span>
            @else
                <textarea
                    id="choice-{{ $choice->id }}"
                    x-data="{
                        init() {
                            // Set initial height on initialization
                            $nextTick(() => this.adjustHeight());
                        },
                        adjustHeight() {
                            const id = $el.id;
                            $el.style.height = 'auto';
                            const newHeight = `${$el.scrollHeight}px`;
                            $el.style.height = newHeight;
                            // Store height in Alpine store
                            Alpine.store('textareaHeights').set(id, newHeight);
                        }
                    }"
                    @input="adjustHeight()"
                    wire:model.defer="choices.{{ $choice->id }}.choice_text"
                    wire:blur="updateChoice({{ $choice->id }})"
                    placeholder="Choice text"
                    onfocus="this.select()"
                    class="flex-1 p-2 border border-gray-300 rounded resize-none overflow-hidden"
                    rows="1"
                    data-autoresize
                    :style="{ height: $store.textareaHeights.get('choice-{{ $choice->id }}') }"
                ></textarea>
            @endif
            {{-- Show remove button only when question is selected --}}
            <span x-show="selectedQuestionId === {{ $question->id }}" class="self-start mt-1"> {{-- Align button --}}
                <button
                    wire:click.stop="removeChoice({{ $choice->id }})"
                    class="text-red-500 hover:text-red-700 ml-2"
                    title="Remove Choice"
                    type="button"
                >&#10005;</button>
            </span>
        </div>
    @endforeach

    {{-- Buttons container --}}
    <template x-if="selectedQuestionId === {{ $question->id }}">
        <div class="mt-4 flex space-x-4"> {{-- Increased spacing slightly --}}
            {{-- Add Choice Button --}}
            <button
                wire:click.stop="addChoice({{ $question->id }})"
                {{-- Remove bg, change text color --}}
                class="px-3 py-1 text-blue-500 hover:text-blue-700 rounded flex items-center"
                type="button"
            >
                <span class="mr-1 text-lg font-bold">+</span> Add Choice
            </button>

            {{-- Add Other Option Button (conditional) --}}
            @if(!$hasOtherOption)
                <button
                    wire:click.stop="addOtherOption({{ $question->id }})"
                    {{-- Remove bg, change text color --}}
                    class="px-3 py-1 text-blue-500 hover:text-blue-700 rounded flex items-center"
                    type="button"
                >
                    <span class="mr-1 text-lg font-bold">+</span> Add Other
                </button>
            @endif
        </div>
    </template>
</div>
