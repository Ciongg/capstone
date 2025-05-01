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
        {{-- Survey Settings Button --}}
        <button
            wire:click="openSurveySettings"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
        >
            Survey Settings
        </button>
        {{-- Publish/Unpublish Button --}}
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


         {{-- Delete All Button --}}
         <div class="flex justify-end">
            <button 
                wire:click="deleteAll"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
                Delete All Questions and Pages
            </button>
        </div>
        
    </div>
</div>




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

                            {{-- Remove Question Button --}}
                            <template x-if="selectedQuestionId === {{ $question->id }}">
                                <button 
                                    wire:click.stop="removeQuestion({{ $question->id }})" 
                                    class="text-red-500 hover:underline mt-2"
                                >
                                    Remove Question
                                </button>
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
            @endforeach
        </div>
    </div>
</div>
