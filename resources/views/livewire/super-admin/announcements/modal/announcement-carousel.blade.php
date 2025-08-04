<div>
    <div x-data="{
        activeSlide: 0,
        slides: {{ count($announcements) }},
        expandedDescriptions: {},
        wordLimit: 20,
        fullscreenImageSrc: null,

        truncateText(text, wordLimit) {
            if (!text) return '';
            const words = text.trim().split(/\s+/);
            if (words.length <= wordLimit) {
                return text;
            }
            return words.slice(0, wordLimit).join(' ') + '...';
        },

        needsTruncation(text, wordLimit) {
            if (!text) return false;
            const words = text.trim().split(/\s+/);
            return words.length > wordLimit;
        }
    }">
        @if(count($announcements) > 0)
            <!-- Carousel Container -->
            <div class="relative w-full bg-white rounded-lg overflow-hidden">
                <!-- Image/Content Container with hover group -->
                <div class="relative overflow-hidden group">
                    <!-- Navigation Buttons - Only show on hover of the container -->
                    @if(count($announcements) > 1)
                        <div class="absolute top-1/2 w-full px-4 z-10 flex items-center justify-between -translate-y-1/2 pointer-events-none">
                            <button @click="activeSlide = (activeSlide - 1 + slides) % slides"
                                    class="p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 focus:outline-none transition-all duration-200 pointer-events-auto opacity-0 group-hover:opacity-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button @click="activeSlide = (activeSlide + 1) % slides"
                                    class="p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 focus:outline-none transition-all duration-200 pointer-events-auto opacity-0 group-hover:opacity-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Slides Container -->
                    <div class="flex transition-transform duration-300 ease-in-out"
                         :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
                        @foreach($announcements as $index => $announcement)
                        <div class="w-full flex-shrink-0">
                            @if($announcement->image_path)
                                <div class="relative pb-[56.25%] cursor-pointer"
                                     @click="fullscreenImageSrc = '{{ asset('storage/' . $announcement->image_path) }}'">
                                    <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                                         alt="{{ $announcement->title }}" 
                                         class="absolute inset-0 w-full h-full object-contain bg-gray-100">
                                    <!-- Title Overlay - positioned at bottom 40% of image -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 via-black/30 to-transparent" 
                                         style="height: 40%;">
                                        <div class="absolute bottom-0 left-0 right-0 p-4 flex items-end justify-start h-full">
                                            <h3 class="text-white font-bold text-lg sm:text-xl leading-tight drop-shadow-lg">
                                                {{ $announcement->title }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- No image: fixed aspect ratio container, with proper text handling -->
                                <div class="relative w-full" style="aspect-ratio: 16/9;">
                                    <div 
                                        class="absolute inset-0 flex flex-col bg-gray-50 border border-gray-200 rounded-lg px-16 py-8 text-center transition-all duration-300"
                                        x-data="{
                                            fullText: {{ json_encode($announcement->description ?? '') }},
                                            needsMore: false,
                                            expanded: false
                                        }"
                                        x-init="needsMore = needsTruncation(fullText, wordLimit); $watch('expanded', val => expandedDescriptions[{{ $index }}] = val); expanded = expandedDescriptions[{{ $index }}] ?? false"
                                    >
                                        <!-- Fixed title at top -->
                                        <h3 class="text-2xl font-bold text-gray-800 mb-4 w-full text-center flex-shrink-0">{{ $announcement->title }}</h3>
                                        
                                        @if($announcement->description)
                                            <!-- Description container with proper flex layout -->
                                            <div class="flex-1 flex flex-col min-h-0 items-center justify-center" :class="expanded ? 'justify-start' : 'justify-center'">
                                                <!-- Text content with better overflow handling -->
                                                <div class="w-full" :class="expanded ? 'flex-1 overflow-y-auto' : 'flex-none'">
                                                    <div 
                                                        class="text-base text-gray-600 leading-relaxed text-left mx-auto transition-all duration-300"
                                                        :class="expanded ? '' : 'line-clamp-4'"
                                                        style="white-space: pre-line; word-wrap: break-word;"
                                                        x-text="expanded ? fullText.trim() : truncateText(fullText, wordLimit)"
                                                    >
                                                    </div>
                                                </div>
                                                
                                                <!-- View More button - fixed at bottom -->
                                                <div class="flex justify-center mt-3 flex-shrink-0">
                                                    <button 
                                                        x-show="needsMore"
                                                        @click="expanded = !expanded"
                                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none px-3 py-1 rounded-md hover:bg-blue-50"
                                                        x-text="expanded ? 'View Less' : 'View More'">
                                                        View More
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Content Container - Below the image (only for image announcements, description truncation) -->
                @if($announcements->where('description', '!=', null)->count() > 0)
                <div class="relative overflow-hidden">
                    <div class="flex transition-transform duration-300 ease-in-out"
                         :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
                        @foreach($announcements as $index => $announcement)
                        <div class="w-full flex-shrink-0 p-6 bg-white">
                            @if($announcement->image_path && $announcement->description)
                                <div class="text-sm text-gray-600 leading-relaxed">
                                    <!-- Description with word-based truncation -->
                                    <div x-data="{ 
                                        fullText: {{ json_encode($announcement->description) }},
                                        needsMore: false
                                    }" x-init="needsMore = needsTruncation(fullText, wordLimit)">
                                        <pre class="whitespace-pre-wrap font-sans" 
                                             x-text="expandedDescriptions[{{ $index }}] ? fullText : truncateText(fullText, wordLimit)">
                                        </pre>
                                        <!-- View More/Less button - only show if needed -->
                                        <button 
                                            x-show="needsMore"
                                            x-on:click="expandedDescriptions[{{ $index }}] = !expandedDescriptions[{{ $index }}]"
                                            class="mt-2 text-blue-600 hover:text-blue-800 text-xs font-medium focus:outline-none"
                                            x-text="expandedDescriptions[{{ $index }}] ? 'View Less' : 'View More'"
                                        >
                                            View More
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Pagination Dots - Back at the bottom -->
                @if(count($announcements) > 1)
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                        @foreach($announcements as $index => $announcement)
                            <button @click="activeSlide = {{ $index }}"
                                    class="w-3 h-3 rounded-full transition-colors duration-200"
                                    :class="activeSlide === {{ $index }} ? 'bg-blue-600' : 'bg-gray-300'">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Fullscreen Image Overlay -->
            <div x-show="fullscreenImageSrc"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="fullscreenImageSrc = null"
                 @keydown.escape.window="fullscreenImageSrc = null"
                 class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4 cursor-pointer"
                 style="display: none;">
                
                <img :src="fullscreenImageSrc" 
                     alt="Fullscreen Announcement Image" 
                     class="max-w-full max-h-full object-contain"
                     @click.stop="">
                          
                <!-- Close button -->
                <button @click="fullscreenImageSrc = null" 
                        class="cursor-pointer absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-white text-4xl sm:text-3xl font-bold leading-none rounded-full hover:bg-black hover:bg-opacity-25 focus:outline-none">
                    &times;
                </button>
            </div>
        @else
            <div class="py-8 text-center text-gray-500">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Announcements</h2>
                <p>No announcements available at this time.</p>
            </div>
        @endif
    </div>
</div>


