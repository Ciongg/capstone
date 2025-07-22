<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="{ 
         fullscreenImageSrc: null,
         isFetchingMore: false, // Local flag to prevent multiple calls
         isScrolledToBottom() {
             // Use document.documentElement.scrollHeight for total page height
             // and window.pageYOffset for scroll position for better reliability.
             const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
             const windowHeight = window.innerHeight;
             const fullHeight = document.documentElement.scrollHeight;
             return (scrollTop + windowHeight) >= fullHeight - 100; // Trigger 100px from bottom
         }
     }"
     x-init="
        window.addEventListener('scroll', () => {
            // Check local flag, Livewire's loadingMore, and if there are more pages
            if (isScrolledToBottom() && !isFetchingMore && !$wire.loadingMore && $wire.hasMorePages) {
                isFetchingMore = true; // Set local flag to true before calling Livewire
                $wire.loadMore().then(() => {
                    isFetchingMore = false; // Reset local flag after Livewire action completes
                }).catch(() => {
                    isFetchingMore = false; // Also reset on error
                });
            }
        });
     ">
   
    {{--hides the scrollwheel in the topics bar--}}
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{-- Account notifications --}}
    @include('livewire.feed.partials.notifications')

    {{-- Search bar and points display --}}
    @include('livewire.feed.partials.search-bar')
    
    {{-- Applied filters display --}}
    @include('livewire.feed.partials.applied-filters')

    {{-- Filter panel --}}
    @include('livewire.feed.partials.filter-panel')

    {{-- Topic filters - reduce top spacing --}}
    <div class="mt-1">
        @include('livewire.feed.partials.topic-filters')
    </div>

    {{-- Loading indicator and surveys section --}}
    <div class="mt-3">
        {{-- Loading indicator with centered positioning --}}
        <div class="relative min-h-[200px]"> 
            {{-- Loading indicator - add missing targets --}}
            <div wire:loading wire:target="toggleTopicFilter, clearTopicFilter, applyPanelTagFilters, removeTagFilter, resetFilters, clearSurveyTypeFilter, toggleSurveyTypeFilter, clearSearch, toggleAnswerableFilter" 
                 class="absolute inset-0 flex justify-center items-center">
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 flex flex-col items-center space-y-3">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-gray-600">Loading surveys...</p>
                </div>
            </div>

            {{-- Survey grid - also update here to match the targets above --}}
            <div wire:loading.class="opacity-0" wire:target="toggleTopicFilter, clearTopicFilter, applyPanelTagFilters, removeTagFilter, resetFilters, clearSurveyTypeFilter, toggleSurveyTypeFilter, clearSearch, toggleAnswerableFilter">
                @if(count($surveys) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-12">
                        @foreach($surveys as $survey)
                            @include('livewire.feed.partials.survey-card', ['survey' => $survey])
                        @endforeach
                    </div>
                    
                    {{-- Bottom loader for infinite scroll - Controlled by Alpine's isFetchingMore flag --}}
                    <div x-show="isFetchingMore" class="flex justify-center mt-6 mb-4">
                        <div class="bg-white px-4 py-3 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-3">
                            <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-gray-500 text-sm">Loading more surveys...</p>
                        </div>
                    </div>

                    {{-- Condition for "End of results" - Show only if not fetching and no more pages --}}
                    @if(!$hasMorePages && count($surveys) > 0)
                        <div x-show="!isFetchingMore" class="text-center text-gray-500 py-8">
                            <p>You've reached the end of the surveys!</p>
                        </div>
                    @endif
                @else
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 text-center">
                        <p class="text-gray-500">No published surveys found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modals and overlays --}}
    @include('livewire.feed.partials.modals')

    <!-- Fullscreen Image Overlay -->
    <div x-show="fullscreenImageSrc"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="fullscreenImageSrc = null"  {{-- Click background to close --}}
         @keydown.escape.window="fullscreenImageSrc = null" {{-- Press Escape to close --}}
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4 cursor-pointer"
         style="display: none;"> {{-- Add display:none to prevent flash on load --}}
        
        <img :src="fullscreenImageSrc" 
             alt="Fullscreen Survey Image" 
             class="max-w-full max-h-full object-contain"
             > {{-- Prevent closing when clicking the image itself --}}
                  
        {{-- Larger, easier-to-tap close button for mobile - made identical to user-survey-view-modal --}}
        <button @click="fullscreenImageSrc = null" 
                class="cursor-pointer absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-white text-4xl sm:text-3xl font-bold leading-none rounded-full hover:bg-black hover:bg-opacity-25 focus:outline-none">
            &times;
        </button>
    </div>
</div>


