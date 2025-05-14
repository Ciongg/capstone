{{-- Applied Filters Display --}}
@if(!empty($search) || !is_null($activeFilters['topic']) || !empty($activeFilters['tags']) || !empty($activeFilters['institutionTags']) || !is_null($activeFilters['type']))
    <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-md">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm font-medium text-blue-700">Filtered by:</span>
            
            @if(!empty($search))
                <div class="flex items-center bg-white px-3 py-1 rounded-full text-sm border border-blue-200">
                    <span class="mr-1">Search:</span>
                    <span class="font-medium">{{ $search }}</span>
                    <button wire:click="clearSearch" class="ml-2 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endif
            
            @if(!is_null($activeFilters['topic']))
                <div class="flex items-center bg-white px-3 py-1 rounded-full text-sm border border-blue-200">
                    <span class="mr-1">Topic:</span>
                    <span class="font-medium">{{ $topics->firstWhere('id', $activeFilters['topic'])->name }}</span>
                    <button wire:click="clearTopicFilter" class="ml-2 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endif
            
            @if(!is_null($activeFilters['type']))
                <div class="flex items-center bg-white px-3 py-1 rounded-full text-sm border border-blue-200">
                    <span class="mr-1">Complexity:</span>
                    <span class="font-medium">{{ ucfirst($activeFilters['type']) }}</span>
                    <button wire:click="clearSurveyTypeFilter" class="ml-2 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endif
            
            {{-- Show all selected tags --}}
            @foreach($activeFilters['tags'] as $tagId)
                @php
                    $tagName = '';
                    foreach ($tagCategories as $category) {
                        $tag = $category->tags->firstWhere('id', $tagId);
                        if ($tag) {
                            $tagName = $tag->name;
                            break;
                        }
                    }
                @endphp
                @if(!empty($tagName))
                    <div class="flex items-center bg-white px-3 py-1 rounded-full text-sm border border-blue-200">
                        <span class="mr-1">Tag:</span>
                        <span class="font-medium">{{ $tagName }}</span>
                        <button wire:click="removeTagFilter({{ $tagId }})" class="ml-2 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endif
            @endforeach
            
            {{-- Show all selected institution tags --}}
            @foreach($activeFilters['institutionTags'] as $tagId)
                @php
                    $tagName = '';
                    $institutionTagCategories = Auth::user()->institution_id
                        ? \App\Models\InstitutionTagCategory::where('institution_id', Auth::user()->institution_id)
                            ->with('tags')
                            ->get()
                        : collect([]);
                        
                    foreach ($institutionTagCategories as $category) {
                        $tag = $category->tags->firstWhere('id', $tagId);
                        if ($tag) {
                            $tagName = $tag->name;
                            break;
                        }
                    }
                @endphp
                @if(!empty($tagName))
                    <div class="flex items-center bg-white px-3 py-1 rounded-full text-sm border border-yellow-200">
                        <span class="mr-1">Institution Tag:</span>
                        <span class="font-medium">{{ $tagName }}</span>
                        <button wire:click="removeTagFilter({{ $tagId }}, true)" class="ml-2 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endif
            @endforeach
            
            <button wire:click="clearAllFilters" class="ml-auto text-blue-600 hover:text-blue-800 text-sm font-semibold">
                Clear all filters
            </button>
        </div>
    </div>
@endif