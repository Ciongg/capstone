@php
    // Generate unique variable name based on context and id
    $showTypePickerVar = 'showTypePicker_' . $context . '_' . $id;
    
    // Alpine key based on context
    $alpineKey = $context === 'page' 
        ? "'type-picker-page-' + activePageId + '-' + selectedQuestionId" 
        : "'type-picker-question-' + selectedQuestionId";
        
    // Wire:click action based on context and order
    $wireClickAction = $context === 'page' 
        ? "addQuestion('TYPE')" 
        : "addQuestion('TYPE', $order)";
@endphp

<div
    x-data="{ {{ $showTypePickerVar }}: false }"
    :key="{{ $alpineKey }}"
    @click.away="{{ $showTypePickerVar }} = false"
>
    {{-- "Add Question Below" Button --}}
    <button
        x-show="!{{ $showTypePickerVar }}"
        @click="{{ $showTypePickerVar }} = true"
        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        type="button"
    >
        + Add Question Below
    </button>
    
    {{-- Picker Grid --}}
    <div
        x-show="{{ $showTypePickerVar }}"
        class="grid grid-cols-4 gap-2 mt-2 sm:grid-cols-4 xs:grid-cols-2"
    >
        @foreach ($questionTypes as $type)
            <button
                @click="{{ $showTypePickerVar }} = false"
                wire:click="{!! str_replace('TYPE', $type, $wireClickAction) !!}"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap"
                type="button"
            >
                {{-- Modify the display text for 'radio' --}}
                {{ $type === 'radio' ? 'Single Choice' : ucwords(str_replace('_', ' ', $type)) }}
            </button>
        @endforeach
    </div>
</div>
