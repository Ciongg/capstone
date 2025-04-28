<div class="bg-gray-100 min-h-screen p-6">
    <div class="space-y-6">

        {{-- Delete All Button --}}
        <div class="flex justify-end">
            <button 
                wire:click="deleteAll"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
                Delete All Questions and Pages
            </button>
        </div>

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
                        class="px-4 py-2 rounded {{ $activePageId === $page->id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}"
                    >
                        Page {{ $page->page_number }}
                    </button>
                @endforeach
            </div>
        @endif

        

        {{-- Pages and Questions --}}
        @foreach ($pages as $page)
            <div class="bg-white shadow-md rounded-lg mt-6 p-6">
                {{-- Page Title and Subtitle --}}
                <div 
                    class="mb-4 p-4 rounded-lg transition hover:shadow-md {{ $activePageId === $page->id && !$selectedQuestionId ? 'border-2 border-blue-500' : '' }}"
                    wire:click="setActivePage({{ $page->id }})"
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
                </div>



                {{-- Question Type Buttons (Only for Selected Page) --}}
                @if ($activePageId === $page->id && !$selectedQuestionId)
                    <div class="flex space-x-4 mt-4">
                        @foreach ($questionTypes as $type)
                            <button 
                                wire:click="addQuestion('{{ $type }}')" 
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                Add {{ ucwords(str_replace('_', ' ', $type)) }}
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Questions --}}
                @foreach ($page->questions->sortBy('order') as $question)
                    <div 
                        class="p-4 bg-gray-50 rounded-lg shadow-sm mb-4 transition hover:shadow-md"
                        wire:click="selectQuestion({{ $question->id }})"
                        style="cursor: pointer; {{ $selectedQuestionId === $question->id ? 'border: 2px solid #3b82f6;' : '' }}"
                    >
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2 w-full">
                                <span class="text-gray-500 font-bold">Q{{ $question->order }}.</span>
                                <input 
                                type="text" 
                                wire:blur="updateQuestion({{ $question->id }}, $event.target.value)"
                                value="{{ $question->question_text }}"
                                placeholder="Enter question title" 
                                class="w-full p-2 border border-gray-300 rounded"
                                >
                            </div>
                            <span class="text-sm text-gray-500">{{ ucwords(str_replace('_', ' ', $question->question_type)) }}</span>
                        </div>

                        {{-- Render Question Type --}}
                        @if ($question->question_type === 'multiple_choice')
                            <div class="mt-4">
                                <div class="space-y-2">
                                    @foreach ($question->choices as $choice)
                                        <div class="flex items-center space-x-2">
                                            <input 
                                                type="checkbox" 
                                                disabled 
                                                class="form-checkbox h-5 w-5 text-blue-600"
                                            />
                                            <input 
                                                type="text" 
                                                wire:model.defer="choicesData.{{ $choice->id }}.choice_text" 
                                                wire:blur="updateChoice({{ $choice->id }})"
                                                placeholder="Enter choice text" 
                                                class="w-full p-2 border border-gray-300 rounded"
                                            />
                                            @if ($selectedQuestionId === $question->id)
                                                <button 
                                                    wire:click="removeChoice({{ $choice->id }})" 
                                                    class="text-red-500 hover:underline"
                                                >
                                                    Remove
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if ($selectedQuestionId === $question->id)
                                    <button 
                                        wire:click="addChoice({{ $question->id }})" 
                                        class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                                    >
                                        + Add Choice
                                    </button>
                                @endif
                            </div>
                        @elseif ($question->question_type === 'radio')
                            <div class="mt-4">
                                <div class="space-y-2">
                                    @foreach ($question->choices as $choice)
                                        <div class="flex items-center space-x-2">
                                            <input 
                                                type="radio" 
                                                name="question_{{ $question->id }}" 
                                                disabled 
                                                class="form-radio h-5 w-5 text-blue-600"
                                            />
                                            <input 
                                                type="text" 
                                                wire:model.defer="choicesData.{{ $choice->id }}.choice_text" 
                                                wire:blur="updateChoice({{ $choice->id }})"
                                                placeholder="Enter choice text" 
                                                class="w-full p-2 border border-gray-300 rounded"
                                            />
                                            @if ($selectedQuestionId === $question->id)
                                                <button 
                                                    wire:click="removeChoice({{ $choice->id }})" 
                                                    class="text-red-500 hover:underline"
                                                >
                                                    Remove
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if ($selectedQuestionId === $question->id)
                                    <button 
                                        wire:click="addChoice({{ $question->id }})" 
                                        class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                                    >
                                        + Add Choice
                                    </button>
                                @endif
                            </div>
                        @elseif ($question->question_type === 'essay')
                            <div class="mt-4">
                                <textarea 
                                    class="w-full p-2 border border-gray-300 rounded" 
                                    placeholder="Essay response will appear here (preview mode)" 
                                    disabled
                                ></textarea>
                            </div>
                        @elseif ($question->question_type === 'page')
                            <div class="mt-4">
                                <button 
                                    wire:click="addPage"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                >
                                    Add New Page
                                </button>
                            </div>
                        @endif

                        {{-- Remove Question Button --}}
                        @if ($selectedQuestionId === $question->id)
                            <button 
                                wire:click="removeQuestion({{ $question->id }})" 
                                class="text-red-500 hover:underline mt-2"
                            >
                                Remove Question
                            </button>
                        @endif
                    </div>

                    {{-- Question Type Buttons (Only for Selected Question) --}}
                    @if ($selectedQuestionId === $question->id)
                        <div class="flex space-x-4 mt-4">
                            @foreach ($questionTypes as $type)
                                <button 
                                    wire:click="addQuestion('{{ $type }}')" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                >
                                    Add {{ ucwords(str_replace('_', ' ', $type)) }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</div>
