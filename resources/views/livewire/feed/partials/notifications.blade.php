{{-- Account Upgrade Notification --}}
@if($accountUpgrade)
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-transition 
        class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" 
        role="alert"
    >
        <div class="flex items-center">
            <svg class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ $accountUpgrade }}</span>
        </div>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <svg class="h-5 w-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

{{-- Account Downgrade Notification --}}
@if($accountDowngrade)
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-transition 
        class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" 
        role="alert"
    >
        <div class="flex items-center">
            <svg class="h-6 w-6 text-yellow-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="font-medium">{{ $accountDowngrade }}</span>
        </div>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <svg class="h-5 w-5 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

{{-- Institution Invalid Warning --}}
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