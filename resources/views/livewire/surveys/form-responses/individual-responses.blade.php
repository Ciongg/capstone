<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto space-y-10 px-4">

        @php
            $response = $survey->responses[$current] ?? null;
        @endphp

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
                    @disabled($current === count($survey->responses)-1)
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
                    @if($response && $response->user)
                        @php
                            $response->user->loadMissing('tags'); 
                            $survey->loadMissing('tags');
                            $respondentTagIds = $response->user->tags->pluck('id')->toArray();
                            $surveyTags = $survey->tags;
                        @endphp
                        {{-- Only show matched tags here --}}
                        <div class="flex flex-wrap justify-center gap-4 w-full px-4">
                            @php $matchedCount = 0; @endphp
                            @forelse ($surveyTags as $surveyTag)
                                @php
                                    $matchesRespondent = in_array($surveyTag->id, $respondentTagIds);
                                @endphp
                                @if($matchesRespondent)
                                    @php $matchedCount++; @endphp
                                    <span class="px-4 py-1.5 text-base font-medium rounded-full whitespace-nowrap bg-green-200 text-green-800 shadow-md">
                                        {{ $surveyTag->name }}
                                    </span>
                                @endif
                            @empty
                                <span class="text-sm text-gray-500 italic">No target demographics set.</span>
                            @endforelse
                            @if($matchedCount === 0 && $surveyTags->isNotEmpty())
                                <span class="text-sm text-gray-500 italic">None matched.</span>
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
                            <livewire:surveys.form-responses.modal.view-all-demographic-modal :survey="$survey" :user="$response->user" />
                        </x-modal>
                    @else
                        <span class="text-2xl font-bold text-gray-600">--</span>
                    @endif
                </div>

                {{-- Trust Score Box --}}
                <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center min-h-[100px]">
                    <span class="text-lg font-semibold text-gray-700 mb-2">Trust Score</span>
                    {{-- Check if response and user exist, then display score --}}
                    @if($response && $response->user)
                        @php
                            $score = $response->user->trust_score ?? 0;
                            $scoreColorClass = match (true) {
                                $score === 100 => 'text-blue-500', // Exactly 100 is green
                                $score < 60 => 'text-red-500',     // Below 60 is red
                                default => 'text-yellow-500',      // 60 to 99 is yellow
                            };
                        @endphp
                        <span @class(['text-3xl font-bold', $scoreColorClass])>
                            {{ $score }}/100
                        </span>
                    @else
                        <span class="text-2xl font-bold text-gray-600">--</span> {{-- Placeholder --}}
                    @endif
                </div>

                {{-- Time Completed Box --}}
                <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center min-h-[100px]">
                    <span class="text-lg font-semibold text-gray-700 mb-2">Time Completed</span>
                    @if($response && $response->created_at && $response->updated_at)
                        @php
                            // Calculate duration if possible
                            $duration = $response->updated_at->diffForHumans($response->created_at, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]);
                        @endphp
                         <span class="text-xl font-bold text-gray-600 text-center">{{ $duration }}</span>
                    @else
                        <span class="text-2xl font-bold text-gray-600">--:--</span> {{-- Placeholder --}}
                    @endif
                </div>
            </div>
        </div>

        {{-- Survey Pages, Questions, and Answers --}}
        @if($response)
            @foreach($survey->pages as $page)
                <div class="bg-white shadow-xl rounded-2xl p-10 space-y-12 mb-10">
                    <div class="mb-4">
                        <span class="text-2xl font-bold">{{ $page->title }}</span>
                        @if($page->subtitle)
                            <div class="text-gray-500 text-lg">{{ $page->subtitle }}</div>
                        @endif
                        <hr class="my-3 border-gray-300">
                    </div>
                    <div class="space-y-8">
                        @foreach($page->questions->sortBy('order') as $question)
                            <div>
                                <div class="font-semibold text-xl mb-2 pl-2">
                                    {{ $question->order ?? $loop->iteration }}. {{ $question->question_text }}
                                </div>
                                @php
                                    // Get all answers for this question in the current response
                                    $questionAnswers = $response->answers->where('survey_question_id', $question->id);
                                @endphp

                                @if(in_array($question->question_type, ['multiple_choice', 'radio']))
                                    <div class="space-y-3 pl-8">
                                        @foreach($question->choices as $choice)
                                            @php
                                                $isSelected = in_array($choice->choice_text, $questionAnswers->pluck('answer')->toArray());
                                            @endphp
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-block w-7 h-7 rounded-full border-2
                                                    {{ $isSelected ? 'bg-gray-500 border-gray-500' : 'bg-white border-gray-400' }}">
                                                </span>
                                                <span class="{{ $isSelected ? 'font-semibold text-gray-700' : 'text-gray-400' }} text-lg">
                                                    {{ $choice->choice_text }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->question_type === 'likert')
                                    @php
                                        // Decode Likert structure
                                        $likertColumns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
                                        $likertRows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);

                                        // Get the Likert answers for this response (likely stored as JSON or serialized array)
                                        // Assuming the answer is stored in the first answer record for this question
                                        $likertAnswerData = $questionAnswers->first() ? json_decode($questionAnswers->first()->answer, true) : [];
                                    @endphp
                                    <div class="overflow-x-auto mt-2 ml-8">
                                        <table class="min-w-full text-center border border-gray-300">
                                            <thead class="border-b border-gray-300">
                                                <tr>
                                                    <th class="bg-gray-50 w-52 border-r border-gray-300"></th> {{-- Header for row labels --}}
                                                    @foreach($likertColumns as $colIndex => $column)
                                                        <th class="bg-gray-50 px-4 py-2 text-base font-medium border-r border-gray-300 last:border-r-0">{{ $column }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($likertRows as $rowIndex => $row)
                                                    @php
                                                        $rowBg = $loop->even ? 'bg-gray-50' : 'bg-white';
                                                        // Get the selected column index for this specific row from the response data
                                                        $selectedColumnIndex = $likertAnswerData[$rowIndex] ?? null;
                                                    @endphp
                                                    <tr class="{{ $rowBg }} border-b border-gray-300 last:border-b-0">
                                                        <td class="px-4 py-2 text-left text-base border-r border-gray-300">{{ $row }}</td>
                                                        @foreach($likertColumns as $colIndex => $column)
                                                            <td class="px-4 py-2 border-r border-gray-300 last:border-r-0">
                                                                {{-- Check if this column was selected for this row --}}
                                                                @if($selectedColumnIndex !== null && $colIndex == $selectedColumnIndex)
                                                                    {{-- Display a filled circle if selected --}}
                                                                    <span class="inline-block w-5 h-5 bg-gray-500 rounded-full"></span>
                                                                @else
                                                                    {{-- Display an empty circle or nothing if not selected --}}
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
                                    @php
                                        // Get all answers for this respondent/question
                                        $allAnswers = $questionAnswers->pluck('answer')->toArray();
                                        $answerCount = count($allAnswers);
                                        $displayLimit = 5;
                                    @endphp
                                    <div class="bg-gray-100 border rounded px-4 py-3 text-lg text-gray-800 ml-8 space-y-2">
                                        {{-- Special display for rating --}}
                                        @if($question->question_type === 'rating')
                                            <div class="flex items-center">
                                                @for ($i = 1; $i <= ($question->stars ?? 5); $i++)
                                                    <svg class="w-6 h-6 {{ $i <= (int)($allAnswers[0] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-gray-600">({{ $allAnswers[0] ?? 'No answer' }})</span>
                                            </div>
                                        @else
                                            @foreach(array_slice($allAnswers, 0, $displayLimit) as $i => $ans)
                                                <div>{{ $ans }}</div>
                                                @if($i === $displayLimit - 1 && $answerCount > $displayLimit)
                                                    <div class="text-center text-gray-400 text-xl font-bold">...</div>
                                                @endif
                                            @endforeach
                                            @if($answerCount === 0)
                                                <div>No answer</div>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-gray-500 text-xl">No response selected.</div>
        @endif
    </div>
</div>
