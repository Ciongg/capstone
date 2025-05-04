<div class="bg-gray-100 min-h-screen p-6">

    {{-- Sticky Survey Navbar --}}
    <div class="sticky top-0 z-30 bg-white shadow flex items-center justify-between px-6 py-3 mb-4">
        {{-- Left Side: Title --}}
        <div class="flex items-center space-x-4">
            <input
                type="text"
                wire:model.defer="surveyTitle"
                wire:blur="updateSurveyTitle"
                class="text-xl font-bold border-b border-gray-300 focus:border-blue-500 outline-none bg-transparent py-1"
                style="min-width: 200px;"
            />
            <span class="text-gray-500 italic text-sm">Survey Title</span>
        </div>

        {{-- Right Side: Buttons & Status --}}
        <div class="flex items-center space-x-3">
            {{-- Survey Settings Button (Icon) --}}
            <button
                x-data
                x-on:click="$dispatch('open-modal', {name : 'survey-settings-modal-{{ $survey->id }}'})"
                class="flex items-center justify-center h-9 w-9 px-2 py-1.5 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                title="Survey Settings"
            >
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.646.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 1.255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.333.184-.583.496-.646.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.646-.87-.074-.04-.147-.083-.22-.127-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.759 6.759 0 0 1 0-1.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.184.582-.496.646-.87l.213-1.281Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>

            {{-- Display Status --}}
            <span @class([
                'inline-flex items-center h-9 px-3 py-1.5 text-xs font-semibold rounded-full',
                'bg-gray-100 text-gray-700' => $survey->status === 'pending',
                'bg-blue-100 text-blue-700' => $survey->status === 'published',
                'bg-amber-100 text-amber-700' => $survey->status === 'ongoing',
                'bg-green-100 text-green-700' => $survey->status === 'finished',
                'bg-red-100 text-red-800' => $survey->status === 'closed',
                'bg-gray-100 text-gray-800' => !in_array($survey->status, ['pending', 'published', 'ongoing', 'finished', 'closed']),
            ])>
                Status: {{ ucfirst($survey->status) }}
            </span>

            {{-- View Responses Button --}}
            @if($hasResponses)
                <a href="{{ route('surveys.responses', $survey->id) }}"
                   class="inline-flex items-center h-9 px-4 py-1.5 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    View Responses
                </a>
            @endif

            {{-- Publish/Unpublish Buttons --}}
            @if($survey->status === 'published' || $survey->status === 'ongoing')
                <button
                    wire:click="unpublishSurvey"
                    class="inline-flex items-center h-9 px-4 py-1.5 bg-yellow-500 text-white text-sm font-medium rounded hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                >
                    Unpublish
                </button>
            @else
                <button
                    wire:click="publishSurvey"
                    class="inline-flex items-center h-9 px-4 py-1.5 bg-green-500 text-white text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    Publish
                </button>
            @endif

            {{-- Delete All Button --}}
            <button
                wire:click="deleteAll"
                class="inline-flex items-center h-9 px-4 py-1.5 bg-red-500 text-white text-sm font-medium rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                title="Delete All Questions and Pages"
            >
                Delete All
            </button>
        </div>
    </div>

    {{-- Modal --}}
    <x-modal name="survey-settings-modal-{{ $survey->id }}" title="Survey Settings">
        <livewire:surveys.form-builder.modal.survey-settings-modal :survey="$survey" />
    </x-modal>

    <div class="space-y-6">

        {{-- Page Selector --}}
        @if ($pages->isEmpty())
            <div class="text-center mt-6">
                <button 
                    wire:click="addPage"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                    + Add Page
                </button>
            </div>
        @else
            <div class="flex space-x-4 items-center">
                @foreach ($pages as $page)
                    <button 
                        wire:click="setActivePage({{ $page->id }})"
                        class="px-4 py-2 rounded 
                            {{ ($activePageId === $page->id || 
                                ($selectedQuestionId && isset($questions[$selectedQuestionId]) && $questions[$selectedQuestionId]['survey_page_id'] == $page->id)) 
                                ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}"
                    >
                        Page {{ $page->page_number }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Pages and Questions --}}
        <div x-data="{ selectedQuestionId: @entangle('selectedQuestionId'), activePageId: @entangle('activePageId') }">
            @foreach ($pages as $page)
                <div class="bg-white shadow-md rounded-lg mt-6 p-6">
                    {{-- Page Title and Subtitle --}}
                    <div 
                        class="mb-4 p-4 rounded-lg transition hover:shadow-md"
                        :class="{ 'border-2 border-blue-500': activePageId === {{ $page->id }} && selectedQuestionId === null }"
                        @click="selectedQuestionId = null; activePageId = {{ $page->id }}; $wire.setActivePage({{ $page->id }})"
                        style="cursor: pointer;"
                    >
                        <input 
                            type="text" 
                            wire:blur="updatePage({{ $page->id }}, 'title', $event.target.value)"
                            value="{{ $page->title }}"
                            placeholder="Enter page title" 
                            class="w-full text-2xl font-bold p-2 border border-gray-300 rounded mb-2"
                        />
                        <input 
                            type="text" 
                            wire:blur="updatePage({{ $page->id }}, 'subtitle', $event.target.value)"
                            value="{{ $page->subtitle }}"
                            placeholder="Enter page subtitle" 
                            class="w-full text-lg text-gray-600 p-2 border border-gray-300 rounded"
                            />
                            
                            {{-- Delete Page Button (only when this page is selected and no question is selected) --}}
                            <template x-if="activePageId === {{ $page->id }} && selectedQuestionId === null">
                                <button 
                                    wire:click.stop="removePage({{ $page->id }})"
                                    class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                >
                                    Delete Page
                                </button>
                            </template>
                            {{-- Question Type Picker (for Page) --}}
                            @php
                                $showTypePickerVar = 'showTypePicker_' . $page->id;
                            @endphp
                            <div 
                                x-data="{ {{ $showTypePickerVar }}: false }"
                                x-show="activePageId === {{ $page->id }} && selectedQuestionId === null"
                                x-cloak
                                class="mt-4"
                                :key="'type-picker-page-' + activePageId + '-' + selectedQuestionId"
                                @click.away="{{ $showTypePickerVar }} = false"
                            >
                                <button 
                                    x-show="!{{ $showTypePickerVar }}"
                                    @click="{{ $showTypePickerVar }} = true"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                    type="button"
                                >
                                    + Add Question Below
                                </button>
                                <div 
                                    x-show="{{ $showTypePickerVar }}"
                                    class="grid grid-cols-4 gap-2 mt-2 sm:grid-cols-4 xs:grid-cols-2"
                                >
                                    @foreach ($questionTypes as $type)
                                        <button 
                                            @click="selectedQuestionId = null; {{ $showTypePickerVar }} = false"
                                            wire:click="addQuestion('{{ $type }}')" 
                                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap"
                                            type="button"
                                        >
                                            {{ ucwords(str_replace('_', ' ', $type)) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                    </div>

                    @foreach ($page->questions->sortBy('order') as $question)
                        <div
                            wire:key="question-{{ $question->id }}-order-{{ $question->order }}"
                            @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $page->id }}; $wire.selectQuestion({{ $question->id }})"
                            :class="{ 'border-2 border-blue-500': selectedQuestionId === {{ $question->id }} }"
                            class="p-4 bg-gray-50 rounded-lg shadow-sm mb-4 transition hover:shadow-md cursor-pointer"
                        >
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2 w-full">
                                    <span class="text-gray-500 font-bold">Q{{ $question->order }}.</span>
                                    <input 
                                        type="text" 
                                        wire:model.defer="questions.{{ $question->id }}.question_text"
                                        wire:blur="updateQuestion({{ $question->id }})"
                                        placeholder="Enter question title" 
                                        class="w-full p-2 border border-gray-300 rounded"
                                    >
                                </div>
                                <span class="ml-4 whitespace-nowrap text-lg text-gray-500">{{ ucwords(str_replace('_', ' ', $question->question_type)) }}</span>
                            </div>

                            {{-- Rating: Star count selector --}}
                            @if($question->question_type === 'rating')
                                <div class="mt-2 flex items-center space-x-2">
                                    <label class="text-gray-600">Stars:</label>
                                    <select
                                        wire:model="ratingStars.{{ $question->id }}"
                                        wire:change="updateRatingStars({{ $question->id }})"
                                        class="border rounded px-2 py-1"
                                        style="width: auto;"
                                    >
                                        @for($i = 2; $i <= 10; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <span class="ml-2 text-yellow-400">
                                        @for($i = 0; $i < ($ratingStars[$question->id] ?? 5); $i++)
                                            ★
                                        @endfor
                                    </span>
                                </div>
                            @endif

                            {{-- For essay --}}
                            @if($question->question_type === 'essay')
                                <textarea
                                    class="w-full border rounded px-3 py-2 mt-2 resize-none cursor-pointer"
                                    rows="4"
                                    readonly
                                    tabindex="-1"
                                    placeholder="Essay response (multi-line, wraps)"
                                    @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $page->id }}; $wire.selectQuestion({{ $question->id }})"
                                ></textarea>
                            @elseif($question->question_type === 'short_text')
                                <input
                                    type="text"
                                    class="w-full border rounded px-3 py-2 mt-2 cursor-pointer"
                                    readonly
                                    tabindex="-1"
                                    placeholder="Short text response (single line, no wrap)"
                                    @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $page->id }}; $wire.selectQuestion({{ $question->id }})"
                                >
                            @elseif($question->question_type === 'date')
                                <input
                                    type="date"
                                    class="w-full border rounded px-3 py-2 mt-2 cursor-pointer"
                                    readonly
                                    tabindex="-1"
                                    @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $page->id }}; $wire.selectQuestion({{ $question->id }})"
                                >
                            @endif

                            {{-- Choices for Multiple Choice or Radio --}}
                            @if(in_array($question->question_type, ['multiple_choice', 'radio']) && isset($question->choices))
                                <div class="mt-4 space-y-2">
                                    @foreach($question->choices->sortBy('order') as $choice)
                                        <div class="flex items-center space-x-2" wire:key="choice-{{ $choice->id }}">
                                            @if($question->question_type === 'radio')
                                                <span class="inline-block w-4 h-4 rounded-full border-2 border-gray-400 mr-2"></span>
                                            @else
                                                <span class="inline-block w-4 h-4 rounded border-2 border-gray-400 mr-2"></span>
                                            @endif
                                            <input
                                                type="text"
                                                wire:model.defer="choices.{{ $choice->id }}.choice_text"
                                                wire:blur="updateChoice({{ $choice->id }})"
                                                placeholder="Choice text"
                                                class="flex-1 p-2 border border-gray-300 rounded"
                                            />
                                            <span x-show="selectedQuestionId === {{ $question->id }}">
                                                <button
                                                    wire:click="removeChoice({{ $choice->id }})"
                                                    class="text-red-500 hover:text-red-700 ml-2"
                                                    title="Remove Choice"
                                                    type="button"
                                                >&#10005;</button>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Likert Scale --}}
                            @if($question->question_type === 'likert')
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
                                                                    wire:click="removeLikertColumn({{ $question->id }}, {{ $colIndex }})"
                                                                    type="button"
                                                                    class="text-red-500 text-base absolute right-0 top-1/2 -translate-y-1/2"
                                                                    style="vertical-align: middle;"
                                                                >&#10005;</button>
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                    <th class="bg-white px-4 py-2">
                                                        @if($selectedQuestionId === $question->id)
                                                            <button wire:click="addLikertColumn({{ $question->id }})"
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
                                                                ></textarea>
                                                                <span style="width: 2em; display: inline-block;">
                                                                    <button
                                                                        x-show="focused"
                                                                        x-transition
                                                                        wire:click="removeLikertRow({{ $question->id }}, {{ $rowIndex }})"
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
                                                <button class="text-green-600 hover:text-green-800 font-bold" wire:click="addLikertRow({{ $question->id }})"
                                                    type="button"
                                                    title="Add Statement"
                                                ><span class="text-2xl ">+</span> statement</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center space-x-2 mt-2">
                                <label class="text-sm text-gray-600">Required</label>
                                <input
                                    type="checkbox"
                                    wire:model="questions.{{ $question->id }}.required"
                                    wire:change="updateQuestion({{ $question->id }})"
                                    class="form-checkbox h-5 w-5 text-blue-600"
                                    @if(isset($questions[$question->id]) && $questions[$question->id]['required']) checked @endif
                                />
                            </div>

                            {{-- Remove Question Button --}}
                            <template x-if="selectedQuestionId === {{ $question->id }}">
                                <div class="flex items-center space-x-4 mt-2">
                                    <button 
                                        wire:click.stop="removeQuestion({{ $question->id }})" 
                                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Remove Question
                                    </button>

                                    @if(in_array($question->question_type, ['multiple_choice', 'radio']))
                                        <button
                                            wire:click.stop="addChoice({{ $question->id }})"
                                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 flex items-center"
                                        >
                                            <span class="mr-1 text-lg font-bold">+</span> Add Choice
                                        </button>
                                    @endif
                                </div>
                            </template>

                            {{-- Question Type Picker for Selected Question --}}
                            <template x-if="selectedQuestionId === {{ $question->id }}">
                                @php $showTypePickerVar = 'showTypePicker_question_' . $question->id; @endphp
                                <div 
                                    x-data="{ {{ $showTypePickerVar }}: false }"
                                    class="mt-4"
                                    :key="'type-picker-question-' + selectedQuestionId"
                                    @click.away="{{ $showTypePickerVar }} = false"
                                >
                                    <button 
                                        x-show="!{{ $showTypePickerVar }}"
                                        @click="{{ $showTypePickerVar }} = true"
                                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                        type="button"
                                    >
                                        + Add Question Below
                                    </button>
                                    <div 
                                        x-show="{{ $showTypePickerVar }}"
                                        class="grid grid-cols-4 gap-2 mt-2 sm:grid-cols-4 xs:grid-cols-2"
                                    >
                                        @foreach ($questionTypes as $type)
                                            <button 
                                                @click="selectedQuestionId = null; {{ $showTypePickerVar }} = false"
                                                wire:click="addQuestion('{{ $type }}', {{ $question->order }})" 
                                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap"
                                                type="button"
                                            >
                                                {{ ucwords(str_replace('_', ' ', $type)) }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
