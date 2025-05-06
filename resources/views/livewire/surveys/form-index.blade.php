<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">My Surveys</h1>
    @forelse($surveys as $survey)
        <div class="bg-white shadow rounded p-4 mb-4 flex justify-between items-center">
            {{-- Left Side: Title & Description --}}
            <div class="flex-grow mr-4">
                <div class="text-lg font-semibold">{{ $survey->title }}</div>
                <div class="text-gray-600 text-sm">{{ $survey->description }}</div>
            </div>

            {{-- Right Side: Status, Count, Actions --}}
            <div class="flex items-center space-x-3 flex-shrink-0">
                {{-- Status Badge --}}
                @if($survey->status === 'pending')
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">Pending</span>
                @elseif($survey->status === 'published')
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">Published</span>
                @elseif($survey->status === 'ongoing')
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">Ongoing</span>
                @elseif($survey->status === 'finished')
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">Finished</span>
                @endif

                {{-- Respondent Count --}}
                <div class="flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm font-semibold" title="Number of Responses / Target Respondents">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ $survey->responses_count ?? 0 }}/{{ $survey->target_respondents > 0 ? $survey->target_respondents : '∞' }}</span>
                </div>

                {{-- Open Button --}}
                <a href="{{ route('surveys.create', $survey->id) }}" {{-- Changed route name --}}
                   class="px-3 py-1.5 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                    Open
                </a>

                {{-- Delete Button --}}
                <button
                    wire:click="deleteSurvey({{ $survey->id }})" {{-- Assumes a deleteSurvey method exists in the component --}}
                    wire:confirm="Are you sure you want to delete this survey and all its data?"
                    class="p-1.5 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    title="Delete Survey"
                >
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>
    @empty
        <div class="text-center text-gray-500 mt-8">You have not created any surveys yet.</div>
    @endforelse
</div>
