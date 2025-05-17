<div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col">
    <!-- Reward Image and Cost Badge -->
    <div class="relative">
        <!-- Image for reward -->
        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
            @if($reward->image_path)
                <img src="{{ asset('storage/' . $reward->image_path) }}" alt="{{ $reward->name }}" class="h-full w-full object-cover">
            @else
                @if($reward->type == 'system')
                    {{-- System Reward Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                @elseif($reward->type == 'voucher')
                    {{-- Voucher Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                @elseif($reward->type == 'monetary')
                    {{-- Monetary Reward Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                @else
                    {{-- Default Fallback Icon (optional) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            @endif
        </div>
        
        <!-- Cost badge -->
        <div class="absolute top-3 right-3 flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full">
            <span class="font-bold text-white drop-shadow">{{ $reward->cost }}</span>
            <svg class="w-5 h-5 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
            </svg>
        </div>
    </div>
    
    <!-- Reward Info -->
    <div class="p-4 flex-grow">
        @if($reward->type == 'voucher' && $reward->vouchers->isNotEmpty())
            <div class="text-sm font-medium text-gray-500 mb-1">{{ $reward->vouchers->first()->store_name }}</div>
        @endif
        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $reward->name }}</h3>
        
        <p class="text-sm text-gray-600 mb-3">{{ $reward->description }}</p>
        
        <div class="flex items-center text-sm text-gray-600">
            @if($reward->quantity == -1)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Unlimited availability</span>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>{{ $reward->quantity }} available</span>
            @endif
        </div>
    </div>
    
    <!-- Redeem Button -->
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        <button 
            x-data
            @click="
                $wire.set('selectedRewardId', null).then(() => {
                    $wire.set('selectedRewardId', {{ $reward->id }});
                    $nextTick(() => $dispatch('open-modal', { name: 'reward-redeem-modal' }));
                })
            "
            class="w-full bg-[#03b8ff] hover:bg-[#0295d1] text-white font-medium py-2 px-4 rounded transition duration-200 
                  {{ Auth::user() && Auth::user()->points >= $reward->cost && ($reward->quantity == -1 || $reward->quantity > 0) ? '' : 'opacity-50 cursor-not-allowed' }}"
            {{ Auth::user() && Auth::user()->points >= $reward->cost && ($reward->quantity == -1 || $reward->quantity > 0) ? '' : 'disabled' }}
        >
            @if(Auth::user() && Auth::user()->points >= $reward->cost)
                @if($reward->quantity == 0 && $reward->quantity != -1)
                    Sold Out
                @else
                    Redeem
                @endif
            @else
                Not Enough Points
            @endif
        </button>
    </div>
</div>
