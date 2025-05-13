{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\feed\index.blade.php --}}
{{-- Keep Alpine for non-filter UI like fullscreenImageSrc, notifications, top bar, filters, etc. as in your full file --}}
<div class="max-w-6xl mx-auto py-8" x-data="{ fullscreenImageSrc: null }">
   
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

    {{-- Survey grid --}}
    @include('livewire.feed.partials.survey-grid')

    {{-- Modals and overlays --}}
    @include('livewire.feed.partials.modals')
</div> {{-- End of main max-w-6xl div --}}


    {{-- Your existing content --}}
