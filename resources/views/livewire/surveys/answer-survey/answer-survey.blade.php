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

                        {{-- Navigation buttons - pass loading targets for spinners --}}
                        @include('livewire.surveys.answer-survey.partials.navigation-buttons', [
                            'isFirstPage' => $loop->first,
                            'isLastPage' => $loop->last,
                            'currentPage' => $pageIndex,
                            'wireTarget' => 'submit'
                        ])
                    </div>
                @endforeach
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if this is a guest accessing a survey
        var hasGuestAccess = {{ session()->has('guest_survey_access') ? 'true' : 'false' }};
        
        if (hasGuestAccess) {
            Swal.fire({
                title: 'Continue as Guest?',
                html: `
                    <div class="text-left">
                        <p class="mb-3">You are not currently logged in. You can continue to answer this survey, but:</p>
                        <ul class="list-disc pl-5 mb-3 space-y-1">
                            <li>Your responses will still be recorded</li>
                            <li>You won't earn any points or rewards</li>
                        </ul>
                        <p>Create an account to start earning rewards!</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Continue as Guest',
                cancelButtonText: 'Register/Login'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    // Redirect to register page with return URL
                    window.location.href = '/register?redirect=' + encodeURIComponent(window.location.href);
                }
            });
        }
    });

    document.addEventListener('livewire:initialized', () => {
        // Listen for validation errors event
        Livewire.on('showValidationAlert', () => {
            Swal.fire({
                title: 'Missing Required Fields',
                text: 'Please complete all required fields before proceeding.',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded'
                }
            });
        });
        
        // Handle successful survey submission
        Livewire.on('surveySubmitted', (eventData) => {
            // Extract data from the event
            const data = eventData[0] || eventData;
            
            // Log data for debugging
            console.log("Survey submission data:", data);
            
            // Check if user is a guest (multiple detection methods)
            const isGuestUser = data.isGuest === true || 
                               (data.points === 0 && data.xp === 0) ||
                               {{ Auth::check() ? 'false' : 'true' }};
            
            console.log("Is guest user:", isGuestUser);
            
            // Different HTML based on whether user is a guest or not
            let contentHtml;
            
            if (isGuestUser) {
                // Guest user message
                contentHtml = `
                    <div class="p-2">
                        <div class="mb-4 text-center">
                            <p class="text-lg mb-4">Thank you for completing "${data.surveyName}" as a guest.</p>
                            <p class="text-md text-red-500 mb-6">Unfortunately, guest users don't earn any rewards.</p>
                            <p class="text-md mb-4">Create an account to start earning rewards from future surveys!</p>
                            <div class="mt-4">
                                <a href="/register" class="px-5 py-2 bg-green-500 hover:bg-green-600 text-white rounded shadow-sm">
                                    Create an Account
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Authenticated user message with points and XP
                const pointsBox = data.points > 0 ? `
                    <div class="flex items-center justify-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-4 py-2 rounded-full shadow-lg mx-auto w-fit">
                        <span class="font-bold text-white drop-shadow text-lg">${data.points}</span>
                        <svg class="w-6 h-6 text-white ml-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </svg>
                    </div>
                ` : '';
                const xpBox = `<div class="flex items-center justify-center bg-blue-100 text-blue-700 px-4 py-2 rounded-full shadow mx-auto w-fit font-bold text-lg">+100 XP</div>`;
                contentHtml = `
                    <div class="p-2">
                        <div class="flex flex-col items-center justify-center">
                            <div class="flex flex-row items-center justify-center gap-4 mb-2">
                                ${pointsBox}
                                ${xpBox}
                            </div>
                            <div class="text-center text-base text-gray-600 mt-2">
                                for completing "${data.surveyName}"
                            </div>
                        </div>
                    </div>
                `;
            }

            // Different buttons based on user type
            const confirmButtonText = isGuestUser ? 'Back to Home' : '<i class="fas fa-home mr-2"></i> Back to Feed';
            const confirmButtonHref = isGuestUser ? "/" : "{{ route('feed.index') }}";

            // Show the success alert
            Swal.fire({
                title: data.title || 'Survey Completed!',
                html: contentHtml,
                icon: 'success',
                confirmButtonText: confirmButtonText,
                confirmButtonColor: '#3b82f6', 
                allowOutsideClick: false,
                customClass: {
                    confirmButton: 'px-5 py-3 text-lg bg-blue-500 hover:bg-blue-600 text-white rounded',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = confirmButtonHref;
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

