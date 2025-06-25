@php
    $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
    $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
    
    // Get translated data if available
    $translatedData = $translatedChoices[$question->id] ?? [];
    $translatedRows = $translatedData['rows'] ?? [];
    $translatedColumns = $translatedData['columns'] ?? [];
@endphp

<div class="mt-2"
    x-data="{
        selected: @js($answers[$question->id] ?? array_fill(0, count($likertRows), null)),
        toggle(rowIndex, colIndex) {

            // Make sure we're working with integers
            rowIndex = parseInt(rowIndex);
            colIndex = parseInt(colIndex);
            
            // If clicking the same option that's already selected, deselect it
            this.selected[rowIndex] = this.selected[rowIndex] === colIndex ? null : colIndex;
            $wire.set('answers.{{ $question->id}}.' + rowIndex, this.selected[rowIndex]);
            
        }
    }"
>
    <!-- Desktop View (Table Layout) -->
    <div class="overflow-x-auto hidden md:block">
        <table class="min-w-full text-center">
            <thead>
                <tr>
                    <th class="bg-white w-52"></th>
                    @foreach($likertColumns as $colIndex => $column)
                        <th class="bg-white px-4 py-2 text-base font-medium {{ isset($translatedColumns[$colIndex]) ? 'text-blue-600' : '' }}">
                            {{ $translatedColumns[$colIndex] ?? $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($likertRows as $rowIndex => $row)
                    @php $rowBg = $loop->even ? 'bg-gray-50' : 'bg-white'; @endphp
                    <tr class="{{ $rowBg }}">
                        <td class="px-4 py-2 text-left text-base {{ isset($translatedRows[$rowIndex]) ? 'text-blue-600' : '' }}" style="white-space: pre-line;">
                            {{ $translatedRows[$rowIndex] ?? $row }}
                        </td>
                        @foreach($likertColumns as $colIndex => $column)
                            <td class="px-4 py-2">
                                <input
                                    type="radio"
                                    name="answers[{{ $question->id }}][{{ $rowIndex }}]"
                                    :checked="selected[{{ $rowIndex }}] === {{ $colIndex }}"
                                    x-on:click="toggle({{ $rowIndex }}, {{ $colIndex }})"
                                    value="{{ $colIndex }}"
                                    class="accent-blue-500"
                                    style="width: 1.5em; height: 1.5em;"
                                    title="{{ $translatedRows[$rowIndex] ?? $row }} - {{ $translatedColumns[$colIndex] ?? $column }}"
                                >
                            </td>
                        @endforeach
                    </tr>
                    <!-- Error message for this row in desktop view -->
                    @error('answers.' . $question->id . '.' . $rowIndex)
                        <tr>
                            <td colspan="{{ count($likertColumns) + 1 }}" class="text-left px-4">
                                <div class="text-red-500 text-sm mt-1 mb-2">{{ $message }}</div>
                            </td>
                        </tr>
                    @enderror
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Mobile View -->
    <div class="md:hidden space-y-6">
        @foreach($likertRows as $rowIndex => $row)
            <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
                <div class="font-medium text-base mb-3 {{ isset($translatedRows[$rowIndex]) ? 'text-blue-600' : '' }}" style="white-space: pre-line;">
                    {{ $translatedRows[$rowIndex] ?? $row }}
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2" x-data="{ rowId: {{ $rowIndex }} }">
                    @foreach($likertColumns as $colIndex => $column)
                        <button 
                            type="button"
                            x-on:click="toggle(rowId, {{ $colIndex }})"
                            class="w-full py-3 px-2 text-center rounded border transition-all text-sm flex items-center justify-center {{ isset($translatedColumns[$colIndex]) ? 'text-blue-600' : '' }}"
                            :class="selected[rowId] === {{ $colIndex }} 
                                ? 'bg-blue-100 border-blue-500 text-blue-800 font-medium' 
                                : 'border-gray-300 hover:bg-gray-50'"
                            style="min-height:45px; padding-left:0.75rem; padding-right:0.75rem; font-variation-settings: 'wght' 500;"
                        >
                            <span class="break-words block w-full" style="hyphens: auto; word-break: break-word;">
                                {{ $translatedColumns[$colIndex] ?? $column }}
                            </span>
                        </button>
                    @endforeach
                </div>
                
                <!-- Error for specific row -->
                @error('answers.' . $question->id . '.' . $rowIndex)
                    <div class="text-red-500 text-sm mt-3">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
    </div>
    
    <!-- Clear All Button - Shows only when at least one selection exists -->
    <div x-show="Object.values(selected).some(val => val !== null)" class="mt-4">
        <button type="button" 
                x-on:click="
                    Object.keys(selected).forEach(key => {
                        selected[key] = null;
                        $wire.set('answers.{{ $question->id}}.' + key, null);
                    })
                "
                class="text-blue-600 text-sm hover:underline">
            Clear all responses
        </button>
    </div>
    
    <!-- General error for entire likert question -->
    @error('answers.' . $question->id)
        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
    @enderror
</div>
