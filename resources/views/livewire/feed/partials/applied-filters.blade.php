{{-- Applied Filters Display --}}
@if(!empty($search) || $activeTopicId || $activePanelTagId)
    <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-md">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm font-medium text-blue-700">Filtered by:</span>
            @if(!empty($search))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Search: "{{ $search }}"
                    <button wire:click="clearSearch" class="ml-1 text-gray-500 hover:text-gray-700 focus:outline-none" title="Clear search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </button>
                </span>
            @endif
            @if($activeTopicId && ($topic = $topics->firstWhere('id', $activeTopicId)))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Topic: {{ $topic->name }}
                    <button wire:click="clearTopicFilter" class="ml-1 text-green-500 hover:text-green-700 focus:outline-none" title="Clear topic filter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </button>
                </span>
            @endif
            @if($activePanelTagId && ($tag = \App\Models\Tag::find($activePanelTagId)))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    Tag: {{ $tag->name }}
                    <button wire:click="clearPanelTagFilter" class="ml-1 text-indigo-500 hover:text-indigo-700 focus:outline-none" title="Clear tag filter">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            @endif
            <button wire:click="clearAllFilters" class="text-xs text-blue-600 hover:text-blue-800 underline ml-auto focus:outline-none">
                Clear all filters
            </button>
        </div>
    </div>
@endif