@php
    // Generate unique variable name based on context and id
    $showTypePickerVar = 'showTypePicker_' . $context . '_' . $id;
    
    // Alpine key based on context
    $alpineKey = $context === 'page' 
        ? "'type-picker-page-' + activePageId + '-' + selectedQuestionId" 
        : "'type-picker-question-' + selectedQuestionId";
        
    // Wire:click action based on context and order
    $wireClickAction = $context === 'page' 
        ? "addItem('question', activePageId, {'question_type': 'TYPE'})" 
        : "addItem('question', activePageId, {'question_type': 'TYPE', 'order': $order})";
    
    $pageClickAction = "addItem('page', null, {})";

    // Icon mapping (using SVG paths - adjust size/stroke in class)
    $icons = [
        'page' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
        'multiple_choice' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>', // Check circle
        'essay' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>', // Bars 3
        'radio' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>', // List bullet
        'date' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" /></svg>', // Calendar days
        'rating' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>', // Star
        'short_text' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-4.5 3h9" /></svg>', // Bars 3 bottom left (alternative)
        'likert' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>', // Squares 2x2
    ];
@endphp

<div
    x-data="{
        {{ $showTypePickerVar }}: false,
        adding: false,
        async add(type) {
            this.adding = true;
            await $wire.addItem(
                type === 'page' ? 'page' : 'question',
                type === 'page' ? null : activePageId,
                type === 'page' ? {} : { question_type: type }
            );
            this.adding = false;
            this.{{ $showTypePickerVar }} = false;
        }
    }"
    :key="{{ $alpineKey }}"
    @click.away="{{ $showTypePickerVar }} = false"
>
    {{-- "Add Question Below" Button --}}
    <button
        x-show="!{{ $showTypePickerVar }}"
        @click="{{ $showTypePickerVar }} = true"
        class="w-full px-3 sm:px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 border border-dashed border-gray-400 flex items-center justify-center text-sm sm:text-base"
        type="button"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Question Below
    </button>

    {{-- Picker Grid --}}
    <div
        x-show="{{ $showTypePickerVar }}"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-1 sm:gap-2 mt-2 border p-2 rounded bg-white shadow overflow-x-auto"
        x-cloak
    >
        {{-- Loading overlay --}}
        <div
            x-show="adding"
            class="absolute inset-0 bg-white/80 flex items-center justify-center z-20"
            style="backdrop-filter: blur(2px);"
        >
            <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-3 text-blue-700 font-semibold text-base">Adding...</span>
        </div>
        @foreach ($questionTypes as $type)
            <button
                @click="add('{{ $type }}')"
                :disabled="adding"
                class="flex flex-col items-center justify-center p-1 sm:p-2 rounded text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition duration-150 ease-in-out min-w-[70px]"
                type="button"
                title="{{ $type === 'radio' ? 'Single Choice' : ucwords(str_replace('_', ' ', $type)) }}"
            >
                {!! $icons[$type] ?? '' !!}
                <span class="text-xs text-center whitespace-nowrap">
                    {{ $type === 'radio' ? 'Single Choice' : ucwords(str_replace('_', ' ', $type)) }}
                </span>
            </button>
        @endforeach
    </div>
</div>
       
