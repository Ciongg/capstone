<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto space-y-10 px-4">

        @if($currentRespondent)
            {{-- Top Navigation & Respondent Info --}}
            <div class="bg-white shadow-xl rounded-2xl p-10 mb-8">
                <div class="flex items-center justify-center mb-8">
                    <button
                        class="p-3 rounded-full bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50"
                        wire:click="$set('current', {{ $current - 1 }})"
                        @disabled($current === 0)
                        aria-label="Previous"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="mx-8 font-bold text-2xl text-center min-w-[220px]">
                        Respondent {{ $current + 1 }} of {{ $survey->responses->count() }}
                    </div>
                    <button
                        class="p-3 rounded-full bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50"
                        wire:click="$set('current', {{ $current + 1 }})"
                        @disabled($current === $survey->responses->count() - 1)
                        aria-label="Next"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Demographic Matched Box --}}
                    <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center min-h-[120px] relative">
                        <span class="text-lg font-semibold text-gray-700 mb-3">Demographic Matched</span>
                        @if($respondentUser)
                            <div class="flex flex-wrap justify-center gap-4 w-full px-4">
                                @if($matchedSurveyTagsInfo['status'] === 'has_matches')
                                    @foreach ($matchedSurveyTagsInfo as $tagInfo)
                                        @if(is_array($tagInfo) && $tagInfo['matched'])
                                            <span class="px-4 py-1.5 text-base font-medium rounded-full whitespace-nowrap bg-green-200 text-green-800 shadow-md">
                                                {{ $tagInfo['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                @elseif($matchedSurveyTagsInfo['status'] === 'none_matched')
                                    <span class="text-sm text-gray-500 italic">None matched.</span>
                                @elseif($matchedSurveyTagsInfo['status'] === 'no_target_demographics')
                                    <span class="text-sm text-gray-500 italic">No target demographics set.</span>
                                @endif
                            </div>
                            {{-- View All button at the bottom right --}}
                            <div class="absolute left-0 right-0 bottom-2 flex justify-end pr-4">
                                <button
                                    x-data
                                    x-on:click="$dispatch('open-modal', {name : 'view-all-demographic-modal'})"
                                    class="text-blue-600 underline font-semibold hover:text-blue-800 text-sm"
                                    type="button"
                                >
                                    View All
                                </button>
                            </div>
                            {{-- Modal --}}
                            <x-modal name="view-all-demographic-modal" title="All Demographics">
                                <livewire:surveys.form-responses.modal.view-all-demographic-modal :survey="$survey" :user="$respondentUser" />
                            </x-modal>
                        @else
                            <span class="text-2xl font-bold text-gray-600">--</span>
                        @endif
                    </div>

                    {{-- Trust Score Box --}}
                    <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center min-h-[100px]">
                        <span class="text-lg font-semibold text-gray-700 mb-2">Trust Score</span>
                        @if($respondentUser)
                            @php
                                $scoreColorClass = match (true) {
                                    $trustScore === 100 => 'text-blue-500',
                                    $trustScore < 60 => 'text-red-500',
                                    default => 'text-yellow-500',
                                };
                            @endphp
                            <span @class(['text-3xl font-bold', $scoreColorClass])>
                                {{ $trustScore }}/100
                            </span>
                        @else
                            <span class="text-2xl font-bold text-gray-600">--</span>
                        @endif
                    </div>

                    {{-- Time Completed Box --}}
                    <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center min-h-[100px]">
                        <span class="text-lg font-semibold text-gray-700 mb-2">Time Completed</span>
                        @if($timeCompleted)
                             <span class="text-xl font-bold text-gray-600 text-center">{{ $timeCompleted }}</span>
                        @else
                            <span class="text-2xl font-bold text-gray-600">--:--</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Survey Pages, Questions, and Answers --}}
            @foreach($pagesWithProcessedAnswers as $page)
                <div class="bg-white shadow-xl rounded-2xl p-10 space-y-12 mb-10">
                    <div class="mb-4">
                        <span class="text-2xl font-bold">{{ $page['title'] }}</span>
                        @if($page['subtitle'])
                            <div class="text-gray-500 text-lg">{{ $page['subtitle'] }}</div>
                        @endif
                        <hr class="my-3 border-gray-300">
                    </div>
                    <div class="space-y-8">
                        @foreach($page['questions'] as $question)
                            <div>
                                <div class="font-semibold text-xl mb-2 pl-2">
                                    {{ $question['order'] ?? $loop->iteration }}. {{ $question['question_text'] }}
                                </div>

                                @if(in_array($question['question_type'], ['multiple_choice', 'radio']))
                                    <div class="space-y-3 pl-8">
                                        @foreach($question['choices'] as $choice)
                                            <div class="flex items-center space-x-3">
                                                @if($choice['is_selected'])
                                                    <span class="inline-block w-7 h-7 bg-gray-500 rounded-full border-2 border-gray-500"></span>
                                                @else
                                                    <span class="inline-block w-7 h-7 bg-white rounded-full border-2 border-gray-400"></span>
                                                @endif
                                                <span class="{{ $choice['is_selected'] ? 'font-semibold text-gray-700' : 'text-gray-400' }} text-lg">
                                                    {{ $choice['choice_text'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question['question_type'] === 'likert')
                                    <div class="overflow-x-auto mt-2 ml-8">
                                        <table class="min-w-full text-center border border-gray-300">
                                            <thead class="border-b border-gray-300">
                                                <tr>
                                                    <th class="bg-gray-50 w-52 border-r border-gray-300"></th>
                                                    @foreach($question['likert_columns'] as $column)
                                                        <th class="bg-gray-50 px-4 py-2 text-base font-medium border-r border-gray-300 last:border-r-0">{{ $column }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
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
                                @else {{-- Handle other types like essay, short_text, date, rating --}}
                                    <div class="bg-gray-100 border rounded px-4 py-3 text-lg text-gray-800 ml-8">
                                        @if($question['question_type'] === 'rating')
                                            <div class="flex items-center">
                                                @for ($i = 1; $i <= $question['stars']; $i++)
                                                    <svg class="w-6 h-6 {{ $i <= (int)$question['single_answer'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-gray-600">({{ $question['single_answer'] }})</span>
                                            </div>
                                        @else
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
            <div class="text-gray-500 text-xl text-center py-10">
                @if($survey->responses->isEmpty())
                    No responses yet for this survey.
                @else
                    No response selected or available.
                @endif
            </div>
        @endif
    </div>
</div>
