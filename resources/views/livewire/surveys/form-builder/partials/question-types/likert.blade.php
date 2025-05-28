@php
    $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
    $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
@endphp

<div class="mb-4">
    <div class="overflow-x-auto">
        <table class="mt-2 min-w-full text-center border border-gray-200">
            <thead>
                <tr>
                    <th class="bg-white w-52"></th>
                    @foreach($likertColumns as $colIndex => $column)
                        <th class="bg-white px-4 py-2 relative" style="vertical-align: bottom;">
                            <div x-data="{ focused: false }" class="flex justify-center items-center gap-0 relative">
                                <textarea
                                    wire:model.defer="likertColumns.{{ $question->id }}.{{ $colIndex }}"
                                    wire:blur="updateLikertColumn({{ $question->id }}, {{ $colIndex }})"
                                    class="text-center px-2 py-1 rounded border border-transparent focus:border-blue-400 focus:ring-0 focus:outline-none transition resize-none bg-white mx-auto"
                                    placeholder="Option"
                                    rows="1"
                                    style="min-width:6em; max-width:10em; width:8em; min-height:2.2em; max-height:10em; overflow-y:auto;"
                                    @if($selectedQuestionId !== $question->id) readonly @endif
                                    @focus="focused = true"
                                    @blur="focused = false"
                                    x-ref="textarea"
                                    @input="$refs.textarea.style.height = 'auto'; $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px';"
                                    x-init="$nextTick(() => { $refs.textarea.style.height = 'auto'; $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px'; })"
                                    x-effect="$refs.textarea && ($refs.textarea.style.height = 'auto', $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px')"
                                ></textarea>
                                <button
                                    x-show="focused"
                                    x-transition
                                    wire:click="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')"
                                    type="button"
                                    class="text-red-500 text-base absolute right-0 top-1/2 -translate-y-1/2"
                                    style="vertical-align: middle;"
                                >&#10005;</button>
                            </div>
                        </th>
                    @endforeach
                    <th class="bg-white px-4 py-2">
                        @if($selectedQuestionId === $question->id)
                            <button wire:click="addItem( 'likertColumn', {{ $question->id }})"
                                type="button"
                                class="text-blue-600 text-2xl font-bold hover:text-blue-800"
                                title="Add Option"
                            >+</button>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($likertRows as $rowIndex => $row)
                    @php $rowBg = $loop->even ? 'bg-gray-50' : 'bg-white'; @endphp
                    <tr class="{{ $rowBg }}">
                        <td class="px-4 py-2 text-left relative">
                            <div 
                                x-data="{ focused: false }" 
                                class="flex items-center gap-1"
                            >
                                <textarea
                                    wire:model.defer="likertRows.{{ $question->id }}.{{ $rowIndex }}"
                                    wire:blur="updateLikertRow({{ $question->id }}, {{ $rowIndex }})"
                                    class="w-full px-2 py-1 rounded border border-transparent focus:border-blue-400 focus:ring-0 focus:outline-none transition resize-none bg-white"
                                    placeholder="Statement"
                                    rows="2"
                                    style="min-height:3.5em; max-height:10em; overflow-y:auto;"
                                    @if($selectedQuestionId !== $question->id) readonly @endif
                                    @focus="focused = true"
                                    @blur="focused = false"
                                    x-ref="textarea"
                                    @input="$refs.textarea.style.height = 'auto'; $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px';"
                                    x-init="$nextTick(() => { $refs.textarea.style.height = 'auto'; $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px'; })"
                                    data-autoresize
                                ></textarea>
                                <span style="width: 2em; display: inline-block;">
                                    <button
                                        x-show="focused"
                                        x-transition
                                        wire:click="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')"
                                        type="button"
                                        class="text-red-500 text-base"
                                        style="vertical-align: middle;"
                                    >&#10005;</button>
                                </span>
                            </div>
                        </td>
                        @foreach($likertColumns as $colIndex => $column)
                            <td class="px-4 py-2">
                                <input type="radio" disabled class="accent-blue-500 w-5 h-5" />
                            </td>
                        @endforeach
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($selectedQuestionId === $question->id)
            <div class="flex justify-start mt-3">
                <button class="text-green-600 hover:text-green-800 font-bold" wire:click="addItem('likertRow', {{ $question->id }})"
                    type="button"
                    title="Add Statement"
                ><span class="text-2xl ">+</span> statement</button>
            </div>
        @endif
    </div>
</div>
