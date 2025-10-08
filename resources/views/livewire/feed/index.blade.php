<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="{ 
         fullscreenImageSrc: null,
         isFetchingMore: false,
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
            {{-- Loading indicator - update targets to include clearSearch --}}
            <div wire:loading wire:target="toggleTopicFilter, clearFilter, applyFilters, removeTagFilter, resetFilters, toggleBooleanFilter, updatedSearch, clearSearch" 
                 class="absolute inset-0 flex justify-center items-center">
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 flex flex-col items-center space-y-3">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-gray-600">Loading surveys...</p>
                </div>
            </div>

            {{-- Survey grid - also update targets to match the above --}}
            <div wire:loading.class="opacity-0" wire:target="toggleTopicFilter, clearFilter, applyFilters, removeTagFilter, resetFilters, toggleBooleanFilter, updatedSearch, clearSearch">
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

@push('scripts')
<script>
    // Use window event listener for Livewire dispatched events
    window.addEventListener('show-data-privacy-notice', function (event) {
        // Access the redirectUrl from event.detail
        const redirectUrl = event.detail && event.detail.redirectUrl ? event.detail.redirectUrl : (event.detail && event.detail[0] && event.detail[0].redirectUrl ? event.detail[0].redirectUrl : null);
        
        if (!redirectUrl) {
            console.error('No redirect URL provided');
            return;
        }
        
        Swal.fire({
            title: 'Data Privacy Notice',
            html: '<div style="text-align: left; max-height: 400px; overflow-y: auto; padding: 15px;">' +
                  '<p style="margin-bottom: 15px; font-weight: 600;">Before you proceed with this survey, please read and acknowledge the following:</p>' +
                  '<h4 style="margin-top: 15px; margin-bottom: 10px; font-weight: 600;">Data Collection & Usage</h4>' +
                  '<p style="margin-bottom: 10px; font-size: 14px; line-height: 1.6;">By participating in this survey, you acknowledge and agree that Formigo will collect, process, and store your responses along with associated metadata (including but not limited to: response timestamps, user ID, and demographic information if provided).</p>' +
                  '<h4 style="margin-top: 15px; margin-bottom: 10px; font-weight: 600;">Purpose of Data Collection</h4>' +
                  '<p style="margin-bottom: 10px; font-size: 14px; line-height: 1.6;">Your data will be used for research and analytical purposes by the survey creator. The information collected will help in understanding trends, preferences, and opinions related to the survey topic.</p>' +
                  '<h4 style="margin-top: 15px; margin-bottom: 10px; font-weight: 600;">Data Privacy & Security</h4>' +
                  '<p style="margin-bottom: 10px; font-size: 14px; line-height: 1.6;">Your responses will be treated in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173). We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>' +
                  '<h4 style="margin-top: 15px; margin-bottom: 10px; font-weight: 600;">Your Rights</h4>' +
                  '<p style="margin-bottom: 10px; font-size: 14px; line-height: 1.6;">You have the right to access, correct, or request deletion of your personal data. You may also withdraw your consent at any time by contacting the Formigo support team.</p>' +
                  '<p style="margin-top: 15px; font-size: 14px; font-weight: 600;">By clicking "I Agree, Continue to Survey" below, you acknowledge that you have read and understood this notice and consent to the collection and processing of your data as described above.</p>' +
                  '</div>',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'I Agree, Continue to Survey',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#03b8ff',
            cancelButtonColor: '#6b7280',
            width: '600px',
            allowOutsideClick: false,
            allowEscapeKey: true,
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = redirectUrl;
            }
        });
    });
</script>
@endpush
