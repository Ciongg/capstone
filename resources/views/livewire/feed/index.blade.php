{{-- filepath: resources\views\livewire\feed\index.blade.php --}}
<div class="max-w-6xl mx-auto py-8" x-data="{ fullscreenImageSrc: null }">
    {{-- Top Bar --}}
    <div class="flex justify-between items-center mb-8">
        {{-- Search Bar --}}
        <div class="flex-1 max-w-md">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Search surveys..."
                class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-400 focus:outline-none"
            />
        </div>
        {{-- User Points --}}
        <div class="flex items-center ml-6">
            <svg class="w-8 h-8 text-blue-400 mr-2" fill="white" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polygon points="12 2 22 9 16 22 8 22 2 9 12 2" />
                <line x1="12" y1="2" x2="12" y2="22" />
                <line x1="2" y1="9" x2="22" y2="9" />
                <line x1="8" y1="22" x2="16" y2="22" />
            </svg>
            <span class="text-2xl font-bold text-gray-800">{{ $userPoints }}</span>
        </div>
    </div>

    {{-- Surveys Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        @forelse($surveys as $survey)
            <div class="relative bg-white shadow-lg rounded-xl p-0 flex flex-col min-h-[500px]">
                {{-- Header: Profile + Name + Points (row), then Title (below, still gray) --}}
                <div class="w-full px-4 py-3 rounded-t-xl bg-gray-100 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center mb-2">
                        {{-- Use the profile photo URL --}}
                        <img src="{{ $survey->user->profile_photo_url }}" alt="{{ $survey->user->name ?? 'User' }}" class="w-10 h-10 rounded-full object-cover mr-3">
                        <span class="text-base font-semibold text-gray-800 truncate mr-4">{{ $survey->user->name ?? 'User' }}</span>
                        <div class="flex-1"></div>
                        <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full">
                            {{-- Diamond (gem) icon (white) --}}
                            <svg class="w-5 h-5 text-white mr-1" fill="white" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polygon points="12 2 22 9 16 22 8 22 2 9 12 2" />
                                <line x1="12" y1="2" x2="12" y2="22" />
                                <line x1="2" y1="9" x2="22" y2="9" />
                                <line x1="8" y1="22" x2="16" y2="22" />
                            </svg>
                            <span class="font-extrabold text-white drop-shadow">{{ $survey->points_allocated ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="w-full">
                        <span class="text-sm font-semibold text-left truncate block">{{ $survey->title }}</span>
                    </div>
                </div>
                {{-- Survey Image --}}
                <div class="w-full flex-grow mt-4 flex items-center justify-center mb-2 relative px-4 min-h-0">
                    @if($survey->image_path)
                        @php
                            $imageUrl = asset('storage/' . $survey->image_path);
                        @endphp
                        <button @click="fullscreenImageSrc = '{{ $imageUrl }}'" class="cursor-pointer w-full h-full flex items-center justify-center">
                            <img src="{{ $imageUrl }}"
                                 alt="Survey image for {{ $survey->title }}"
                                 class="rounded-lg object-contain max-w-full max-h-[340px]" />
                        </button>
                    @else
                        <div class="w-full h-full max-h-[340px] bg-gray-200 flex items-center justify-center rounded-lg">
                            <span class="text-gray-500 text-sm">no image</span>
                        </div>
                    @endif
                </div>

                {{-- Tags Section --}}
                <div class="w-full px-4 mb-3 flex-shrink-0">
                    <div class="flex flex-wrap gap-2 justify-center min-h-[36px] items-center">
                        @if($survey->tags->isEmpty())
                            {{-- Display three empty styled tags if no tags are present --}}
                            <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                            <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                            <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                        @else
                            @php
                                // Always show exactly 3 tags or placeholders
                                $tagsToShow = $survey->tags->take(3);
                                $remainingCount = $survey->tags->count() - 3;
                            @endphp
                            
                            @foreach($tagsToShow as $tag)
                                <span class="px-3 py-2 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full shadow-md overflow-hidden whitespace-nowrap max-w-[100px] text-ellipsis">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                            
                            {{-- Fill empty slots with placeholders if fewer than 3 tags --}}
                            @for($i = $tagsToShow->count(); $i < 3; $i++)
                                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                            @endfor
                            
                            
                        @endif
                    </div>
                </div>

                {{-- Read More Button --}}
                <div class="w-full flex justify-start mt-auto mb-4 px-4 flex-shrink-0">
                    <button
                        x-data
                        x-on:click="$dispatch('open-modal', {name : 'view-survey-{{ $survey->id }}'})"
                        class="px-4 py-1 rounded-full font-bold text-white"
                        style="background-color: #00BBFF;"
                    >
                        Read More
                    </button>
                </div>
             
            </div>

            <x-modal name="view-survey-{{ $survey->id }}" title="Survey Details">
                <livewire:feed.modal.view-survey-modal :survey="$survey" />
            </x-modal>
        @empty
            <div class="col-span-3 text-gray-500 text-center">No published surveys available.</div>
        @endforelse
    </div>

    <!-- Fullscreen Image Overlay -->
    <div x-show="fullscreenImageSrc"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="fullscreenImageSrc = null" {{-- Click background to close --}}
         @keydown.escape.window="fullscreenImageSrc = null" {{-- Press Escape to close --}}
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4 cursor-pointer"
         style="display: none;"> {{-- Add display:none to prevent flash on load --}}
        
        <img :src="fullscreenImageSrc" 
             alt="Fullscreen Survey Image" 
             class="max-w-full max-h-full object-contain"
             @click.stop {{-- Prevent closing when clicking the image itself --}}
             />
        
        {{-- Larger, easier-to-tap close button for mobile --}}
        <button @click="fullscreenImageSrc = null" 
                class="cursor-pointer absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-white text-4xl sm:text-3xl font-bold leading-none rounded-full hover:bg-black hover:bg-opacity-25 focus:outline-none">
            &times;
        </button>
    </div>
</div>
