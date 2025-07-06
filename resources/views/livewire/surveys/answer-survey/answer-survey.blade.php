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
                                <div class="flex justify-between items-start mb-2">
                                    <!-- Question container with grid layout -->
                                    <div class="grid grid-cols-[30px_1fr] gap-2 items-start w-full">
                                        <!-- Question number in fixed width column -->
                                        <div class="font-medium text-lg">
                                            {{ $questionNumber++ }}.
                                             @if($question->required)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Question text with preserved line breaks, aligned to top -->
                                        <div class="font-medium text-lg">
                                            <div class="{{ isset($translatedQuestions[$question->id]) ? 'text-blue-600' : '' }}">
                                                @if(isset($translatedQuestions[$question->id]))
                                                    {!! nl2br(e($translatedQuestions[$question->id])) !!}
                                                @else
                                                    {!! nl2br(e($question->question_text)) !!}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Translation dropdown component with question-specific loading states -->
                                    <div class="inline-block flex-shrink-0 ml-2 relative" wire:key="translate-{{ $question->id }}">
                                        
                                        <!-- Loading spinner: Shows only for this specific question -->
                                        <div wire:loading wire:target="translateQuestion({{ $question->id }})">
                                            <div class="p-1">
                                                <svg class="animate-spin w-4 h-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    
                                        <!-- Dropdown component: Hidden when loading -->
                                        <div wire:loading.remove wire:target="translateQuestion({{ $question->id }})">
                                            <x-question-translate-dropdown :question-id="$question->id" />
                                        </div>

                                    </div>
                                </div>
                                
                                {{-- Include the appropriate question type partial --}}
                                @include('livewire.surveys.answer-survey.partials.question-types.' . $question->question_type, ['question' => $question])
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

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Handle successful survey submission
        Livewire.on('surveySubmitted', (eventData) => {
            // Extract data from the event (this is the key fix)
            const data = eventData[0] || eventData;
            
            // Check if points are available and greater than 0
            const pointsMessage = data.points > 0 
                ? `<div class="mt-2 mb-4">
                     <div class="text-lg font-bold text-center">You earned</div>
                     <div class="flex items-center justify-center gap-2">
                       <span class="text-3xl font-bold text-green-500">${data.points}</span>
                       <svg class="w-6 h-6 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                         <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z"></path>
                       </svg>
                     </div>
                     <div class="text-center text-sm text-gray-500">points for completing "${data.surveyName}"</div>
                   </div>`
                : '<div class="my-4">Thank you for your participation!</div>';

            // Show the success alert
            Swal.fire({
                title: data.title || 'Survey Completed!',
                html: `
                    <div class="p-2">
                        ${pointsMessage}
                    </div>
                `,
                icon: 'success',
                confirmButtonText: '<i class="fas fa-home mr-2"></i> Back to Feed',
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false,
                customClass: {
                    confirmButton: 'px-5 py-3 text-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('feed.index') }}";
                }
            });
        });

        // Handle survey submission errors
        Livewire.on('surveySubmissionError', (eventData) => {
            const data = eventData[0] || eventData;
            
            // Determine button text and color based on error type
            let buttonText = '<i class="fas fa-home mr-2"></i> Back to Feed';
            let buttonColor = '#3085d6';
            
            if (data.type === 'already_responded') {
                buttonText = '<i class="fas fa-chart-bar mr-2"></i> View My Responses';
                buttonColor = '#17a2b8';
            } else if (data.type === 'expired' || data.type === 'limit_reached') {
                buttonText = '<i class="fas fa-search mr-2"></i> Find Other Surveys';
                buttonColor = '#6c757d';
            }

            // Show the error alert
            Swal.fire({
                title: data.title || 'Submission Failed',
                html: `
                    <div class="p-2">
                        <div class="mb-4 text-gray-600">
                            ${data.message || 'Your response could not be submitted.'}
                        </div>
                        ${data.type === 'expired' ? 
                            '<div class="text-sm text-red-500"><i class="fas fa-clock mr-1"></i> Survey ended on: ' + 
                            (new Date('{{ $survey->end_date }}').toLocaleDateString()) + '</div>' : ''
                        }
                        ${data.type === 'limit_reached' ? 
                            '<div class="text-sm text-orange-500"><i class="fas fa-users mr-1"></i> Maximum responses: {{ $survey->target_respondents ?? "N/A" }}</div>' : ''
                        }
                    </div>
                `,
                icon: data.icon || 'error',
                confirmButtonText: buttonText,
                confirmButtonColor: buttonColor,
                allowOutsideClick: false,
                customClass: {
                    confirmButton: 'px-5 py-3 text-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (data.type === 'already_responded') {
                        // Redirect to user's responses page if it exists
                        window.location.href = "{{ route('feed.index') }}";
                    } else {
                        // Default redirect to feed
                        window.location.href = "{{ route('feed.index') }}";
                    }
                }
            });
        });
    });
</script>
@endpush

