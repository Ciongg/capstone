{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\feed\index.blade.php --}}
<div class="max-w-6xl mx-auto py-8" x-data="{ fullscreenImageSrc: null }">
    {{-- Account Upgrade Notification --}}
    @if (session('account-upgrade'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert" 
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false }, 8000)">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('account-upgrade') }}</span>
            </div>
            <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
                <svg class="h-5 w-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Account Downgrade Notification --}}
    @if (session('account-downgrade'))
        <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert" 
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false }, 8000)">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-yellow-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="font-medium">{{ session('account-downgrade') }}</span>
            </div>
            <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
                <svg class="h-5 w-5 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Institution Invalid Warning - ALWAYS DISPLAYED FOR INSTITUTION ADMINS WITH INVALID INSTITUTION --}}
    @auth
        @if(Auth::user()->hasInvalidInstitution())
            <div class="mb-6 bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded relative" role="alert">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-orange-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">Your institution is no longer in our system. Some features will be limited.</span>
                </div>
            </div>
        @endif
    @endauth

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
        <div class="flex flex-row items-center ml-6">
            <span class="text-4xl text-[#FFB349] font-bold">{{ $userPoints }}</span>
            <svg class="w-8 h-8 text-[#FFB349] ml-2" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
            </svg>
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
                        {{-- Diamond (gem) icon (white) --}}
                        <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full">
                            <span class="font-bold text-white drop-shadow">{{ $survey->points_allocated ?? 0 }}</span>
                            <svg class="w-6 h-6  text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </svg>
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
