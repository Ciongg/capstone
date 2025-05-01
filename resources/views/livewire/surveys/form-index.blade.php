<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">My Surveys</h1>
    @forelse($surveys as $survey)
        <div class="bg-white shadow rounded p-4 mb-4 flex justify-between items-center">
            <div>
                <div class="text-lg font-semibold">{{ $survey->title }}</div>
                <div class="text-gray-600">{{ $survey->description }}</div>
            </div>
            <div class="flex items-center space-x-4">
                @if($survey->status === 'pending')
                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm font-medium">Pending</span>
            @elseif($survey->status === 'published')
                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-medium">Published</span>
            @elseif($survey->status === 'ongoing')
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-sm font-medium">Ongoing</span>
            @elseif($survey->status === 'finished')
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">Finished</span>
                @endif
                <a href="{{ route('surveys.create', $survey->id) }}"
                    wire:navigate
                   class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Open
                </a>
            </div>
        </div>
    @empty
        <div class="text-gray-500">You have not created any surveys yet.</div>
    @endforelse
</div>
