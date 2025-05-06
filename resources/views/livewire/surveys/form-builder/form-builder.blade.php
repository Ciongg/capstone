<div
    class="bg-gray-100 min-h-screen p-6"
    x-data="{ selectedQuestionId: @entangle('selectedQuestionId').live, activePageId: @entangle('activePageId').live }"
    x-init="
        $watch('selectedQuestionId', (value) => {
            if (value !== null) {
                $nextTick(() => {
                    const element = document.getElementById('question-' + value);
                    if (element) {
                        const elementRect = element.getBoundingClientRect();
                        const absoluteElementTop = elementRect.top + window.scrollY;
                        const middle = absoluteElementTop - (window.innerHeight / 2) + (element.offsetHeight / 2);
                        window.scrollTo({ top: middle, behavior: 'smooth' });
                    } else {
                        console.warn('Element not found for scrolling: question-' + value);
                    }
                });
            }
        });

        $watch('activePageId', (value, oldValue) => {
            if (value !== null && selectedQuestionId === null) {
                $nextTick(() => {
                    const pageElement = document.getElementById('page-container-' + value);
                    if (pageElement) {
                        const elementRect = pageElement.getBoundingClientRect();
                        const absoluteElementTop = elementRect.top + window.scrollY;
                        const middle = absoluteElementTop - (window.innerHeight / 3);
                        window.scrollTo({ top: Math.max(0, middle), behavior: 'smooth' });
                    } else {
                        console.warn('Element not found for scrolling: page-container-' + value);
                    }
                });
            }
        });

        document.addEventListener('livewire:initialized', () => {
            @this.on('questionAdded', ({ questionId, pageId }) => {
                selectedQuestionId = questionId;
                activePageId = pageId;
                console.log('Alpine received questionAdded:', questionId, pageId);
            });

            @this.on('pageAdded', ({ pageId }) => {
                activePageId = pageId;
                selectedQuestionId = null;
                console.log('Alpine received pageAdded:', pageId);
            });

            @this.on('pageSelected', ({ pageId }) => {
                selectedQuestionId = null;
                activePageId = pageId;
                console.log('Alpine received pageSelected:', pageId);
            });
        });

        window.addEventListener('scrollToPage', (event) => {
            const pageId = event.detail.pageId;
            const pageElement = document.getElementById('page-container-' + pageId);
            if (pageElement) {
                const elementRect = pageElement.getBoundingClientRect();
                const absoluteElementTop = elementRect.top + window.scrollY;
                const middle = absoluteElementTop - (window.innerHeight / 3);
                window.scrollTo({ top: Math.max(0, middle), behavior: 'smooth' });
            }
        });
    "
>

    <!-- Display Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Sticky Survey Navbar -->
    @include('livewire.surveys.form-builder.partials.survey-navbar')

    <!-- Modal -->
    <x-modal name="survey-settings-modal-{{ $survey->id }}" title="Survey Settings">
        <livewire:surveys.form-builder.modal.survey-settings-modal :survey="$survey" />
    </x-modal>

    <div class="space-y-6">

        <!-- Sticky Page Selector Container -->
        <div class="sticky top-0 z-30 bg-white shadow px-6 py-3 mb-4 rounded">
            <!-- Page Selector -->
            @if ($pages->isEmpty())
                <div class="text-center">
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
                            @click="
                                selectedQuestionId = null;
                                activePageId = {{ $page->id }};
                                $wire.setActivePage({{ $page->id }});
                                setTimeout(() => {
                                    const pageElement = document.getElementById('page-container-' + {{ $page->id }});
                                    if (pageElement) {
                                        const elementRect = pageElement.getBoundingClientRect();
                                        const absoluteElementTop = elementRect.top + window.scrollY;
                                        const middle = absoluteElementTop - (window.innerHeight / 3);
                                        window.scrollTo({ top: Math.max(0, middle), behavior: 'smooth' });
                                    }
                                }, 50);
                            "
                            type="button"
                            class="px-4 py-2 rounded cursor-pointer transition duration-150 ease-in-out"
                            :class="{
                                'bg-blue-500 text-white hover:bg-blue-600': activePageId === {{ $page->id }},
                                'bg-gray-200 text-gray-700 hover:bg-gray-300': activePageId !== {{ $page->id }}
                            }"
                        >
                            Page {{ $page->page_number }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Pages and Questions -->
        <div>
            @foreach ($pages as $page)
                <div id="page-container-{{ $page->id }}" class="bg-white shadow-md rounded-lg p-6" wire:key="page-{{ $page->id }}">
                    @include('livewire.surveys.form-builder.partials.page-header', ['page' => $page])

                    @foreach ($page->questions->sortBy('order') as $question)
                        <div
                            id="question-{{ $question->id }}"
                            wire:key="question-{{ $page->id }}-{{ $question->id }}"
                            :class="{ 'border-2 border-blue-500': selectedQuestionId === {{ $question->id }} }"
                            class="p-4 bg-gray-50 rounded-lg shadow-sm mb-4 transition hover:shadow-md cursor-pointer relative"
                        >
                            <div
                                x-show="selectedQuestionId !== {{ $question->id }}"
                                @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $page->id }}; $wire.selectQuestion({{ $question->id }})"
                                class="absolute inset-0 bg-transparent hover:bg-blue-500/5 z-10 rounded-lg transition-all duration-200 cursor-pointer"
                            ></div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2 w-full">
                                    <span class="text-gray-500 font-bold self-start pt-2">Q{{ $question->order }}.</span>
                                    <textarea
                                        id="question-text-{{ $question->id }}"
                                        x-data="{
                                            init() {
                                                $nextTick(() => this.adjustHeight());
                                            },
                                            adjustHeight() {
                                                const id = $el.id; // Get the ID
                                                $el.style.height = 'auto';
                                                const newHeight = `${$el.scrollHeight}px`;
                                                $el.style.height = newHeight;
                                                // Ensure it sets the store value
                                                Alpine.store('textareaHeights').set(id, newHeight);
                                            }
                                        }"
                                        @input="adjustHeight()"
                                        wire:model.defer="questions.{{ $question->id }}.question_text"
                                        wire:blur="updateQuestion({{ $question->id }})"
                                        placeholder="Enter question text"
                                        onfocus="this.select()"
                                        class="w-full p-2 border border-gray-300 rounded resize-none overflow-hidden"
                                        rows="1"
                                        data-autoresize
                                        :style="{ height: $store.textareaHeights.get('question-text-{{ $question->id }}') }"
                                    ></textarea>
                                </div>
                                <span class="ml-4 whitespace-nowrap text-lg text-gray-500 self-start pt-2">
                                    {{ $question->question_type === 'radio' ? 'Single Choice' : ucwords(str_replace('_', ' ', $question->question_type)) }}
                                </span>
                            </div>

                            @include('livewire.surveys.form-builder.partials.question-types.'.$question->question_type, ['question' => $question])

                            @include('livewire.surveys.form-builder.partials.question-settings', ['question' => $question])

                            <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="mt-4 space-y-4">
                                @include('livewire.surveys.form-builder.partials.type-picker', [
                                    'context' => 'question',
                                    'id' => $question->id,
                                    'order' => $question->order,
                                    'questionTypes' => $questionTypes
                                ])

                                <div class="flex justify-end">
                                    @include('livewire.surveys.form-builder.partials.delete-button', [
                                        'context' => 'question',
                                        'id' => $question->id,
                                        'confirmMessage' => 'Are you sure you want to remove this question?',
                                        'action' => 'removeQuestion'
                                    ])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
