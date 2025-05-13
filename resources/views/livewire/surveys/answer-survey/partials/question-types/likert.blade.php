@php
    $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
    $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
@endphp
<div class="overflow-x-auto mt-2">
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
                                wire:model="answers.{{ $question->id }}.{{ $rowIndex }}"
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
