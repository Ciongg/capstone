<div class="max-w-3xl mx-auto py-10" x-data="{ tab: 'about' }">
    {{-- Institution Invalid Warning - ALWAYS DISPLAYED FOR INSTITUTION ADMINS WITH INVALID INSTITUTION --}}
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

        {{-- Downgraded Researcher Warning - ALWAYS DISPLAYED FOR RESPONDENTS WITH .EDU EMAILS --}}
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

    <!-- Profile Header -->
    <div class="flex flex-col items-center bg-white rounded-xl shadow p-8 mb-8">
        <!-- Profile Image -->
        <div class="relative mb-4">
            {{-- Display current profile photo --}}
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-full object-cover border-2 border-gray-200">

            {{-- Hidden file input triggered by the label --}}
            <input type="file" id="photo" class="hidden" wire:model="photo">

            {{-- Label acting as the edit button --}}
            <label for="photo" class="absolute bottom-2 right-2 bg-blue-500 text-white rounded-full p-1 cursor-pointer hover:bg-blue-600" title="Change Photo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6v2H7v-2z"/>
                </svg>
            </label>

            {{-- Loading indicator --}}
            <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-full">
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        @error('photo') <span class="text-red-500 text-sm mb-2">{{ $message }}</span> @enderror

        <!-- User Name -->
        <div class="text-2xl font-bold mb-1">{{ $user?->name ?? 'Unknown User' }}</div>
        <!-- Institution Name -->
        @if($user->institution)
        <div class="text-sm text-gray-500 mb-1">{{ $user->institution?->name }}</div>
        @endif
        <!-- User Type -->
        <div class="text-blue-500 font-semibold mb-1 capitalize">{{ $user?->type ?? 'User' }}</div>
        <!-- Trust Score -->
        <div class="text-sm text-gray-600 font-bold mb-2 ">
            Trust Score: {{ $user?->trust_score ?? 0 }}/100
        </div>
    </div>

    <!-- Profile Navigation Tabs -->
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex justify-center space-x-8 mb-6">
            <button
                class="px-4 py-2 font-semibold rounded transition"
                :class="tab === 'about' ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-600'"
                @click="tab = 'about'"
            >
                About
            </button>
            <button
                class="px-4 py-2 font-semibold rounded transition"
                :class="tab === 'surveys' ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-600'"
                @click="tab = 'surveys'"
            >
                My Surveys
            </button>
            <button
                class="px-4 py-2 font-semibold rounded transition"
                :class="tab === 'history' ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-600'"
                @click="tab = 'history'"
            >
                Survey History
            </button>
        </div>
        <!-- Dynamic Content Container -->
        <div class="min-h-[150px]">
            <div x-show="tab === 'about'">
                <livewire:profile.view-about :user="$user" />
            </div>
            <div x-show="tab === 'surveys'">
                <livewire:surveys.form-index :user="$user" />
            </div>
            <div x-show="tab === 'history'">
                <livewire:profile.view-history :user="$user" />
            </div>
        </div>
    </div>
</div>
