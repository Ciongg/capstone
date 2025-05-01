<div class="bg-gray-100 min-h-screen p-6">

    {{-- Sticky Survey Navbar --}}
<div class="sticky top-0 z-30 bg-white shadow flex items-center justify-between px-6 py-3 mb-4">
    <div class="flex items-center space-x-4">
        {{-- Survey Title (editable) --}}
        <input
            type="text"
            wire:model.defer="surveyTitle"
            wire:blur="updateSurveyTitle"
            class="text-2xl font-bold border-b border-gray-300 focus:border-blue-500 outline-none bg-transparent"
            style="min-width: 200px;"
        />
        <span class="text-gray-500 italic">Survey Title</span>
    </div>
    <div class="flex items-center space-x-4">
        <button
            wire:click="openSurveySettings"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            @if($isLocked) disabled @endif
        >
            Survey Settings
        </button>
        @if($isLocked)
            <a href="{{ route('surveys.responses', $survey->id) }}"
               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
               
               >
                View Responses
            </a>
            <button
                wire:click="unpublishSurvey"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
            >
                Test (Unpublish)
            </button>
        @else
            @if($survey->status === 'published')
                <button
                    wire:click="unpublishSurvey"
                    class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                >
                    Unpublish
                </button>
            @else
                <button
                    wire:click="publishSurvey"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >
                    Publish
                </button>
            @endif
            <button 
                wire:click="deleteAll"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
                Delete All Questions and Pages
            </button>
        @endif
    </div>
</div>




    <div class="space-y-6">

        @php $isLocked = $isLocked ?? false; @endphp

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
                                ($selectedQuestionId && $questions[$selectedQuestionId]['survey_page_id'] == $page->id)) 
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
                            @if($isLocked) disabled @endif
                        />
                        <input 
                            type="text" 
                            wire:blur="updatePage({{ $page->id }}, 'subtitle', $event.target.value)"
                            value="{{ $page->subtitle }}"
                            placeholder="Enter page subtitle" 
                            class="w-full text-lg text-gray-600 p-2 border border-gray-300 rounded"
                            @if($isLocked) disabled @endif
                        />

                        {{-- Delete Page Button (only when this page is selected and no question is selected) --}}
                        <template x-if="activePageId === {{ $page->id }} && selectedQuestionId === null">
                            <button 
                                wire:click.stop="removePage({{ $page->id }})"
                                class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                @if($isLocked) disabled @endif
                            >
                                Delete Page
                            </button>
                        </template>
                    </div>

                    {{-- Question Type Buttons (Only for Selected Page) --}}
                    <div 
                        x-show="activePageId === {{ $page->id }} && selectedQuestionId === null"
                        x-cloak
                        class="flex space-x-4 mt-4"
                    >
                        @foreach ($questionTypes as $type)
                            <button 
                                @click="selectedQuestionId = null"
                                wire:click="addQuestion('{{ $type }}')" 
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                Add {{ ucwords(str_replace('_', ' ', $type)) }}
                            </button>
                        @endforeach
                    </div>

                    <div @if($isLocked) class="pointer-events-none opacity-60" @endif>
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

                                {{-- For essay and short_text, show preview of input --}}
                                @if($question->question_type === 'essay')
                                    <textarea class="w-full border rounded px-3 py-2 mt-2 resize-none" rows="4" disabled placeholder="Essay response (multi-line, wraps)"></textarea>
                                @elseif($question->question_type === 'short_text')
                                    <input type="text" class="w-full border rounded px-3 py-2 mt-2" disabled placeholder="Short text response (single line, no wrap)">
                                @elseif($question->question_type === 'date')
                                    <input type="date" class="w-full border rounded px-3 py-2 mt-2" disabled>
                                @endif

                                {{-- Choices for Multiple Choice or Radio --}}
                                @if(in_array($question->question_type, ['multiple_choice', 'radio']) && isset($question->choices))
                                    <div class="mt-4 space-y-2">
                                        @foreach($question->choices->sortBy('order') as $choice)
                                            <div class="flex items-center space-x-2">
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
                                                <button
                                                    wire:click="removeChoice({{ $choice->id }})"
                                                    class="text-red-500 hover:text-red-700 ml-2"
                                                    title="Remove Choice"
                                                    type="button"
                                                >&#10005;</button>
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
                                        <div class="flex items-center mb-2">
                                            <span class="font-semibold mr-2">Likert Scale</span>
                                            <button wire:click="addLikertColumn({{ $question->id }})" type="button" class="ml-2 px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs">+ Option (Column)</button>
                                            <button wire:click="addLikertRow({{ $question->id }})" type="button" class="ml-2 px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs">+ Statement (Row)</button>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full border text-center">
                                                <thead>
                                                    <tr>
                                                        <th class="border px-2 py-1 bg-gray-100"></th>
                                                        @foreach($likertColumns as $colIndex => $column)
                                                            <th class="border px-2 py-1 bg-gray-100">
                                                                <input type="text"
                                                                    wire:model.defer="likertColumns.{{ $question->id }}.{{ $colIndex }}"
                                                                    wire:blur="updateLikertColumn({{ $question->id }}, {{ $colIndex }})"
                                                                    class="w-24 border rounded px-1 py-0.5 text-center"
                                                                    placeholder="Option"
                                                                />
                                                                <button wire:click="removeLikertColumn({{ $question->id }}, {{ $colIndex }})" type="button" class="text-red-500 ml-1">&#10005;</button>
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($likertRows as $rowIndex => $row)
                                                        <tr>
                                                            <td class="border px-2 py-1 bg-gray-50 text-left">
                                                                <input type="text"
                                                                    wire:model.defer="likertRows.{{ $question->id }}.{{ $rowIndex }}"
                                                                    wire:blur="updateLikertRow({{ $question->id }}, {{ $rowIndex }})"
                                                                    class="w-48 border rounded px-1 py-0.5"
                                                                    placeholder="Statement"
                                                                />
                                                                <button wire:click="removeLikertRow({{ $question->id }}, {{ $rowIndex }})" type="button" class="text-red-500 ml-1">&#10005;</button>
                                                            </td>
                                                            @foreach($likertColumns as $colIndex => $column)
                                                                <td class="border px-2 py-1">
                                                                    <input type="radio" disabled>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                {{-- Remove Question Button --}}
                                <template x-if="selectedQuestionId === {{ $question->id }}">
                                    <div class="flex items-center space-x-4 mt-2">
                                        <button 
                                            wire:click.stop="removeQuestion({{ $question->id }})" 
                                            class="text-red-500 hover:underline"
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

                                {{-- Question Type Buttons (Only for Selected Question) --}}
                                <template x-if="selectedQuestionId === {{ $question->id }}">
                                    <div class="flex space-x-4 mt-4">
                                        @foreach ($questionTypes as $type)
                                            <button 
                                                @click="selectedQuestionId = null"
                                                wire:click="addQuestion('{{ $type }}')" 
                                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                            >
                                                Add {{ ucwords(str_replace('_', ' ', $type)) }}
                                            </button>
                                        @endforeach
                                    </div>
                                </template>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
