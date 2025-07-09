@php
    $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
    $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
@endphp

<div class="mb-4">
    <!-- Desktop View - Table Layout -->
    <div class="overflow-x-auto hidden md:block">
        <table class="mt-2 min-w-full text-center border border-gray-200">
            <thead>
                <tr>
                    <th class="bg-white w-52"></th>
                    @foreach($likertColumns as $colIndex => $column)
                        <th class="bg-white px-4 py-2 relative" style="vertical-align: bottom;">
                            <div x-data="{ focused: false }" class="flex justify-center items-center gap-0 relative">
                                <textarea
                                    id="likert-col-{{ $question->id }}-{{ $colIndex }}"
                                    wire:model.defer="likertColumns.{{ $question->id }}.{{ $colIndex }}"
                                    wire:blur="updateLikertColumn({{ $question->id }}, {{ $colIndex }})"
                                    class="text-center px-2 py-1 rounded border border-gray-200 focus:border-blue-400 focus:ring-0 focus:outline-none transition resize-none bg-white mx-auto"
                                    placeholder="Option"
                                    style="min-width:6em; max-width:10em; width:8em; min-height:2.2em; max-height:10em; overflow-y:auto; field-sizing: content;"
                                    @if($selectedQuestionId !== $question->id) readonly @endif
                                    @focus="focused = true"
                                    @blur="focused = false"
                                ></textarea>
                                <button
                                    x-show="selectedQuestionId === {{ $question->id }}" x-cloak
                                    wire:click="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')"
                                    type="button"
                                    class="text-red-500 text-base absolute right-0 top-1/2 -translate-y-1/2 flex items-center"
                                    style="vertical-align: middle;"
                                    wire:loading.attr="disabled"
                                    wire:target="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')"
                                >
                                    <span wire:loading.remove wire:target="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')">&#10005;</span>
                                    <span wire:loading wire:target="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')">
                                          <svg class="animate-spin h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    </span>
                                </button>
                            </div>
                        </th>
                    @endforeach
                    <th class="bg-white px-4 py-2">
                        @if($selectedQuestionId === $question->id)
                            <button 
                                wire:click="addItem('likertColumn', {{ $question->id }})"
                                type="button"
                                class="text-blue-600 text-2xl font-bold hover:text-blue-800 flex items-center"
                                title="Add Option"
                                wire:loading.attr="disabled"
                                wire:target="addItem('likertColumn', {{ $question->id }})"
                            >
                                <span wire:loading.remove wire:target="addItem('likertColumn', {{ $question->id }})">+</span>
                                <span wire:loading wire:target="addItem('likertColumn', {{ $question->id }})" class="flex items-center space-x-1">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
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
                                    id="likert-row-{{ $question->id }}-{{ $rowIndex }}"
                                    wire:model.defer="likertRows.{{ $question->id }}.{{ $rowIndex }}"
                                    wire:blur="updateLikertRow({{ $question->id }}, {{ $rowIndex }})"
                                    class="w-full px-2 py-1 rounded border border-gray-200 focus:border-blue-400 focus:ring-0 focus:outline-none transition resize-none bg-white"
                                    placeholder="Statement"
                                    style="min-height:3.5em; max-height:15em; overflow-y:auto; field-sizing: content;"
                                    @if($selectedQuestionId !== $question->id) readonly @endif
                                    @focus="focused = true"
                                    @blur="focused = false"
                                ></textarea>
                                <span style="width: 2em; display: inline-block;">
                                    <button
                                        x-show="selectedQuestionId === {{ $question->id }}" x-cloak
                                        wire:click="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')"
                                        type="button"
                                        class="text-red-500 text-base flex items-center"
                                        style="vertical-align: middle;"
                                        wire:loading.attr="disabled"
                                        wire:target="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')"
                                    >
                                        <span wire:loading.remove wire:target="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')">&#10005;</span>
                                        <span wire:loading wire:target="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')">
                                            <svg class="animate-spin h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                        </span>
                                    </button>
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
    </div>

    <!-- Mobile View - Stacked Layout -->
    <div class="md:hidden space-y-6">
        <!-- Column Headers for Mobile (Options) -->
        @if($selectedQuestionId === $question->id)
            <div class="mb-4 border border-gray-200 rounded-lg p-4 bg-white">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Options:</h4>
                <div class="space-y-2">
                    @foreach($likertColumns as $colIndex => $column)
                        <div x-data="{ focused: false }" class="flex items-center gap-2">
                            <textarea
                                id="likert-col-mobile-{{ $question->id }}-{{ $colIndex }}"
                                wire:model.defer="likertColumns.{{ $question->id }}.{{ $colIndex }}"
                                wire:blur="updateLikertColumn({{ $question->id }}, {{ $colIndex }})"
                                class="flex-1 px-2 py-1 rounded border border-gray-200 focus:border-blue-400 focus:ring-0 focus:outline-none transition resize-none bg-white"
                                placeholder="Option"
                                style="min-height:2.2em; max-height:6em; overflow-y:auto; field-sizing: content;"
                                @if($selectedQuestionId !== $question->id) readonly @endif
                                @focus="focused = true"
                                @blur="focused = false"
                            ></textarea>
                            <button
                                x-show="selectedQuestionId === {{ $question->id }}" x-cloak
                                wire:click="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')"
                                type="button"
                                class="text-red-500 text-base px-2 flex items-center"
                                wire:loading.attr="disabled"
                                wire:target="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')"
                            >
                                <span wire:loading.remove wire:target="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')">&#10005;</span>
                                <span wire:loading wire:target="removeItem('likertColumn', '{{ $question->id }}-{{ $colIndex }}')">
                                     <svg class="animate-spin h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    @endforeach
                    <button wire:click="addItem('likertColumn', {{ $question->id }})"
                        type="button"
                        class="text-blue-600 text-sm font-medium hover:text-blue-800 mt-2 flex items-center space-x-1"
                        title="Add Option"
                        wire:loading.attr="disabled"
                        wire:target="addItem('likertColumn', {{ $question->id }})"
                    >
                        <span wire:loading.remove wire:target="addItem('likertColumn', {{ $question->id }})">+ Add Option</span>
                        <span wire:loading wire:target="addItem('likertColumn', {{ $question->id }})" class="flex items-center space-x-1">
                            <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                        </span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Statements with Options for Mobile -->
        @foreach($likertRows as $rowIndex => $row)
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <!-- Statement -->
                <div class="mb-3">
                    @if($selectedQuestionId === $question->id)
                        <div x-data="{ focused: false }" class="flex items-start gap-2">
                            <textarea
                                id="likert-row-mobile-{{ $question->id }}-{{ $rowIndex }}"
                                wire:model.defer="likertRows.{{ $question->id }}.{{ $rowIndex }}"
                                wire:blur="updateLikertRow({{ $question->id }}, {{ $rowIndex }})"
                                class="flex-1 px-2 py-1 rounded border border-gray-200 focus:border-blue-400 focus:ring-0 focus:outline-none transition resize-none bg-white font-medium"
                                placeholder="Statement"
                                style="min-height:3.5em; max-height:15em; overflow-y:auto; field-sizing: content;"
                                @focus="focused = true"
                                @blur="focused = false"
                            ></textarea>
                            <button
                                x-show="selectedQuestionId === {{ $question->id }}" x-cloak
                                wire:click="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')"
                                type="button"
                                class="text-red-500 text-base px-2 mt-1 flex items-center"
                                wire:loading.attr="disabled"
                                wire:target="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')"
                            >
                                <span wire:loading.remove wire:target="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')">&#10005;</span>
                                <span wire:loading wire:target="removeItem('likertRow', '{{ $question->id }}-{{ $rowIndex }}')">
                                       <svg class="animate-spin h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    @else
                        <div class="font-medium text-gray-800">{{ $row }}</div>
                    @endif
                </div>

                <!-- Options for this statement -->
                <div class="grid grid-cols-1 gap-2">
                    @foreach($likertColumns as $colIndex => $column)
                        <label class="flex items-start p-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="radio" disabled class="accent-blue-500 w-4 h-4 mr-3 flex-shrink-0 mt-1" />
                            <span class="text-sm text-gray-700 break-words whitespace-normal" style="word-break: break-word; overflow-wrap: break-word;">
                                {{ $column }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($selectedQuestionId === $question->id)
            <div class="flex justify-start">
                <button 
                    class="text-green-800 hover:text-green-900 font-bold flex items-center space-x-1" 
                    wire:click="addItem('likertRow', {{ $question->id }})"
                    type="button"
                    title="Add Statement"
                    wire:loading.attr="disabled"
                    wire:target="addItem('likertRow', {{ $question->id }})"
                >
                    <span wire:loading.remove wire:target="addItem('likertRow', {{ $question->id }})">
                        <span class="text-2xl">+</span> statement
                    </span>
                    <span wire:loading wire:target="addItem('likertRow', {{ $question->id }})" class="flex items-center space-x-1">
                        <svg class="animate-spin h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        @endif
    </div>

    <!-- Add Statement Button for Desktop -->
    @if($selectedQuestionId === $question->id)
        <div class="hidden md:flex justify-start mt-3">
            <button 
                class="text-green-600 hover:text-green-800 font-bold flex items-center space-x-1" 
                wire:click="addItem('likertRow', {{ $question->id }})"
                type="button"
                title="Add Statement"
                wire:loading.attr="disabled"
                wire:target="addItem('likertRow', {{ $question->id }})"
            >
                <span wire:loading.remove wire:target="addItem('likertRow', {{ $question->id }})">
                    <span class="text-2xl">+</span> statement
                </span>
                <span wire:loading wire:target="addItem('likertRow', {{ $question->id }})" class="flex items-center space-x-1">
                    <svg class="animate-spin h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
            </button>
        </div>
    @endif
</div>


