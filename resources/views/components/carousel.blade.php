<div 
    x-data="{
        activeSlide: 1,
        slides: 4,
        interval: 5000,
        autoplayInterval: null,
        init() {
            this.startAutoplay();
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
        },
        nextSlide() {
            this.activeSlide = this.activeSlide === this.slides ? 1 : this.activeSlide + 1;
            this.startAutoplay();
        },
        prevSlide() {
            this.activeSlide = this.activeSlide === 1 ? this.slides : this.activeSlide - 1;
            this.startAutoplay();
        }
    }"
    class="w-full h-full bg-white relative overflow-hidden"
    x-init="init()"
    wire:ignore
>
    <!-- Carousel Container with hover group -->
    <div class="relative w-full h-full group">
        <!-- Navigation Buttons - Only show on hover -->
        <div class="absolute top-1/2 w-full px-4 z-10 flex items-center justify-between -translate-y-1/2 pointer-events-none">
            <button 
                @click="prevSlide()"
                class="p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 focus:outline-none transition-all duration-200 pointer-events-auto opacity-0 group-hover:opacity-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button 
                @click="nextSlide()"
                class="p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 focus:outline-none transition-all duration-200 pointer-events-auto opacity-0 group-hover:opacity-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- Slides Container -->
        <div class="flex transition-transform duration-300 ease-in-out h-full"
             :style="{ transform: `translateX(-${(activeSlide - 1) * 100}%)` }">
            
            <!-- Slide 1 -->
            <div class="w-full flex-shrink-0 h-full p-8 flex flex-col justify-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Help Researchers In Need!</h2>
                <p class="text-gray-600 mb-8 text-center">Browse through your feed to find latest surveys from different categories</p>
                
                <!-- Feed Image -->
                <div class="w-full px-4 flex items-center justify-center">
                    <img src="{{ asset('images/landing/feed-carousel.png') }}" alt="Feed" 
                         class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="w-full flex-shrink-0 h-full p-8 flex flex-col justify-center items-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Gain Rewards</h2>
                <p class="text-gray-600 mb-8 text-center">Answer Surveys and get points to turn in for real rewards</p>
                
                <!-- Rewards Image -->
                <div class="w-full px-4 flex items-center justify-center">
                    <img src="{{ asset('images/landing/rewards-carousel.png') }}" alt="Rewards" 
                         class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="w-full flex-shrink-0 h-full p-8 flex flex-col justify-center items-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Find or Create Targeted Surveys</h2>
                <p class="text-gray-600 mb-8 text-center">Matching respondents with surveys through target demographics</p>
                
                <!-- Targeted Image -->
                <div class="w-full px-4 flex items-center justify-center">
                    <img src="{{ asset('images/landing/targeted-carousel.png') }}" alt="Targeted Surveys" 
                         class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
                </div>
            </div>
            
            <!-- Slide 4 -->
            <div class="w-full flex-shrink-0 h-full p-8 flex flex-col justify-center items-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-3 text-center">Create Forms Easily</h2>
                <p class="text-gray-600 mb-8 text-center">Use our built in form builder and share it to respondents instantly</p>
                
                <!-- Form Builder Image -->
                <div class="w-full px-4 flex items-center justify-center">
                    <img src="{{ asset('images/landing/form-builder-carousel.png') }}" alt="Form Builder" 
                         class="w-full max-h-[280px] object-contain rounded-lg shadow-xl">
                </div>
            </div>
        </div>
        
        <!-- Pagination Dots - Back at the bottom -->
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
</div>
         
