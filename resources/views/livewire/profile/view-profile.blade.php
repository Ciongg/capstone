<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ tab: 'about' }">
    {{-- Status warnings - keep these at the very top --}}
    @auth
        @if(Auth::user()->hasInvalidInstitution())
            <div class="bg-orange-100 border-b border-orange-400 text-orange-700 px-4 py-3 relative mb-6 rounded-xl shadow" role="alert">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-orange-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">Your institution is no longer in our system. Some features will be limited.</span>
                </div>
            </div>
        @endif

        @if(Auth::user()->isDowngradedResearcher())
            <div class="bg-yellow-100 border-b border-yellow-400 text-yellow-700 px-4 py-3 relative mb-6 rounded-xl shadow" role="alert">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-yellow-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">Your account has researcher potential! However, your institution is not yet registered in our system.</span>
                </div>
            </div>
        @endif
    @endauth

    {{-- Profile Information Container --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden w-full mb-4">
        {{-- Banner Image --}}
        <div class="h-48 bg-[#03b8ff] relative w-full">
            {{-- Add a banner change option if needed --}}
        </div>

        {{-- Profile Info Section --}}
        <div class="px-4 sm:px-6 pt-0 pb-6"> {{-- Reduced horizontal padding on mobile --}}
            <div class="flex flex-col md:flex-row">
                {{-- Profile Picture (overlapping the banner) --}}
                <div class="relative -mt-20 mb-4 md:mb-0 flex justify-center md:justify-start">
                    <div class="relative w-36 h-36"> {{-- Fixed dimensions to match the image --}}
                        {{-- Display current profile photo --}}
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                             class="w-full h-full rounded-full object-cover border-4 border-white shadow-md">

                        {{-- Hidden file input triggered by the label --}}
                        <input type="file" id="photo" class="hidden" wire:model="photo">

                        {{-- Label acting as the edit button --}}
                        <label for="photo" 
                               class="absolute bottom-2 right-2 bg-blue-500 text-white rounded-full p-2 cursor-pointer hover:bg-blue-600 z-10 flex items-center justify-center" 
                               title="Change Photo">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6v2H7v-2z"/>
                            </svg>
                        </label>

                        {{-- Loading indicator --}}
                        <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-full">
                            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('photo') <span class="text-red-500 text-sm block text-center mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- User Info (to the right of profile picture) --}}
                <div class="md:ml-6 mt-4 md:mt-0 md:pt-5 flex-1">
                    {{-- Wrapper for name/details and buttons - vertical on mobile, horizontal on large screens --}}
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start">
                        {{-- Left column with name and details - always stacked --}}
                        <div class="text-center md:text-left">
                            {{-- User name display --}}
                            <div class="mb-3">
                                <div class="text-3xl font-bold">{{ $user?->name ?? 'Unknown User' }}</div>
                            </div>
                            
                            {{-- Institution, Role, Trust Score in a column --}}
                            <div class="flex flex-col mb-6 lg:mb-0 text-gray-600">
                                @if($user->institution)
                                    <div class="text-sm">{{ $user->institution?->name }}</div>
                                @endif
                                <div class="text-sm capitalize">{{ $user?->type ?? 'User' }}</div>
                                {{-- Trust Score Display --}}
                                <div class="flex items-center justify-center md:justify-start text-sm mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-gray-600">Trust Score: <span class="text-green-600 font-bold">{{ $user?->trust_score ?? 0 }}/100</span></span>
                                </div>
                            </div>
                        </div>

                        {{-- Action buttons - right side on large screens, stacked below on small screens --}}
                        <div class="flex flex-col md:flex-row gap-3 items-center md:items-start">
                            {{-- Help Request Button --}}
                            <button
                                x-data
                                x-on:click="$dispatch('open-modal', {name: 'support-request-modal'})"
                                class="flex items-center justify-center space-x-2 py-2 px-4 text-white bg-[#03b8ff] hover:bg-[#0299d5] rounded-lg shadow-md transition-colors w-full md:w-40"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                </svg>
                                <span class="font-semibold">Help Request</span>
                            </button>
                            
                            {{-- Logout Button --}}
                            <form method="POST" action="{{ route('logout') }}" class="w-full md:w-auto">
                                @csrf
                                <button type="submit" class="flex items-center justify-center space-x-2 py-2 px-4 text-white bg-red-500 hover:bg-red-600 rounded-lg shadow-md transition-colors w-full md:w-40">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                    <span class="font-semibold">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dynamic Content Container with Tabs --}}
    <div class="bg-white rounded-xl shadow-lg mt-4 w-full">
        {{-- Navigation Tabs - Modified for consistent centering --}}
        <div class="px-3 sm:px-6 border-b border-gray-200">
            <div class="flex justify-center overflow-x-auto space-x-6 md:space-x-12 py-3 scrollbar-hide">
                <button
                    class="px-3 sm:px-4 py-2 font-medium transition relative flex-shrink-0"
                    :class="tab === 'about' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-500'"
                    @click="tab = 'about'"
                >
                    About
                    <div :class="tab === 'about' ? 'absolute bottom-0 left-0 w-full h-0.5 bg-blue-600' : ''" class="transform -translate-y-2"></div>
                </button>
                <button
                    class="px-3 sm:px-4 py-2 font-medium transition relative"
                    :class="tab === 'surveys' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-500'"
                    @click="tab = 'surveys'"
                >
                    My Surveys
                    <div :class="tab === 'surveys' ? 'absolute bottom-0 left-0 w-full h-0.5 bg-blue-600' : ''" class="transform -translate-y-2"></div>
                </button>
                <button
                    class="px-3 sm:px-4 py-2 font-medium transition relative"
                    :class="tab === 'history' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-500'"
                    @click="tab = 'history'"
                >
                    Survey History
                    <div :class="tab === 'history' ? 'absolute bottom-0 left-0 w-full h-0.5 bg-blue-600' : ''" class="transform -translate-y-2"></div>
                </button>
                
                {{-- Only show Institution Demographics tab for Institution Admins --}}
                @if($user->type === 'institution_admin')
                <button
                    class="px-3 sm:px-4 py-2 font-medium transition relative"
                    :class="tab === 'institution_demographics' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-500'"
                    @click="tab = 'institution_demographics'"
                >
                    Institution Demographics
                    <div :class="tab === 'institution_demographics' ? 'absolute bottom-0 left-0 w-full h-0.5 bg-blue-600' : ''" class="transform -translate-y-2"></div>
                </button>
                @endif
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="p-5 w-full">
            <div x-show="tab === 'about'" class="w-full">
                <livewire:profile.view-about :user="$user" />
            </div>
            <div x-show="tab === 'surveys'" class="w-full">
                <livewire:surveys.form-index :user="$user" />
            </div>
            <div x-show="tab === 'history'" class="w-full">
                <livewire:profile.view-history :user="$user" />
            </div>
            

            {{-- Institution Demographics Tab Content - Only loaded for Institution Admins --}}
            @if($user->type === 'institution_admin')
            <div x-show="tab === 'institution_demographics'" class="w-full">
                <livewire:profile.institution.set-institution-demographic :user="$user" />
            </div>
            @endif
        </div>
    </div>
</div>
