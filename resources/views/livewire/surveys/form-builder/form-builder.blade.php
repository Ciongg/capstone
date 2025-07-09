<div
    class="bg-gray-100 min-h-screen p-4 sm:p-6 md:p-8 lg:p-16"
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
       if (value !== null && selectedQuestionId === null && value !== oldValue) {
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

    <!-- Save Status Indicator -->
    <div class="fixed top-4 right-4 z-50">
        @if($saveStatus === 'saving')
            <div class="flex items-center space-x-2 bg-blue-100 text-blue-700 px-3 py-2 rounded-lg shadow-md">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm font-medium">{{ $saveMessage }}</span>
            </div>
        @elseif($saveStatus === 'saved')
            <div 
                class="flex items-center space-x-2 bg-green-100 text-green-700 px-3 py-2 rounded-lg shadow-md"
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 2000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium">{{ $saveMessage }}</span>
            </div>
        @endif
    </div>

    <!-- Survey Locked Warning -->
    @include('livewire.surveys.form-builder.partials.locked-warning', ['survey' => $survey])

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
    
    
    <!-- Sticky Survey Navbar - Always accessible -->
    @include('livewire.surveys.form-builder.partials.survey-navbar')

    <!-- Modal -->
    <x-modal name="survey-settings-modal-{{ $survey->id }}" title="Survey Settings">
        {{-- Add a wire:key that changes when the survey is updated --}}
        <livewire:surveys.form-builder.modal.survey-settings-modal 
            :survey="$survey" 
            :key="'settings-modal-' . $survey->id . '-' . $survey->updated_at->timestamp" 
        />
    </x-modal>

    <!-- Wrapper for survey content - disabled when survey is locked or ongoing -->
    <div @class([
        'relative', // Always relative
        'opacity-50 pointer-events-none select-none' => $survey->is_locked || $survey->status === 'ongoing', // Disabled when locked or ongoing
    ])>
        @if($survey->is_locked)
            <!-- Overlay message for locked surveys -->
            <div class="absolute inset-0 flex items-center justify-center z-50 pointer-events-none">
                <div class="bg-white/80 p-6 rounded-lg shadow-lg border-2 border-red-300 max-w-lg text-center">
                    <h3 class="text-xl font-bold text-red-600">Survey Locked</h3>
                    <p class="mt-2 text-gray-700">This survey has been locked by an administrator and cannot be edited.</p>
                </div>
            </div>
        @elseif($survey->status === 'ongoing')
            <!-- Overlay message for ongoing surveys -->
            <div class="absolute inset-0 flex items-center justify-center z-50 pointer-events-none">
                <div class="bg-white/80 p-6 rounded-lg shadow-lg border-2 border-amber-300 max-w-lg text-center">
                    <h3 class="text-xl font-bold text-amber-600">Survey Ongoing</h3>
                    <p class="mt-2 text-gray-700">This survey has received responses and cannot be edited.</p>
                    <p class="mt-1 text-sm text-gray-600">View responses using the buttons in the navbar above.</p>
                </div>
            </div>
        @endif

        <!-- Sticky Page Selector -->
        @include('livewire.surveys.form-builder.partials.page-navigation')

        <!-- Pages and Questions the form itself-->
        <div class="min-w-[300px]">
            @foreach ($pages as $page)
                <div id="page-container-{{ $page->id }}" class="bg-white shadow-md rounded-lg p-3 sm:p-6 mb-6 overflow-x-auto" wire:key="page-{{ $page->id }} ">
                    @include('livewire.surveys.form-builder.partials.page-header', ['page' => $page])

                    @php
                        $sortedQuestions = $page->questions->sortBy('order')->values();
                    @endphp

                    @foreach ($sortedQuestions as $qIndex => $question)
                        <div
                            id="question-{{ $question->id }}"
                            wire:key="question-{{ $page->id }}-{{ $question->id }}"
                            :class="{ 'border-2 border-blue-500': selectedQuestionId === {{ $question->id }} }"
                            class="p-3 sm:p-4 bg-gray-50 rounded-lg shadow-sm mb-4 transition hover:shadow-md relative group"
                        >
                            {{-- Clickable Overlay --}}
                            <div
                                x-show="selectedQuestionId !== {{ $question->id }}"
                                x-on:click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $page->id }}; $wire.selectQuestion({{ $question->id }})"
                                class="absolute inset-0 bg-transparent hover:bg-blue-500/5 z-10 rounded-lg transition-all duration-200 cursor-pointer"
                            ></div>

                            {{-- Question Content --}}
                            <div class="flex mb-4 justify-between items-start">
                                <div class="flex items-start space-x-2 w-full pr-4 sm:pr-16">
                                    <span class="text-gray-500 font-bold self-start pt-2">Q{{ $question->order }}.</span>
                                    <textarea
                                        id="question-text-{{ $question->id }}"
                                        wire:model.defer="questions.{{ $question->id }}.question_text"
                                        wire:blur="updateQuestion({{ $question->id }})"
                                        placeholder="Enter question text"
                                        onfocus="this.select()"
                                        class="w-full p-2 border border-gray-300 rounded resize-none overflow-hidden"
                                        rows="1"
                                        style="field-sizing: content; min-height: 2.5em; max-height: 200em;"
                                    ></textarea>
                                </div>
                                <span class="ml-4 whitespace-nowrap text-sm text-gray-500 self-start pt-2 hidden sm:block">
                                    {{ $question->question_type === 'radio' ? 'Single Choice' : ucwords(str_replace('_', ' ', $question->question_type)) }}
                                </span>
                            </div>

                            {{-- Include Question Type Specific Fields --}}
                            @include('livewire.surveys.form-builder.partials.question-types.'.$question->question_type, ['question' => $question])

                            {{-- Include Type Picker and Delete Button (when selected) --}}
                            <div x-show="selectedQuestionId === {{ $question->id }}" x-cloak class="mt-4 space-y-4">
                                @include('livewire.surveys.form-builder.partials.type-picker', [
                                    'context' => 'question',
                                    'id' => $question->id,
                                    'order' => $question->order,
                                    'questionTypes' => $questionTypes
                                ])
                            </div>

                            {{-- Include Question Settings --}}
                            @include('livewire.surveys.form-builder.partials.question-settings', [
                                'question' => $question,
                                'qIndex' => $qIndex,
                                'totalQuestions' => $sortedQuestions->count()
                            ])
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div> <!-- End of wrapper for interactive elements -->
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('clearSaveStatus', () => {
            setTimeout(() => {
                Livewire.find('{{ $_instance->getId() }}').set('saveStatus', '');
            }, 2000);
        });
    });
</script>
@endpush
