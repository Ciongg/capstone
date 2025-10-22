<div>
    @php
        $respondentTagIds = collect($demographicTags)->pluck('id')->toArray();
        $surveyTagsCollection = collect($surveyTags);
        $matchedTags = $surveyTagsCollection->filter(fn($tag) => in_array($tag['id'], $respondentTagIds));
        $unmatchedTags = $surveyTagsCollection->filter(fn($tag) => !in_array($tag['id'], $respondentTagIds));
        $hasMatches = $matchedTags->isNotEmpty();
    @endphp

    @if(empty($demographicTags) && !$surveyTagsCollection->isEmpty())
        {{-- Message if demographic data isn't available --}}
        <div class="text-gray-500 italic text-center py-4">
            No demographic data available for this respondent.
        </div>
    @elseif($surveyTagsCollection->isEmpty())
        {{-- Message if no demographics are set at all --}}
        <div class="text-gray-500 italic text-center py-4">
            No demographics set for this survey.
        </div>
    @elseif(!$hasMatches)
        {{-- Message when no tags match --}}
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
            <span class="text-yellow-800">This respondent doesn't match any of the target demographics for this survey.</span>
        </div>
    @else
        {{-- Matched Demographics Section --}}
        <div class="mb-6">
            <h3 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">Respondent's Matched Demographics</h3>
            <div class="flex flex-wrap gap-3">
                @foreach($matchedTags as $tag)
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-200 text-green-800">
                        {{ $tag['name'] }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Unmatched Survey Demographics Section - Only shown when there are matches --}}
        @if($unmatchedTags->isNotEmpty())
            <div class="mb-2">
                <h3 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">Unmatched Survey Demographics</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($unmatchedTags as $tag)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700">
                            {{ $tag['name'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
        
    @endif
</div>

