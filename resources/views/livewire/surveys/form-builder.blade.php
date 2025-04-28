<div>
    <div class="space-y-6">
       
        {{-- Message if no pages --}}
        @if ($pages->isEmpty())
            <div class="mt-6 text-center text-gray-500">No pages yet. Click "Add Page" to start!</div>
        @endif

         {{-- Page Selector --}}
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
        

        {{-- Pages and Questions --}}
        @foreach ($pages as $page)
            <div class="border p-4 rounded-lg mt-6 bg-gray-100">
                {{-- Page Title and Subtitle --}}
                <div class="mb-4">
                    <input 
                        type="text" 
                        wire:model.defer="pages.{{ $page->id }}.title" 
                        wire:blur="updatePage({{ $page->id }}, 'title', $event.target.value)"
                        placeholder="Enter page title" 
                        class="w-full text-2xl font-bold p-2 border rounded mb-2"
                    />
                    <input 
                        type="text" 
                        wire:model.defer="pages.{{ $page->id }}.subtitle" 
                        wire:blur="updatePage({{ $page->id }}, 'subtitle', $event.target.value)"
                        placeholder="Enter page subtitle" 
                        class="w-full text-lg text-gray-600 p-2 border rounded"
                    />
                </div>

                @foreach ($page->questions->sortBy('order') as $question)
                    <div class="p-3 bg-white mb-2 rounded shadow">
                        <div class="flex justify-between items-center">
                            <strong>({{ $question->order }}) [{{ ucwords(str_replace('_', ' ', $question->question_type)) }}]</strong>
                            <button 
                                wire:click="removeQuestion({{ $question->id }})" 
                                class="text-red-500 hover:underline"
                            >
                                Remove Question
                            </button>
                        </div>
                        
                        {{-- Render Question Type --}}
                        @if ($question->question_type === 'multiple_choice')
                            <div>
                                <input 
                                    type="text" 
                                    wire:model.defer="questions.{{ $question->id }}.question_text" 
                                    wire:blur="updateQuestion({{ $question->id }})"
                                    placeholder="Enter question title" 
                                    class="w-full p-2 border rounded mb-2"
                                />

                                <div class="space-y-2">
                                    @foreach ($question->choices as $choice)
                                        <div class="flex items-center space-x-2">
                                            {{-- Checkbox for multiple choice --}}
                                            <input 
                                                type="checkbox" 
                                                disabled 
                                                class="form-checkbox h-5 w-5 text-blue-600"
                                            />
                                            <input 
                                                type="text" 
                                                wire:model.defer="choices.{{ $choice->id }}.choice_text" 
                                                wire:blur="updateChoice({{ $choice->id }})"
                                                placeholder="Enter choice text" 
                                                class="w-full p-2 border rounded"
                                            />
                                            <button 
                                                wire:click="removeChoice({{ $choice->id }})" 
                                                class="text-red-500 hover:underline"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <button 
                                    wire:click="addChoice({{ $question->id }})" 
                                    class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                                >
                                    + Add Choice
                                </button>
                            </div>
                        @elseif ($question->question_type === 'radio')
                            <div>
                                <input 
                                    type="text" 
                                    wire:model.defer="questions.{{ $question->id }}.question_text" 
                                    wire:blur="updateQuestion({{ $question->id }})"
                                    placeholder="Enter question title" 
                                    class="w-full p-2 border rounded mb-2"
                                />

                                <div class="space-y-2">
                                    @foreach ($question->choices as $choice)
                                        <div class="flex items-center space-x-2">
                                            {{-- Radio button for single choice --}}
                                            <input 
                                                type="radio" 
                                                name="question_{{ $question->id }}" 
                                                disabled 
                                                class="form-radio h-5 w-5 text-blue-600"
                                            />
                                            <input 
                                                type="text" 
                                                wire:model.defer="choices.{{ $choice->id }}.choice_text" 
                                                wire:blur="updateChoice({{ $choice->id }})"
                                                placeholder="Enter choice text" 
                                                class="w-full p-2 border rounded"
                                            />
                                            <button 
                                                wire:click="removeChoice({{ $choice->id }})" 
                                                class="text-red-500 hover:underline"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <button 
                                    wire:click="addChoice({{ $question->id }})" 
                                    class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                                >
                                    + Add Choice
                                </button>
                            </div>
                        @elseif ($question->question_type === 'essay')
                            <div>
                                <input 
                                    type="text" 
                                    wire:model.defer="questions.{{ $question->id }}.question_text" 
                                    wire:blur="updateQuestion({{ $question->id }})"
                                    placeholder="Enter question title" 
                                    class="w-full p-2 border rounded mb-2"
                                />
                                <textarea 
                                    class="w-full p-2 border rounded" 
                                    placeholder="Essay response will appear here (preview mode)" 
                                    disabled
                                ></textarea>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach






        

         {{-- Question Type Buttons --}}
         <div class="flex space-x-4 items-center">
            
            @foreach ($questionTypes as $type)
                @if ($type === 'add_page')
                    <button 
                        wire:click="addPage"
                        class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
                    >
                        + Add Page
                    </button>
                @else
                    @if ($pages->isNotEmpty())
                        <button 
                            wire:click="addQuestion('{{ $type }}', {{ $pages->first()->id }})"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            Add {{ ucwords(str_replace('_', ' ', $type)) }}
                        </button>
                    @endif
                @endif
            @endforeach
        </div>

    </div>
</div>
