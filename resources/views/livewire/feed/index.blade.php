{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\feed\index.blade.php --}}
{{-- Keep Alpine for non-filter UI like fullscreenImageSrc, notifications, top bar, filters, etc. --}}
<div class="max-w-7xl mx-auto " 
     x-data="{ 
         fullscreenImageSrc: null,
         isScrolledToBottom() {
             return (window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500;
         }
     }"
     x-init="
        window.addEventListener('scroll', () => {
            if (isScrolledToBottom() && !$wire.loadingMore && $wire.hasMorePages) {
                $wire.loadMore();
            }
        });
     ">
   
        {{-- Add any global CSS styles needed --}}
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

    {{-- Add the support-request modal --}}
    

    {{-- Applied filters display --}}
    @include('livewire.feed.partials.applied-filters')

    {{-- Filter panel --}}
    @include('livewire.feed.partials.filter-panel')

    {{-- Topic filters --}}
    @include('livewire.feed.partials.topic-filters')

    {{-- Loading indicator and surveys section --}}
    <div class="mt-4">
        {{-- Loading indicator with centered positioning --}}
        <div class="relative min-h-[200px]"> 
            {{-- Loading indicator - add clearSurveyTypeFilter to the wire:target --}}
            <div wire:loading wire:target="toggleTopicFilter, clearTopicFilter, filterByTag, applyPanelTagFilters, removeTagFilter, clearPanelTagFilter, clearAllFilters, clearSurveyTypeFilter, toggleSurveyTypeFilter" 
                 class="absolute inset-0 flex justify-center items-center">
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 flex flex-col items-center space-y-3">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-gray-600">Loading surveys...</p>
                </div>
            </div>

            {{-- Survey grid - also update here to match the targets above --}}
            <div wire:loading.class="opacity-0" wire:target="toggleTopicFilter, clearTopicFilter, filterByTag, applyPanelTagFilters, removeTagFilter, clearPanelTagFilter, clearAllFilters, clearSurveyTypeFilter, toggleSurveyTypeFilter">
                @if(count($surveys) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                        @foreach($surveys as $survey)
                            @include('livewire.feed.partials.survey-card', ['survey' => $survey])
                        @endforeach
                    </div>
                    
                    {{-- Bottom loader for infinite scroll --}}
                    @if($hasMorePages)
                        <div class="flex justify-center mt-6 mb-4" wire:loading.delay wire:target="loadMore">
                            <div class="bg-white px-4 py-3 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-3">
                                <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                <p class="text-gray-500 text-sm">Loading more surveys...</p>
                            </div>
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
             @click.stop> {{-- Prevent closing when clicking the image itself --}}
                  
        {{-- Larger, easier-to-tap close button for mobile - made identical to user-survey-view-modal --}}
        <button @click="fullscreenImageSrc = null" 
                class="cursor-pointer absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-white text-4xl sm:text-3xl font-bold leading-none rounded-full hover:bg-black hover:bg-opacity-25 focus:outline-none">
            &times;
        </button>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('filter-changed', function () {
            // Scroll back to top when filters are changed
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            // Force browser to recalculate layout after filter changes
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 200);
        });
    });
</script>
