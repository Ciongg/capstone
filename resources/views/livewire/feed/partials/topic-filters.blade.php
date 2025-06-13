{{-- Topic Filters with enhanced scrolling --}}
<div class="mb-4 relative px-1 sm:px-0" 
     x-data="{ 
         handleWheel: function(event) {
             // Prevent the default vertical scroll
             event.preventDefault();
             
             // Get the scroll container
             const container = document.getElementById('topic-scroll');
             
             // Determine scroll amount (multiplier makes scrolling more noticeable)
             const scrollAmount = event.deltaY * 0.5;
             
             // Apply horizontal scroll
             container.scrollLeft += scrollAmount;
         },
         isDragging: false,
         startX: 0,
         scrollLeft: 0,
         
         // Start drag operation
         startDrag: function(e) {
             const container = document.getElementById('topic-scroll');
             this.isDragging = true;
             
             // Get initial positions
             if (e.type === 'touchstart') {
                 this.startX = e.touches[0].pageX - container.offsetLeft;
             } else {
                 this.startX = e.pageX - container.offsetLeft;
             }
             
             this.scrollLeft = container.scrollLeft;
             
             // Change cursor to indicate dragging
             container.style.cursor = 'grabbing';
         },
         
         // During drag operation
         drag: function(e) {
             if (!this.isDragging) return;
             e.preventDefault();
             
             const container = document.getElementById('topic-scroll');
             let x;
             
             // Calculate how far the pointer has moved
             if (e.type === 'touchmove') {
                 x = e.touches[0].pageX - container.offsetLeft;
             } else {
                 x = e.pageX - container.offsetLeft;
             }
             
             // Calculate the distance moved and update scroll position
             const walk = (x - this.startX) * 1.5; // Multiplier for faster scrolling
             container.scrollLeft = this.scrollLeft - walk;
         },
         
         // End drag operation
         endDrag: function() {
             const container = document.getElementById('topic-scroll');
             this.isDragging = false;
             container.style.cursor = 'grab';
         }
     }"
>
    {{-- Topic pills container --}}
    <div 
        id="topic-scroll" 
        class="scrollbar-hide flex overflow-x-auto py-3 px-2 space-x-2 sm:space-x-3 cursor-grab select-none"
        @wheel.prevent="handleWheel($event)"
        @mousedown="startDrag"
        @mousemove="drag"
        @mouseup="endDrag"
        @mouseleave="endDrag"
        @touchstart="startDrag"
        @touchmove="drag"
        @touchend="endDrag"
    >
        {{-- All Topics option --}}
        <button
            wire:click="clearTopicFilter"
            class="whitespace-nowrap px-3 sm:px-4 py-2 rounded-full border text-sm font-medium flex-shrink-0 transition-colors select-none
                  {{ is_null($activeFilters['topic']) 
                     ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-transparent shadow-md' 
                     : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
        >
            All Topics
        </button>
        
        {{-- Topic options --}}
        @foreach($topics as $topic)
            <button
                wire:click="toggleTopicFilter({{ $topic->id }})"
                wire:key="topic-{{ $topic->id }}"
                class="whitespace-nowrap px-3 sm:px-4 py-2 shadow-md rounded-full border text-sm font-medium flex-shrink-0 transition-colors select-none
                      {{ $activeFilters['topic'] == $topic->id 
                         ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-transparent' 
                         : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
            >
                {{ $topic->name }}
            </button>
        @endforeach
    </div>
</div>