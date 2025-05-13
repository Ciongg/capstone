{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\feed\index.blade.php --}}
{{-- Keep Alpine for non-filter UI like fullscreenImageSrc, notifications, top bar, filters, etc. as in your full file --}}
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
        {{-- Search Bar with Icons --}}
        <div class="flex-1 max-w-md relative">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search" {{-- Use .live for immediate feedback or .debounce --}}
                    placeholder="Search surveys..."
                    class="w-full pl-10 pr-10 py-2 rounded-full border border-gray-300 focus:border-blue-400 focus:outline-none"
                />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <button 
                        wire:click="toggleFilterPanel" {{-- Livewire action --}}
                        class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200"
                        :class="{'text-blue-500': {{ $showFilterPanel ? 'true' : 'false' }}}" {{-- Bind class to Livewire property --}}
                        title="Filter settings"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        {{-- User Points --}}
        <div class="flex flex-row items-center ml-6">
            <span class="text-4xl text-[#FFB349] font-bold">{{ $userPoints }}</span>
            <svg class="w-8 h-8 text-[#FFB349] ml-2" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
            </svg>
        </div>
    </div>

    {{-- Applied Filters Display --}}
    @if(!empty($search) || $activeTopicId || $activePanelTagId)
        <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-md">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-medium text-blue-700">Filtered by:</span>
                @if(!empty($search))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Search: "{{ $search }}"
                        <button wire:click="clearSearch" class="ml-1 text-gray-500 hover:text-gray-700 focus:outline-none" title="Clear search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </button>
                    </span>
                @endif
                @if($activeTopicId && ($topic = $topics->firstWhere('id', $activeTopicId)))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Topic: {{ $topic->name }}
                        <button wire:click="clearTopicFilter" class="ml-1 text-green-500 hover:text-green-700 focus:outline-none" title="Clear topic filter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </button>
                    </span>
                @endif
                @if($activePanelTagId && ($tag = \App\Models\Tag::find($activePanelTagId)))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        Tag: {{ $tag->name }}
                        <button wire:click="clearPanelTagFilter" class="ml-1 text-indigo-500 hover:text-indigo-700 focus:outline-none" title="Clear tag filter">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </span>
                @endif
                <button wire:click="clearAllFilters" class="text-xs text-blue-600 hover:text-blue-800 underline ml-auto focus:outline-none">
                    Clear all filters
                </button>
            </div>
        </div>
    @endif

    {{-- Filter Panel - Controlled by Livewire --}}
    @if($showFilterPanel)
    <div 
        class="mb-6 p-4 bg-white rounded-lg shadow-md border border-gray-100"
        wire:transition.origin.top.left {{-- Optional: add a Livewire transition --}}
    >
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-medium text-gray-700">Filter Surveys by Tag</h3>
            <button wire:click="$set('showFilterPanel', false)" class="text-gray-400 hover:text-gray-600" title="Close panel">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        
        @if($activePanelTagId && ($tag = \App\Models\Tag::find($activePanelTagId)))
            <div class="mb-4 p-2 bg-blue-50 border border-blue-100 rounded-md">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-700">Selected: {{ $tag->name }}</span>
                    <button wire:click="clearPanelTagFilter" class="text-xs text-blue-600 hover:text-blue-800 underline">
                        Clear this tag
                    </button>
                </div>
            </div>
        @endif
        
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @forelse($tagCategories as $category)
                <div wire:key="filter-category-{{ $category->id }}">
                    <h4 class="font-semibold text-gray-600 mb-2">{{ $category->name }}</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        @foreach($category->tags as $tag)
                            <button 
                                wire:click="togglePanelTagFilter({{ $tag->id }})"
                                wire:key="filter-tag-{{ $tag->id }}"
                                type="button"
                                class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors duration-150
                                       {{ $activePanelTagId == $tag->id 
                                           ? 'bg-blue-500 text-white font-semibold shadow-md' 
                                           : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                            >
                                {{ $tag->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No tags available for filtering.</p>
            @endforelse
        </div>
        
        <div class="mt-6 flex justify-end space-x-2">
            <button 
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                wire:click="$set('showFilterPanel', false)"
            >
                Cancel
            </button>
            <button 
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
                wire:click="$set('showFilterPanel', false)"
            >
                Done
            </button>
        </div>
    </div>
    @endif

    {{-- Topic Filters with enhanced scrolling (Alpine.js for UI enhancement is fine here) --}}
    <div class="mb-6 relative group" 
         x-data="{ 
            scroll: 0,
            scrollMax: 0,
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
         x-init="initScroll()" 
         @resize.window="initScroll()"
         wire:ignore {{-- Prevent Livewire from interfering with this Alpine component if issues arise --}}
         >
        <div class="flex overflow-x-auto py-2 px-1 scrollbar-hide scroll-smooth" x-ref="scrollContainer" style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
            <div class="flex space-x-3 min-w-max">
                @foreach($topics as $topic)
                    <button 
                        wire:click="toggleTopicFilter({{ $topic->id }})"
                        wire:key="topic-button-{{ $topic->id }}"
                        class="px-5 py-2.5 text-base font-medium rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 whitespace-nowrap border border-gray-100 
                               {{ $activeTopicId == $topic->id ? 'bg-blue-500 text-white' : 'bg-white text-gray-700' }}"
                    >
                        {{ $topic->name }}
                    </button>
                @endforeach
            </div>
        </div>
        {{-- Scroll Buttons (Alpine controlled) --}}
        <button 
            @click="$refs.scrollContainer.scrollBy({left: -200, behavior: 'smooth'})" 
            class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 focus:outline-none opacity-0 group-hover:opacity-75 hover:opacity-100 transition-opacity z-10"
            :class="{'pointer-events-none opacity-25': scroll <= 0}"
            x-show="scrollMax > 0"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button 
            @click="$refs.scrollContainer.scrollBy({left: 200, behavior: 'smooth'})" 
            class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 focus:outline-none opacity-0 group-hover:opacity-75 hover:opacity-100 transition-opacity z-10"
            :class="{'pointer-events-none opacity-25': scroll >= scrollMax}"
            x-show="scrollMax > 0"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Add this to your main layout/app.blade.php or directly here if needed --}}
    <style>
        /* Only the minimum required CSS that's not in Tailwind by default */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{-- Surveys Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8" id="survey-grid">
        @forelse($surveys as $survey)
            <div wire:key="survey-card-{{ $survey->id }}" class="relative bg-white shadow-lg rounded-xl p-0 flex flex-col min-h-[500px]">
                {{-- Header --}}
                <div class="w-full px-4 py-3 rounded-t-xl bg-gray-100 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center mb-2">
                        <img src="{{ $survey->user->profile_photo_url }}" alt="{{ $survey->user->name ?? 'User' }}" class="w-10 h-10 rounded-full object-cover mr-3">
                        <span class="text-base font-semibold text-gray-800 truncate mr-4">{{ $survey->user->name ?? 'User' }}</span>
                        <div class="flex-1"></div>
                        <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full">
                            <span class="font-bold text-white drop-shadow">{{ $survey->points_allocated ?? 0 }}</span>
                            <svg class="w-6 h-6 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </div>
                    </div>
                    <div class="w-full">
                        <span class="text-sm font-semibold text-left truncate block">{{ $survey->title }}</span>
                    </div>
                </div>
                
                {{-- Image --}}
                <div class="w-full flex-grow mt-4 flex items-center justify-center mb-2 relative px-4 min-h-0">
                    @if($survey->image_path)
                        @php $imageUrl = asset('storage/' . $survey->image_path); @endphp
                        <button @click="fullscreenImageSrc = '{{ $imageUrl }}'" class="cursor-pointer w-full h-full flex items-center justify-center">
                            <img src="{{ $imageUrl }}" alt="Survey image for {{ $survey->title }}" class="rounded-lg object-contain max-w-full max-h-[340px]" />
                        </button>
                    @else
                        <div class="w-full h-full max-h-[340px] bg-gray-200 flex items-center justify-center rounded-lg"><span class="text-gray-500 text-sm">no image</span></div>
                    @endif
                </div>

                {{-- Tags Section --}}
                <div class="w-full px-4 mb-3 flex-shrink-0">
                    <div class="flex flex-wrap gap-2 justify-center min-h-[36px] items-center">
                        @if($survey->tags->isEmpty())
                            <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                            <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                            <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                        @else
                            @php $tagsToShow = $survey->tags->take(3); @endphp
                            @foreach($tagsToShow as $tag)
                                <button
                                    wire:click="filterByTag({{ $tag->id }})"
                                    wire:key="survey-{{ $survey->id }}-tag-{{ $tag->id }}"
                                    class="px-3 py-2 text-xs font-semibold rounded-full shadow-md overflow-hidden whitespace-nowrap max-w-[100px] text-ellipsis
                                        {{ $activeTagId == $tag->id ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $tag->name }}
                                </button>
                            @endforeach
                            @for($i = $tagsToShow->count(); $i < 3; $i++)
                                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                            @endfor
                        @endif
                    </div>
                </div>

                {{-- Read More Button & Topic Badge --}}
                <div class="w-full flex justify-between items-center mt-auto mb-4 px-4 flex-shrink-0">
                    <button
                        @click="
                            $wire.set('modalSurveyId', null).then(() => {
                                $wire.set('modalSurveyId', {{ $survey->id }});
                                $nextTick(() => $dispatch('open-modal', { name: 'surveyDetailModal' }));
                            })
                        "
                        class="px-4 py-1 rounded-full font-bold text-white cursor-pointer transition
                               bg-[#00BBFF] hover:bg-[#0099cc] hover:shadow-lg focus:outline-none"
                        type="button"
                    >
                        Read More
                    </button>
                    @if($survey->topic)
                        <span class="text-xs text-gray-500">{{ $survey->topic->name }}</span>
                    @endif
                </div>
            </div> {{-- End of survey-card div --}}
        @empty
            <div class="col-span-1 sm:col-span-2 md:col-span-3 text-gray-500 text-center py-10">No published surveys match your criteria.</div>
        @endforelse
    </div> {{-- End of survey-grid div --}}

    {{-- Single Modal Shell - ALWAYS in the DOM. Its visibility is controlled by Alpine. --}}
    <x-modal name="surveyDetailModal" title="Survey Details" @close="$wire.closeSurveyModal()">
        {{-- Content INSIDE the modal is conditional based on Livewire's $modalSurveyId --}}
        @if($modalSurveyId)
            <livewire:feed.modal.view-survey-modal :survey="\App\Models\Survey::find($modalSurveyId)" :wire:key="'modal-view-' . $modalSurveyId . '-' . now()->timestamp" />
        @else
            {{-- Placeholder for when modalSurveyId is null or content is loading --}}
            <div class="p-6 animate-pulse">
                <div class="h-6 bg-gray-300 rounded w-3/4 mb-4"></div>
                <div class="h-32 bg-gray-300 rounded mb-4"></div>
                <div class="space-y-3">
                    <div class="h-4 bg-gray-300 rounded"></div>
                    <div class="h-4 bg-gray-300 rounded w-5/6"></div>
                    <div class="h-4 bg-gray-300 rounded w-4/6"></div>
                </div>
            </div>
        @endif
    </x-modal>

    {{-- Fullscreen Image Overlay (Alpine controlled) --}}
    <div x-show="fullscreenImageSrc" @click.self="fullscreenImageSrc = null" class="fixed inset-0 bg-black bg-opacity-75 z-[100] flex items-center justify-center p-4" style="display: none;">
        <div class="relative">
            <button @click="fullscreenImageSrc = null" class="absolute -top-3 -right-3 m-2 text-white bg-black bg-opacity-50 rounded-full p-1.5 hover:bg-opacity-75 z-[101]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="fullscreenImageSrc" alt="Fullscreen image" class="max-w-full max-h-[90vh] rounded-lg shadow-md object-contain" />
        </div>
    </div>
</div> {{-- End of main max-w-6xl div --}}
