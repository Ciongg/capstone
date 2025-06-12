{{-- Applied Filters Display --}}
@if(!empty($search) || !is_null($activeFilters['topic']) || !empty($activeFilters['tags']) || !empty($activeFilters['institutionTags']) || !is_null($activeFilters['type']))
    <div class="mb-4 p-2 sm:p-3 bg-blue-50 border border-blue-100 rounded-md">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-xs sm:text-sm font-medium text-blue-700">Filtered by:</span>
            
            {{--Search Filter--}}
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

            {{--Topic Filter--}}
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
            {{--Survey Type Filter--}}
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
            {{-- Show all selected tags (both regular and institution) using a combined approach --}}
            @php
                // Define configurations for both tag types
                $tagTypes = [
                    [
                        'ids' => $activeFilters['tags'], //from the component's active filters
                        'categories' => $tagCategories, //from the component's rendered general tags
                        'label' => 'Tag:', //prefix
                        'borderClass' => 'border-blue-400', // border color 
                        'isInstitution' => false
                    ],
                    [
                        'ids' => $activeFilters['institutionTags'], //from the component's active filters
                        'categories' => $institutionTagCategories,//from the component's rendered institution tags
                        'label' => 'Institution Tag:',
                        'borderClass' => 'border-yellow-400',
                        'isInstitution' => true
                    ]
                ];
            @endphp


            {{-- Loop through both tag types and display their tags --}}

            @foreach($tagTypes as $tagType)
                @foreach($tagType['ids'] as $tagId)
                    @php
                        $tagName = '';
                        foreach ($tagType['categories'] as $category) {
                            $tag = $category->tags->firstWhere('id', $tagId); //checks category tags if it matches with the current tag selected
                            if ($tag) {
                                $tagName = $tag->name;
                                break;
                            }
                        }
                    @endphp

                    {{-- Display the tag if found  ex tag: 18-24 --}}
                    @if(!empty($tagName))
                        <div class="flex items-center bg-white px-3 py-1 rounded-full text-sm border {{ $tagType['borderClass'] }}">
                            <span class="mr-1">{{ $tagType['label'] }}</span>
                            <span class="font-medium">{{ $tagName }}</span>
                            <button wire:click="removeTagFilter({{ $tagId }}, {{ $tagType['isInstitution'] ? 'true' : 'false' }})" 
                                    class="ml-2 text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    @endif
                @endforeach
            @endforeach
            
            <button wire:click="resetFilters" class="ml-auto text-blue-600 hover:text-blue-800 text-xs sm:text-sm font-semibold">
                Clear all filters
            </button>
        </div>
    </div>
@endif