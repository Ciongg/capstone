<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto relative">
     
        

        {{-- Back Button (Only in Preview Mode) --}}
        @include('livewire.surveys.answer-survey.partials.preview-button')

        @include('livewire.surveys.answer-survey.partials.survey-header')

        <form wire:submit.prevent="submit">
            <div x-data="{ navAction: 'submit' }">
                @php $questionNumber = 1; @endphp
                @foreach($survey->pages as $pageIndex => $page)
                <div @if($pageIndex !== $currentPage) style="display:none" @endif>
                    {{-- Page Header - pass isPreview flag --}}
                    @include('livewire.surveys.answer-survey.partials.page-header', ['page' => $page, 'isPreview' => $isPreview ?? false])
                        
                        
                        @foreach($page->questions->sortBy('order') as $question)
                            <div class="mb-8">
                                <label class="block font-medium mb-2 text-lg">
                                    {{ $questionNumber++ }}. {{ $question->question_text }}
                                    @if($question->required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                {{-- Include the appropriate question type partial --}}
                                @include('livewire.surveys.answer-survey.partials.question-types.' . $question->question_type, ['question' => $question])
                                
                                {{-- Add error display for question types that don't handle it internally --}}
                                @if(!in_array($question->question_type, ['multiple_choice', 'radio', 'likert']))
                                    @include('livewire.surveys.answer-survey.partials.question-error', ['question' => $question])
                                @endif
                            </div>
                        @endforeach

                        {{-- Navigation buttons --}}
                        @include('livewire.surveys.answer-survey.partials.navigation-buttons', [
                            'isFirstPage' => $loop->first,
                            'isLastPage' => $loop->last, 
                            'currentPage' => $pageIndex
                        ])
                    </div>
                @endforeach
            </div>
        </form>
    </div>
</div>
