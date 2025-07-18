<div
    {{-- Add relative positioning --}}
    class="mb-4 p-2 sm:p-4 rounded-lg transition hover:shadow-md cursor-pointer relative"
    {{-- Use local Alpine variables --}}
    :class="{ 'border-2 border-blue-500': activePageId === {{ $page->id }} && selectedQuestionId === null }"
>
    {{-- Page Selection Overlay --}}
    <div
        {{-- Use local Alpine variables --}}
        x-show="activePageId !== {{ $page->id }} || selectedQuestionId !== null" {{-- Show if page not active OR a question is selected --}}
        {{-- Update Alpine state directly AND call Livewire --}}
        x-on:click="
            selectedQuestionId = null;
            activePageId = {{ $page->id }};
            $wire.setActivePage({{ $page->id }});
        "
        class="absolute inset-0 bg-transparent hover:bg-blue-500/5 z-10 rounded-lg transition-all duration-200 cursor-pointer"
    >
    </div>

    {{-- Page Title Textarea --}}
    <textarea
        id="page-title-{{ $page->id }}"
        wire:blur="updatePage({{ $page->id }}, 'title', $event.target.value)"
        placeholder="Enter page title"
        class="w-full text-xl sm:text-2xl font-bold p-2 border border-gray-300 rounded mb-2 resize-none overflow-hidden"
        rows="1"
        style="field-sizing: content; min-height: 3em; max-height: 100em;"
        @if(isset($survey) && $survey->is_locked) readonly @endif
    >{{ $page->title }}</textarea>

    {{-- Page Subtitle Textarea --}}
    <textarea
        id="page-subtitle-{{ $page->id }}"
        wire:blur="updatePage({{ $page->id }}, 'subtitle', $event.target.value)"
        placeholder="Enter page subtitle"
        class="w-full text-base sm:text-lg text-gray-600 p-2 border border-gray-300 rounded resize-none overflow-hidden"
        rows="1"
        style="field-sizing: content; min-height: 3em; max-height: 100em;"
        @if(isset($survey) && $survey->is_locked) readonly @endif
    >{{ $page->subtitle }}</textarea>
        
    {{-- Container for Page Actions (Delete Button & Add Question Picker) --}}
    {{-- Use local Alpine variables --}}
    <div x-show="activePageId === {{ $page->id }} && selectedQuestionId === null" x-cloak class="mt-4 space-y-4">
        {{-- Question Type Picker (reusable component) --}}
        @include('livewire.surveys.form-builder.partials.type-picker', [
            'context' => 'page',
            'id' => $page->id,
            'order' => null,
            'questionTypes' => $questionTypes
        ])

        {{-- Delete Button (reusable component) --}}
        <div class="flex justify-end">
            @include('livewire.surveys.form-builder.partials.delete-button', [
                'context' => 'page',
                'id' => $page->id,
                'confirmMessage' => 'Are you sure you want to delete this page and all its questions?',
                'action' => 'removeItem',
                'type' => 'page'  // Add this line to specify the type
            ])
        </div>
    </div> {{-- End Page Actions Container --}}
</div>
