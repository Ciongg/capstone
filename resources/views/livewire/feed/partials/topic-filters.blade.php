{{-- Topic Filters with enhanced scrolling --}}
<div class="mb-6 relative" 
     x-data="{ 
        scroll: 0,
        scrollMax: 0,
        activeTopicId: @entangle('activeTopicId').defer, {{-- Use .defer for better performance --}}
        initScroll() {
            if (this.$refs.scrollContainer) {
                this.scrollMax = this.$refs.scrollContainer.scrollWidth - this.$refs.scrollContainer.clientWidth;
                this.$refs.scrollContainer.addEventListener('scroll', () => {
                    this.scroll = this.$refs.scrollContainer.scrollLeft;
                });
                this.$refs.scrollContainer.addEventListener('wheel', (e) => {
                    if (e.deltaY !== 0) {
                        e.preventDefault();
                        this.$refs.scrollContainer.scrollLeft += e.deltaY;
                        this.scroll = this.$refs.scrollContainer.scrollLeft;
                    }
                }, { passive: false });
            }
        }
     }"
     x-init="
        initScroll();   
        // Listen for Livewire activeTopicId changes and update Alpine
        $wire.on('activeTopicIdChanged', (value) => {
            activeTopicId = value;
        });
     " 
     @resize.window="initScroll()">

    {{-- The scrollable container that holds both arrows and topics --}}
    <div class="relative">
        {{-- Left Arrow (positioned as first item, fixed to left) --}}
        <button 
            @click="$refs.scrollContainer.scrollBy({left: -200, behavior: 'smooth'})" 
            class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 px-2 py-2 bg-white text-gray-700 rounded-full shadow-lg hover:shadow-xl hover:bg-gray-100 transition-all duration-300"
            :class="{'opacity-50 cursor-not-allowed': scroll <= 0, 'cursor-pointer': scroll > 0}"
            x-show="scrollMax > 0"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        
        {{-- Right Arrow (positioned as last item, fixed to right) --}}
        <button 
            @click="$refs.scrollContainer.scrollBy({left: 200, behavior: 'smooth'})" 
            class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 px-2 py-2 bg-white text-gray-700 rounded-full shadow-lg hover:shadow-xl hover:bg-gray-100 transition-all duration-300"
            :class="{'opacity-50 cursor-not-allowed': scroll >= scrollMax, 'cursor-pointer': scroll < scrollMax}"
            x-show="scrollMax > 0"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
        
        {{-- The scrollable container with topics --}}
        <div class="flex overflow-x-auto py-2 px-10 scrollbar-hide scroll-smooth" x-ref="scrollContainer" style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;" wire:ignore>
            <div class="flex space-x-3 min-w-max">
                @foreach($topics as $topic)
                    <button 
                        @click="
                            const newValue = activeTopicId == {{ $topic->id }} ? null : {{ $topic->id }};
                            activeTopicId = newValue;
                            $wire.toggleTopicFilter({{ $topic->id }});
                        "
                        :class="{
                            'bg-blue-500 text-white': activeTopicId == {{ $topic->id }}, 
                            'bg-white text-gray-700 hover:bg-blue-100 hover:text-blue-700': activeTopicId != {{ $topic->id }}
                        }"
                        class="px-5 py-2.5 text-base font-medium rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 whitespace-nowrap border border-gray-100"
                    >
                        {{ $topic->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>