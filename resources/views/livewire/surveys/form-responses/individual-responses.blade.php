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
                <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span class="text-lg font-semibold text-gray-700 mb-2">Demographic Matched</span>
                    <span class="text-2xl font-bold text-gray-600">--</span>
                </div>
                <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span class="text-lg font-semibold text-gray-700 mb-2">Trust Score</span>
                    <span class="text-2xl font-bold text-gray-600">--</span>
                </div>
                <div class="bg-gray-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span class="text-lg font-semibold text-gray-700 mb-2">Time Completed</span>
                    <span class="text-2xl font-bold text-gray-600">--:--</span>
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
                                    $answers = $response->answers->where('survey_question_id', $question->id)->pluck('answer')->toArray();
                                @endphp

                                @if(in_array($question->question_type, ['multiple_choice', 'radio']))
                                    <div class="space-y-3 pl-8">
                                        @foreach($question->choices as $choice)
                                            @php
                                                $isSelected = in_array($choice->choice_text, $answers);
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
                                @else
                                    <div class="bg-gray-100 border rounded px-4 py-3 text-lg text-gray-800 ml-8">
                                        {{ $answers[0] ?? 'No answer' }}
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
