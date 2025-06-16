{{-- Main container with gray background --}}
<div class="bg-gray-100 min-h-screen py-4 sm:py-8">
    <div class="max-w-7xl mx-auto space-y-6 sm:space-y-10 px-2 sm:px-4">
        {{-- Only show content when a response is available --}}
        @if($response)
            {{-- Top card with response information --}}
            <div class="bg-white shadow-xl rounded-lg sm:rounded-2xl p-4 sm:p-10 mb-6 sm:mb-8">
                {{-- Back button container --}}
                <div class="flex justify-between items-center mb-6">
                    {{-- Back button --}}
                    <button onclick="history.back()"
                       class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 flex items-center text-sm sm:text-base"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </button>
                    
                    {{-- Points Display with Diamond --}}
                    <div class="flex items-center">
                        <span class="text-gray-600 mr-2 text-sm sm:text-base">Points Earned:</span>
                        <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-2 sm:px-3 py-1 rounded-full">
                            <span class="font-bold text-white drop-shadow text-sm">{{ $survey->points_allocated ?? 0 }}</span>
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                {{-- Response header --}}
                <div class="text-center mb-6 sm:mb-8">
                    <div class="font-bold text-lg sm:text-2xl">
                        {{ $respondentUser->name ?? 'Unknown User' }}'s Response
                    </div>
                    <div class="text-gray-600 text-sm sm:text-base mt-1">
                        Survey: {{ $survey->title }}
                    </div>
                    <div class="text-gray-500 text-xs sm:text-sm mt-1">
                        Answered: {{ $response->created_at->format('Y-m-d') }} | {{ $response->created_at->format('g:i A') }}
                    </div>
                    @if($response->reported && $reportData)
                        <div class="text-red-600 font-semibold text-sm sm:text-base mt-1">
                            This Response Has Been Reported
                        </div>
                        
                        {{-- Report Details --}}
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-left">
                            <h4 class="font-bold text-red-800 mb-2">Report Details</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="font-medium text-red-700">Reason:</span>
                                    <span class="text-red-900">{{ ucfirst(str_replace('_', ' ', $reportData->reason)) }}</span>
                                </div>
                                
                                @if($reportedQuestionTitle)
                                <div>
                                    <span class="font-medium text-red-700">Reported Question:</span>
                                    <span class="text-red-900">{{ $reportedQuestionTitle }}</span>
                                </div>
                                @endif
                                
                                <div>
                                    <span class="font-medium text-red-700">Details:</span>
                                    <div class="text-red-900 whitespace-pre-wrap bg-red-100 p-2 rounded mt-1">{{ $reportData->details }}</div>
                                </div>
                                
                                <div class="text-xs text-red-600 mt-2">
                                    Reported on: {{ $reportData->created_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Respondent statistics grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    {{-- Demographic Matched Box --}}
                    <div class="bg-gray-100 font-bold rounded-lg shadow p-4 sm:p-6 relative min-h-[120px]">
                        <span class="text-base sm:text-lg font-semibold text-gray-500 mb-3 block text-left">Demographic Matched</span>
                        @if($respondentUser)
                            <div class="flex items-start">
                                <div class="flex flex-wrap gap-2 flex-1">
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
                            @php
                                $scoreColorClass = match (true) {
                                    $trustScore === 100 => 'text-[#03b8ff]',
                                    $trustScore >= 80 => 'text-yellow-500',
                                    default => 'text-red-500',
                                };
                            @endphp
                            <div class="flex items-center justify-between">
                                <span @class(['text-3xl sm:text-5xl font-bold', $scoreColorClass])>
                                    {{ $trustScore }}
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
                        <div class="flex items-center justify-between">
                            <span class="text-3xl sm:text-5xl font-bold" style="color: #03b8ff;">{{ $timeCompleted ?? "0" }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 sm:w-14 sm:h-14 text-gray-500 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>
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
                            <div class="{{ $reportData && $reportData->question_id == $question['id'] ? 'bg-red-50 border-2 border-red-200 rounded-lg p-4' : '' }}">
                                {{-- Highlight reported question --}}
                                @if($reportData && $reportData->question_id == $question['id'])
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-red-600 font-semibold text-sm">REPORTED QUESTION</span>
                                    </div>
                                @endif
                                
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
                No response found.
            </div>
        @endif
    </div>
</div>
