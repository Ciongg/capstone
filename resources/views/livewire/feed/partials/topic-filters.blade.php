{{-- Topic Filters with enhanced scrolling --}}
<div class="mb-6 relative" 
     x-data="{ 
         scroll: function(direction) {
             const container = document.getElementById('topic-scroll');
             const scrollAmount = 200;
             if (direction === 'left') {
                 container.scrollLeft -= scrollAmount;
             } else {
                 container.scrollLeft += scrollAmount;
             }
         },
         showLeftArrow: false,
         showRightArrow: true,
         checkArrows: function() {
             const container = document.getElementById('topic-scroll');
             this.showLeftArrow = container.scrollLeft > 0;
             this.showRightArrow = container.scrollLeft < (container.scrollWidth - container.clientWidth - 5);
         }
     }"
     x-init="$nextTick(() => { checkArrows(); })"
>

    {{-- Left scroll button --}}
    <button 
        @click="scroll('left')" 
        x-show="showLeftArrow"
        x-transition
        class="absolute left-0 top-1/2 -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-1 shadow-md z-10"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    
    {{-- Right scroll button --}}
    <button 
        @click="scroll('right')" 
        x-show="showRightArrow"
        x-transition
        class="absolute right-0 top-1/2 -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-1 shadow-md z-10"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
    
    {{-- Topic pills container --}}
    <div 
        id="topic-scroll" 
        class="scrollbar-hide flex overflow-x-auto py-2 px-1 space-x-2"
        @scroll.throttle.50ms="checkArrows()"
    >
        {{-- All Topics option --}}
        <button
            wire:click="clearTopicFilter"
            class="whitespace-nowrap px-4 py-2 rounded-full border text-sm font-medium flex-shrink-0 transition-colors
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
                class="whitespace-nowrap px-4 py-2 rounded-full border text-sm font-medium flex-shrink-0 transition-colors
                      {{ $activeFilters['topic'] == $topic->id 
                         ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-transparent shadow-md' 
                         : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
            >
                {{ $topic->name }}
            </button>
        @endforeach
    </div>
</div>