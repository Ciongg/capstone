{{-- filepath: resources/views/livewire/surveys/form-responses/form-responses.blade.php --}}
<div class="max-w-4xl mx-auto py-8">
    {{-- Delete All Responses Button --}}
    <div class="flex justify-end mb-4">
        <button
            wire:click="deleteAllResponses"
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            onclick="return confirm('Are you sure you want to delete all responses for this survey?')"
        >
            Delete All Responses
        </button>
    </div>

    {{-- Top summary containers --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
            <span class="text-lg font-semibold">Responses</span>
            <span class="text-2xl text-blue-600 font-bold mt-2">
                {{ $survey->responses()->count() }}
            </span>
        </div>
        <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
            <span class="text-lg font-semibold">Average Time</span>
            <span class="text-2xl text-blue-600 font-bold mt-2">--</span>
        </div>
        <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
            <span class="text-lg font-semibold">Points</span>
            <span class="text-2xl text-blue-600 font-bold mt-2">--</span>
        </div>
    </div>

    {{-- Survey questions and responses --}}
    @if(isset($survey))
        @foreach($survey->pages as $page)
            @foreach($page->questions as $question)
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="font-semibold mb-2">{{ $question->question_text }}</div>
                    <div class="space-y-2">
                        @forelse($question->answers as $answer)
                            <div class="p-3 bg-gray-50 rounded border border-gray-200">
                                {{ $answer->answer }}
                            </div>
                        @empty
                            <div class="text-gray-400 italic">No responses yet.</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        @endforeach
    @else
        <div class="text-gray-500">Survey not found.</div>
    @endif
</div>
