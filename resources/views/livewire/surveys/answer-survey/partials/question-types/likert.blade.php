@php
    $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
    $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
@endphp

<div class="overflow-x-auto mt-2"
    x-data="{
        selected: @js($answers[$question->id] ?? array_fill(0, count($likertRows), null)),
        toggle(rowIndex, colIndex) {
            // If clicking the same option that's already selected, deselect it
            this.selected[rowIndex] = this.selected[rowIndex] === colIndex ? null : colIndex;
            $wire.set('answers.{{ $question->id}}.' + rowIndex, this.selected[rowIndex]);
        }
    }"
>
    <table class="min-w-full text-center">
        <thead>
            <tr>
                <th class="bg-white w-52"></th>
                @foreach($likertColumns as $colIndex => $column)
                    <th class="bg-white px-4 py-2 text-base font-medium">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($likertRows as $rowIndex => $row)
                @php $rowBg = $loop->even ? 'bg-gray-50' : 'bg-white'; @endphp
                <tr class="{{ $rowBg }}">
                    <td class="px-4 py-2 text-left text-base">{{ $row }}</td>
                    @foreach($likertColumns as $colIndex => $column)
                        <td class="px-4 py-2">
                            <input
                                type="radio"
                                name="answers[{{ $question->id }}][{{ $rowIndex }}]"
                                :checked="selected[{{ $rowIndex }}] === {{ $colIndex }}"
                                @click="toggle({{ $rowIndex }}, {{ $colIndex }})"
                                value="{{ $colIndex }}"
                                class="accent-blue-500"
                                style="width: 1.5em; height: 1.5em;"
                            >
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Clear All Button - Shows only when at least one selection exists -->
    <div x-show="Object.values(selected).some(val => val !== null)" class="mt-2">
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
    
    {{-- Add the error display specifically for the Likert block --}}
    @error('answers.' . $question->id)
        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
    @enderror
    {{-- Display errors for individual rows if needed --}}
    @foreach($likertRows as $rowIndex => $row)
        @error('answers.' . $question->id . '.' . $rowIndex)
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
        @enderror
    @endforeach
</div>
