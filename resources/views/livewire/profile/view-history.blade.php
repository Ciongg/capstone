<div>
    @forelse($user->responses()->with('survey')->latest()->get() as $response)
        <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between">
            <div>
                <div class="font-semibold text-lg text-blue-700">
                    {{ $response->survey->title ?? 'Survey Deleted' }}
                </div>
                <div class="text-gray-500 text-sm">
                    Answered: {{ $response->created_at->format('Y-m-d g:ia') }}
                </div>
            </div>
            <div class="mt-2 sm:mt-0 flex items-center">
                <span class="text-gray-600 mr-2">Points:</span>
                <span class="font-bold text-blue-500 text-xl">
                    {{ $response->survey->points_allocated ?? 0 }}
                </span>
            </div>
        </div>
    @empty
        <div class="text-gray-500 text-center py-8">
            No survey history yet.
        </div>
    @endforelse
</div>
