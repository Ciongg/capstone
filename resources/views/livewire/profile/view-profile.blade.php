<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ tab: 'about' }">
    {{-- Status warnings - keep these at the very top --}}

    @auth
        @if(Auth::user()->hasInvalidInstitution())
            <div class="mt-4 bg-orange-100 border-b border-orange-400 text-orange-700 px-4 py-3 relative mb-6 rounded-xl shadow" role="alert">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-orange-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">Your institution is no longer in our system. Some features will be limited.</span>
                </div>
            </div>
        @endif

        @if(Auth::user()->isDowngradedResearcher())
            <div class="mt-4 bg-yellow-100 border-b border-yellow-400 text-yellow-700 px-4 py-3 relative mb-6 rounded-xl shadow" role="alert">
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
    <div class="bg-white rounded-xl shadow-lg w-full mb-4 mt-8" style="overflow: visible;">
        {{-- Banner Image --}}
        <div class="h-48 bg-[#03b8ff] relative w-full rounded-t-xl">
            {{-- Add a banner change option if needed --}}
        </div>

        {{-- Profile Info Section --}}
        <div class="px-4 sm:px-6 pt-0 pb-6" style="overflow: visible;">
            <div class="flex flex-col md:flex-row" style="overflow: visible;">
                {{-- Profile Picture (overlapping the banner) --}}
                <div class="relative -mt-20 mb-4 md:mb-0 flex justify-center md:justify-start">
                    <button
                        type="button"
                        class="cursor-pointer relative w-36 h-36 group focus:outline-none"
                        x-on:click="$dispatch('open-modal', {name: 'edit-profile-modal'})"
                        aria-label="Edit profile photo"
                    >
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                             class="w-full h-full rounded-full object-cover border-4 border-white shadow-md transition-colors duration-200 group-hover:border-blue-500">
                        <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-full">
                            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </button>
                    @error('photo') <span class="text-red-500 text-sm block text-center mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- User Info (to the right of profile picture) --}}
                <div class="md:ml-6 mt-4 md:mt-0 md:pt-5 flex-1" style="overflow: visible;">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start" style="overflow: visible;">
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
                                
                                {{-- Trust Score Display with Status --}}
                                <div class="flex flex-col items-center md:items-start mt-1">
                                    <div class="flex items-center justify-center md:justify-start text-sm">
                                        <span class="font-semibold mr-1">Trust Score:</span> {{ $user->trust_score ?? 0 }}/100
                                        
                                        {{-- Trust Score Status Indicator --}}
                                        @php 
                                            $trustScore = $user->trust_score ?? 0;
                                        @endphp
                                        
                                        @if($trustScore > 70)
                                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Excellent</span>
                                        @elseif($trustScore > 40)
                                            <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">Probationary</span>
                                        @elseif($trustScore > 20)
                                            <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs">Restricted</span>
                                        @else
                                            <span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-800 rounded-full text-xs">Archived</span>
                                        @endif
                                    </div>
                                    
                                    {{-- Trust Score Warning Messages --}}
                                    @if($trustScore <= 70 && $trustScore > 40)
                                        <div class="mt-1 text-xs text-yellow-700 bg-yellow-50 p-2 rounded w-full md:w-auto">
                                            <span class="font-medium">Warning:</span> You can answer surveys but will not earn points or purchase rewards.
                                        </div>
                                    @elseif($trustScore <= 40 && $trustScore > 20)
                                        <div class="mt-1 text-xs text-red-700 bg-red-50 p-2 rounded w-full md:w-auto">
                                            <span class="font-medium">Severe Warning:</span> You cannot answer surveys. If your trust score reaches 20, your account will be archived.
                                        </div>
                                    @elseif($trustScore <= 20)
                                        <div class="mt-1 text-xs text-gray-700 bg-gray-100 p-2 rounded w-full md:w-auto">
                                            <span class="font-medium">Account Status:</span> Your account is pending archive due to low trust score.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            
                        </div>

                        {{-- Action buttons - Help Request and Settings --}}
                        <div class="flex flex-col md:flex-row items-center gap-3 mt-4 md:mt-0 justify-center md:justify-start">
                            {{-- Help Request Button --}}
                            <button
                                x-on:click="$dispatch('open-modal', {name: 'create-support-request-modal'})"
                                class="cursor-pointer flex items-center justify-center space-x-2 py-2.5 px-4 text-white bg-[#03b8ff] hover:bg-[#0299d5] rounded-lg shadow-md transition-colors"
                                aria-label="Help Request"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                </svg>
                                <span class="font-semibold">Help Request</span>
                            </button>

                            {{-- Settings Dropdown --}}
                            <div
                                class="relative"
                                x-data="{ open: false }"
                                @keydown.escape.window="open=false"
                                @open-modal.window="open=false"
                                style="z-index: 20;"
                            >
                                <button
                                    x-ref="settingsButton"
                                    @click="open = !open"
                                    class="cursor-pointer flex items-center justify-center p-3 text-white bg-gray-700 hover:bg-gray-800 rounded-full shadow-md transition-colors"
                                    aria-label="Settings"
                                    aria-haspopup="true"
                                    :aria-expanded="open.toString()"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.065 2.572c.94 1.543-.827 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37A1.724 1.724 0 003.32 13.675c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                </button>

                                <div
                                    x-ref="settingsMenu"
                                    x-show="open"
                                    x-transition
                                    @click.outside="open = false"
                                    class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-xl p-3"
                                    style="z-index: 30;"
                                    x-cloak
                                >
                                    <div class="flex flex-col gap-2">
                                        {{-- Edit Profile --}}
                                        <button
                                            x-on:click="$dispatch('open-modal', {name: 'edit-profile-modal'}); open=false"
                                            class="cursor-pointer flex items-center justify-center space-x-2 py-2.5 px-4 text-white bg-gray-400 hover:bg-gray-500 rounded-lg shadow-md transition-colors w-full"
                                        >
                                            <svg class="w-5 h-5" fill="white" viewBox="0 -0.5 21 21" xmlns="http://www.w3.org/2000/svg">
                                                <g fill="white"><path d="M384,209.210475 L384,219 L363,219 L363,199.42095 L373.5,199.42095 L373.5,201.378855 L365.1,201.378855 L365.1,217.042095 L381.9,217.042095 L381.9,209.210475 L384,209.210475 Z M370.35,209.51395 L378.7731,201.64513 L380.4048,203.643172 L371.88195,212.147332 L370.35,212.147332 L370.35,209.51395 Z M368.25,214.105237 L372.7818,214.105237 L383.18415,203.64513 L378.8298,199 L368.25,208.687714 L368.25,214.105237 Z"/></g>
                                            </svg>
                                            <span class="font-semibold">Edit Profile</span>
                                        </button>

                                        {{-- Two-Factor Authentication --}}
                                        <button
                                            x-on:click="$dispatch('open-modal', {name: 'two-factor-modal'}); open=false"
                                            class="cursor-pointer flex items-center justify-center space-x-2 py-2.5 px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-lg shadow-md transition-colors w-full"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                            </svg>
                                            <span class="font-semibold">Two-Factor Auth</span>
                                        </button>

                                        {{-- Logout --}}
                                        <form method="POST" action="{{ route('logout') }}" class="w-full" x-on:submit="open=false">
                                            @csrf
                                            <button type="submit" class="cursor-pointer flex items-center justify-center space-x-2 py-2.5 px-4 text-white bg-red-500 hover:bg-red-600 rounded-lg shadow-md transition-colors w-full">
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
                        {{-- End Action buttons --}}
                    </div>
                </div>
            </div>
        </div>
        {{-- Success message for profile update --}}
    </div>

    {{-- Dynamic Content Container with Tabs --}}
    <div class="bg-white rounded-xl shadow-lg mt-4 w-full" style="position: relative; z-index: 1;">
        {{-- Navigation Tabs - Modified for expanding design --}}
        <div class="border-b border-gray-200">
            <div class="flex -mb-px w-full">
                <button
                    class="cursor-pointer py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition"
                    :class="tab === 'about' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300'"
                    @click="tab = 'about'"
                >
                    About
                </button>
                {{-- Remove the condition so all users see My Surveys tab --}}
                <button
                    class="cursor-pointer py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition"
                    :class="tab === 'surveys' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300'"
                    @click="tab = 'surveys'"
                >
                    My Surveys
                </button>
                <button
                    class="cursor-pointer py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition"
                    :class="tab === 'history' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300'"
                    @click="tab = 'history'"
                >
                    Survey History
                </button>
                
               
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

        </div>
    </div>

    {{-- Support Request Modal --}}
    <x-modal name="create-support-request-modal" title="Support Request">
        <livewire:support-requests.create-support-request-modal />
    </x-modal>
    {{-- Edit Profile Modal --}}
    <x-modal name="edit-profile-modal" title="Edit Profile">
        @livewire('profile.modal.edit-profile-modal', ['user' => $user], key('edit-profile-modal-' . $user->id))
    </x-modal>

    {{-- Two-Factor Authentication Modal - same pattern as edit-profile --}}
    <x-modal name="two-factor-modal" title="Two-Factor Authentication">
        @livewire('profile.two-factor-authentication', key('2fa-' . auth()->id()))
    </x-modal>
</div>

@push('scripts')
<script>
    window.addEventListener('profile-updated', function (event) {
        Swal.fire({
            icon: 'success',
            title: 'Profile Updated',
            text: event.detail?.message || 'Your profile has been updated successfully.',
            confirmButtonColor: '#03b8ff',
        });
    });
</script>
@endpush

