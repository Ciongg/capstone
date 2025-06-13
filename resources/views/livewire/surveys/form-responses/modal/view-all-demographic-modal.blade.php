<div>
    @if($user)
        @php
            $userTagIds = $user->tags->pluck('id')->toArray();
            $matchedTags = $survey->tags->filter(fn($tag) => in_array($tag->id, $userTagIds));
            $unmatchedTags = $survey->tags->filter(fn($tag) => !in_array($tag->id, $userTagIds));
        @endphp

        {{-- Matched Demographics Section --}}
        @if($matchedTags->isNotEmpty())
            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">Respondent's Matched Demographics</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($matchedTags as $tag)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-200 text-green-800">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Other Survey Demographics Section --}}
        @if($unmatchedTags->isNotEmpty())
            <div class="mb-2">
                <h3 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">Unmatched Survey Demographics</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($unmatchedTags as $tag)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Message if no demographics are set at all --}}
        @if($survey->tags->isEmpty())
            <span class="text-gray-500 italic">No demographics set for this survey.</span>
        @endif

        {{-- Message if user has no tags but survey has demographics --}}
        @if($survey->tags->isNotEmpty() && $matchedTags->isEmpty() && $unmatchedTags->isNotEmpty())
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <span class="text-yellow-800 text-sm">This respondent doesn't match any of the target demographics for this survey.</span>
            </div>
        @endif
    @else
        <div class="text-gray-500 italic">
            No user data available for this respondent.
        </div>
    @endif
</div>
