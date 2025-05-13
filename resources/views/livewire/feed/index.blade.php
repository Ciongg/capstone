{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\feed\index.blade.php --}}
{{-- Keep Alpine for non-filter UI like fullscreenImageSrc, notifications, top bar, filters, etc. as in your full file --}}
<div class="max-w-7xl mx-auto py-8" x-data="{ fullscreenImageSrc: null }">
   
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

    {{-- Applied filters display --}}
    @include('livewire.feed.partials.applied-filters')

    {{-- Filter panel --}}
    @include('livewire.feed.partials.filter-panel')

    {{-- Topic filters --}}
    @include('livewire.feed.partials.topic-filters')

    {{-- REMOVE THIS DUPLICATE INCLUDE - it's causing the double grid --}}
    {{-- @include('livewire.feed.partials.survey-grid') --}}

    {{-- Loading indicator and surveys section --}}
    <div class="mt-4">
        {{-- Loading indicator with centered positioning --}}
        <div class="relative min-h-[200px]"> {{-- Add a parent container with relative positioning --}}
            {{-- Loading indicator - with unified targets and centered positioning --}}
            <div wire:loading wire:target="toggleTopicFilter, clearTopicFilter, filterByTag, applyPanelTagFilters, removeTagFilter, clearPanelTagFilter, clearAllFilters" 
                 class="absolute inset-0 flex justify-center items-center">
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 flex flex-col items-center space-y-3">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-gray-600">Loading surveys...</p>
                </div>
            </div>

            {{-- Survey grid - with unified targets --}}
            <div wire:loading.class="opacity-0" wire:target="toggleTopicFilter, clearTopicFilter, filterByTag, applyPanelTagFilters, removeTagFilter, clearPanelTagFilter, clearAllFilters">
                @if($surveys->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($surveys as $survey)
                            @include('livewire.feed.partials.survey-card', ['survey' => $survey])
                        @endforeach
                    </div>
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
</div> {{-- End of main max-w-6xl div --}}


    {{-- Your existing content --}}
    {{-- Optional: Add an Alpine.js hook to ensure the grid refreshes properly --}}
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('filter-changed', function () {
                // Force browser to recalculate layout after filter changes
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 200);
            });
        });
    </script>
