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
                                

                            </div>
                        @endforeach

                        {{-- Navigation buttons --}}
                        @include('livewire.surveys.answer-survey.partials.navigation-buttons', [
                            //$loop is based on the foreach loop, it provides information about the current iteration
                            'isFirstPage' => $loop->first, //sends true if this is the first page
                            'isLastPage' => $loop->last,  //sends true if this is the last page
                            'currentPage' => $pageIndex //sends the current page index
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
    });
</script>
@endpush
