<div 
    x-data="{
        activeSlide: 1,
        slides: 4, {{-- Updated from 2 to 4 slides --}}
        interval: 5000,
        autoplayInterval: null,
        init() {
            this.startAutoplay();
            // Re-init on Livewire loads
            window.addEventListener('livewire:navigated', () => {
                this.startAutoplay();
            });
        },
        startAutoplay() {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = setInterval(() => {
                this.activeSlide = this.activeSlide === this.slides ? 1 : this.activeSlide + 1;
            }, this.interval);
        },
        setActiveSlide(slide) {
            this.activeSlide = slide;
            this.startAutoplay();
        }
    }"
    class="w-full h-full bg-white relative"
    x-init="init()"
    wire:ignore
>
    <!-- Carousel content -->
    <div class="absolute inset-0">
        <!-- Slide 1 -->
        <div 
            x-show="activeSlide === 1" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="h-full w-full p-8 flex flex-col justify-center"
        >
            <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Help Researchers In Need!</h2>
            <p class="text-gray-600 mb-8 text-center">Browse through your feed to find latest surveys from different categories</p>
            
            <!-- Feed Image -->
            <div class="w-full px-4 flex items-center justify-center">
                <img src="{{ asset('storage/images/feed-carousel.png') }}" alt="Feed" 
                     class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div 
            x-show="activeSlide === 2" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="h-full w-full p-8 flex flex-col justify-center items-center"
        >
            <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Gain Rewards</h2>
            <p class="text-gray-600 mb-8 text-center">Answer Surveys and get points to turn in for real rewards</p>
            
            <!-- Rewards Image -->
            <div class="w-full px-4 flex items-center justify-center">
                <img src="{{ asset('storage/images/rewards-carousel.png') }}" alt="Rewards" 
                     class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
            </div>
        </div>
        
        <!-- Slide 3 - New Targeted Surveys slide -->
        <div 
            x-show="activeSlide === 3" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="h-full w-full p-8 flex flex-col justify-center items-center"
        >
            <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Find or Create Targeted Surveys</h2>
            <p class="text-gray-600 mb-8 text-center">Matching respondents with surveys through target demographics</p>
            
            <!-- Targeted Image -->
            <div class="w-full px-4 flex items-center justify-center">
                <img src="{{ asset('storage/images/targeted-carousel.png') }}" alt="Targeted Surveys" 
                     class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
            </div>
        </div>
        
        <!-- Slide 4 - New Form Builder slide -->
        <div 
            x-show="activeSlide === 4" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="h-full w-full p-8 flex flex-col justify-center items-center"
        >
            <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Create Forms Easily</h2>
            <p class="text-gray-600 mb-8 text-center">Use our built in form builder and share it to respondents instantly</p>
            
            <!-- Form Builder Image -->
            <div class="w-full px-4 flex items-center justify-center">
                <img src="{{ asset('storage/images/form-builder-carousel.png') }}" alt="Form Builder" 
                     class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
            </div>
        </div>
    </div>
    
    <!-- Pagination Dots - Updated with 2 additional dots -->
    <div class="absolute bottom-8 left-0 right-0 flex justify-center space-x-2">
        <button 
            @click="setActiveSlide(1)" 
            class="w-4 h-4 rounded-full transition-colors duration-200" 
            :class="activeSlide === 1 ? 'bg-blue-500' : 'bg-gray-300'"
        ></button>
        <button 
            @click="setActiveSlide(2)" 
            class="w-4 h-4 rounded-full transition-colors duration-200" 
            :class="activeSlide === 2 ? 'bg-blue-500' : 'bg-gray-300'"
        ></button>
        <button 
            @click="setActiveSlide(3)" 
            class="w-4 h-4 rounded-full transition-colors duration-200" 
            :class="activeSlide === 3 ? 'bg-blue-500' : 'bg-gray-300'"
        ></button>
        <button 
            @click="setActiveSlide(4)" 
            class="w-4 h-4 rounded-full transition-colors duration-200" 
            :class="activeSlide === 4 ? 'bg-blue-500' : 'bg-gray-300'"
        ></button>
    </div>
</div>
