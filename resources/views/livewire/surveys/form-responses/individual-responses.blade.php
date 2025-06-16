{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\surveys\form-responses\individual-responses.blade.php --}}
{{-- Main container with gray background --}}
<div class="bg-gray-100 min-h-screen py-4 sm:py-8">
    <div class="max-w-7xl mx-auto space-y-6 sm:space-y-10 px-2 sm:px-4">
        {{-- Only show content when a response is available --}}
        @if($currentRespondent)
            {{-- Top card with respondent navigation and summary information --}}
            <div class="bg-white shadow-xl rounded-lg sm:rounded-2xl p-4 sm:p-10 mb-6 sm:mb-8">
                {{-- Back and Report button container --}}
                <div class="flex justify-between items-center mb-6">
                    {{-- Back button to return to all responses view --}}
                    <a href="{{ route('surveys.responses', $survey->id) }}"
                       class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 flex items-center text-sm sm:text-base"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    
                    {{-- Report button to flag problematic responses --}}
                    <button
                        x-data
                        x-on:click="$dispatch('open-modal', {name : 'view-report-response-modal'})"
                        class="p-1 sm:p-2 text-red-500 hover:text-red-700"
                        type="button"
                        aria-label="Report Response"
                    >
                        <svg class="w-8 h-8 sm:w-12 sm:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </button>
                </div>
                
                {{-- Navigation controls between responses --}}
                <div class="flex items-center justify-center mb-6 sm:mb-8">
                    {{-- Previous response button --}}
                    <button
                        class="p-2 sm:p-3 rounded-full bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50"
                        wire:click="$set('current', {{ $current - 1 }})"
                        @disabled($current === 0)
                        aria-label="Previous"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    {{-- Current respondent counter with reported status --}}
                    <div class="mx-8 sm:mx-16 text-center min-w-[180px] sm:min-w-[220px]">
                        <div class="font-bold text-lg sm:text-2xl">
                            Respondent {{ $current + 1 }} of {{ $survey->responses->count() }}
                        </div>
                        @if($currentRespondent && $currentRespondent->reported)
                            <div class="text-red-600 font-semibold text-sm sm:text-base mt-1">
                                Reported Response
                            </div>
                        @endif
                    </div>
                    
                    {{-- Next response button --}}
                    <button
                        class="p-2 sm:p-3 rounded-full bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50"
                        wire:click="$set('current', {{ $current + 1 }})"
                        @disabled($current === $survey->responses->count() - 1)
                        aria-label="Next"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                {{-- Respondent statistics grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    {{-- Demographic Matched Box --}}
                    <div class="bg-gray-100 font-bold rounded-lg shadow p-4 sm:p-6 relative min-h-[120px]">
                        <span class="text-base sm:text-lg font-semibold text-gray-500 mb-3 block text-left">Demographic Matched</span>
                        @if($respondentUser)
                            <div class="flex items-start">
                                <div class="flex flex-wrap gap-2 flex-1 pr-16">
                                    {{-- Show matched demographic tags based on status --}}
                                    @if($matchedSurveyTagsInfo['status'] === 'has_matches')
                                        @foreach ($matchedSurveyTagsInfo as $tagInfo)
                                            @if(is_array($tagInfo) && isset($tagInfo['matched']) && $tagInfo['matched'])
                                                <span class="px-2 py-1 text-xs sm:text-sm font-medium rounded-full whitespace-nowrap bg-green-200 text-green-800 shadow-md break-words">
                                                    {{ $tagInfo['name'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @elseif($matchedSurveyTagsInfo['status'] === 'none_matched')
                                        <span class="text-xs sm:text-sm text-gray-500 italic">None matched.</span>
                                    @elseif($matchedSurveyTagsInfo['status'] === 'no_target_demographics')
                                        <span class="text-xs sm:text-sm text-gray-500 italic">No target demographics set.</span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- View all demographics modal trigger --}}
                            <div class="absolute right-2 sm:right-4 bottom-2">
                                <button
                                    x-data
                                    x-on:click="$dispatch('open-modal', {name : 'view-all-demographic-modal'})"
                                    class="text-blue-600 underline font-semibold hover:text-blue-800 text-xs sm:text-sm"
                                    type="button"
                                >
                                    View All
                                </button>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-xl sm:text-2xl font-bold text-gray-600">--</span>
                            </div>
                        @endif
                    </div>

                    {{-- Trust Score Box --}}
                    <div class="bg-gray-100 rounded-lg font-bold shadow p-4 sm:p-6 min-h-[120px]">
                        <span class="text-base sm:text-lg font-semibold text-gray-500 mb-3 block text-left">Trust Score</span>
                        @if($respondentUser)
                            {{-- Color coding based on trust score value --}}
                            @php
                                $scoreColorClass = match (true) {
                                    $trustScore === 100 => 'text-[#03b8ff]',
                                    $trustScore >= 80 => 'text-yellow-500',
                                    default => 'text-red-500',
                                };
                                // Format trust score without unnecessary trailing zeros
                                $formattedTrustScore = rtrim(rtrim(number_format($trustScore, 2, '.', ''), '0'), '.');
                            @endphp
                            <div class="flex items-center justify-between">
                                <span @class(['text-3xl sm:text-5xl font-bold', $scoreColorClass])>
                                    {{ $formattedTrustScore }}
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 sm:w-14 sm:h-14 text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-xl sm:text-2xl font-bold text-gray-600">--</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 sm:w-14 sm:h-14 text-gray-300 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Time Completed Box --}}
                    <div class="bg-gray-100 rounded-lg font-bold shadow p-4 sm:p-6 min-h-[120px]">
                        <span class="text-base sm:text-lg font-semibold text-gray-500 mb-3 block text-left">Time Completed</span>
                        @if($timeCompleted)
                            <div class="flex items-center justify-between">
                                {{-- Display the actual time from the $timeCompleted variable instead of hardcoded "4:34" --}}
                                <span class="text-3xl sm:text-5xl font-bold" style="color: #03b8ff;">{{ $timeCompleted }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 sm:w-14 sm:h-14 text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-2xl sm:text-3xl font-bold" style="color: #03b8ff;">--</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 sm:w-14 sm:h-14 text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Demographics modal component - moved outside the conditional --}}
                <x-modal name="view-all-demographic-modal" title="All Demographics">
                    <livewire:surveys.form-responses.modal.view-all-demographic-modal 
                        :survey="$survey" 
                        :response="$currentRespondent" 
                        :user="$respondentUser" 
                        :key="'demographic-modal-'.$current" 
                    />
                </x-modal>

                {{-- Report Response Modal with unique key --}}
                <x-modal name="view-report-response-modal" title="Report Response">
                    <livewire:surveys.form-responses.modal.view-report-response-modal 
                        :response="$currentRespondent" 
                        :survey="$survey" 
                        :key="'report-modal-'.$current.'-'.$currentRespondent->id" 
                    />
                </x-modal>
            </div>

            {{-- Survey pages with questions and answers --}}
            @foreach($pagesWithProcessedAnswers as $page)
                <div class="bg-white shadow-xl rounded-lg sm:rounded-2xl p-4 sm:p-10 space-y-8 sm:space-y-12 mb-6 sm:mb-10">
                    {{-- Page header with title and subtitle --}}
                    <div class="mb-4">
                        <span class="text-xl sm:text-2xl font-bold">{{ $page['title'] }}</span>
                        @if($page['subtitle'])
                            <div class="text-gray-500 text-base sm:text-lg">{{ $page['subtitle'] }}</div>
                        @endif
                        <hr class="my-3 border-gray-300">
                    </div>
                    
                    {{-- Questions and answers list --}}
                    <div class="space-y-6 sm:space-y-8">
                        @foreach($page['questions'] as $question)
                            <div>
                                {{-- Question text with numbering --}}
                                <div class="font-semibold text-lg sm:text-xl mb-2 pl-2 break-words">
                                    {{ $question['order'] ?? $loop->iteration }}. {{ $question['question_text'] }}
                                </div>

                                {{-- Display answer based on question type --}}
                                @if(in_array($question['question_type'], ['multiple_choice', 'radio']))
                                    {{-- Choice-based question (Multiple choice or Radio) --}}
                                    <div class="space-y-3 pl-4 sm:pl-8">
                                        @foreach($question['choices'] as $choice)
                                            <div class="flex items-start space-x-3">
                                                {{-- Selected/unselected circle indicator --}}
                                                @if($choice['is_selected'])
                                                    <span class="inline-block w-6 h-6 sm:w-7 sm:h-7 bg-gray-500 rounded-full border-2 border-gray-500 flex-shrink-0 mt-0.5"></span>
                                                @else
                                                    <span class="inline-block w-6 h-6 sm:w-7 sm:h-7 bg-white rounded-full border-2 border-gray-400 flex-shrink-0 mt-0.5"></span>
                                                @endif
                                                
                                                {{-- Choice text, bold if selected --}}
                                                <span class="{{ $choice['is_selected'] ? 'font-semibold text-gray-700' : 'text-gray-400' }} text-base sm:text-lg break-words">
                                                    {{ $choice['choice_text'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                        
                                        {{-- Display "Other" text if an "Other" choice is selected --}}
                                        @php
                                            $otherChoice = collect($question['choices'])->first(function($choice) {
                                                return $choice['is_selected'] && $choice['is_other'] == 1;
                                            });
                                        @endphp
                                        
                                        {{-- Show "Other" text when available --}}
                                        @if($otherChoice && isset($question['other_text']) && $question['other_text'])
                                            <div class="flex items-center space-x-3 mt-1">
                                                
                                                <span class="text-lg font-semibold text-gray-700">
                                                   <span class="text-gray-500"> Other Response: </span>  {{ $question['other_text'] }}
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Debug message when "Other" is selected but no text provided --}}
                                        @if($otherChoice && empty($question['other_text']))
                                            <div class="flex items-center space-x-3 mt-1">
                                                <span class="inline-block w-7 h-7 opacity-0"></span>
                                                <span class="text-lg text-yellow-600 italic">
                                                    (No text provided for "Other" option)
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($question['question_type'] === 'likert')
                                    {{-- Likert scale question --}}
                                    <div class="overflow-x-auto mt-2 ml-4 sm:ml-8">
                                        <table class="min-w-full text-center border border-gray-300 text-sm sm:text-base">
                                            {{-- Column headers --}}
                                            <thead class="border-b border-gray-300">
                                                <tr>
                                                    <th class="bg-gray-50 w-52 border-r border-gray-300"></th>
                                                    @foreach($question['likert_columns'] as $column)
                                                        <th class="bg-gray-50 px-4 py-2 text-base font-medium border-r border-gray-300 last:border-r-0">{{ $column }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            {{-- Rows with selections --}}
                                            <tbody>
                                                @foreach($question['likert_rows'] as $rowIndex => $row)
                                                    {{-- loop is a variable found in for each loops checks the current index of the loop--}}
                                                    @php
                                                        $rowBg = $loop->even ? 'bg-gray-50' : 'bg-white';
                                                        $selectedColumnIndex = $question['likert_answer_data'][$rowIndex] ?? null;
                                                    @endphp
                                                    <tr class="{{ $rowBg }} border-b border-gray-300 last:border-b-0">
                                                        <td class="px-4 py-2 text-left text-base border-r border-gray-300">{{ $row }}</td>
                                                        @foreach($question['likert_columns'] as $colIndex => $column)
                                                            <td class="px-4 py-2 border-r border-gray-300 last:border-r-0">
                                                                {{-- Selected/unselected indicator --}}
                                                                @if($selectedColumnIndex !== null && $colIndex == $selectedColumnIndex)
                                                                    <span class="inline-block w-5 h-5 bg-gray-500 rounded-full"></span>
                                                                @else
                                                                    <span class="inline-block w-5 h-5 border-2 border-gray-300 rounded-full"></span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    {{-- Other question types (essay, short_text, date, rating) --}}
                                    <div class="bg-gray-100 border rounded px-3 sm:px-4 py-2 sm:py-3 text-base sm:text-lg text-gray-800 ml-4 sm:ml-8 break-words">
                                        @if($question['question_type'] === 'rating')
                                            {{-- Star rating display --}}
                                            <div class="flex items-center flex-wrap">
                                                @for ($i = 1; $i <= $question['stars']; $i++)
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $i <= (int)$question['single_answer'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-gray-600">({{ $question['single_answer'] }})</span>
                                            </div>
                                        @else
                                            {{-- Text-based answer (essay, short_text, date) --}}
                                            {{ $question['single_answer'] }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            {{-- Display when no response is available --}}
            <div class="text-gray-500 text-lg sm:text-xl text-center py-10">
                @if($survey->responses->isEmpty())
                    No responses yet for this survey.
                @else
                    No response selected or available.
                @endif
            </div>
        @endif
    </div>
</div>