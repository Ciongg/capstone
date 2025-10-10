<div class="min-h-[400px] flex items-center justify-center">
    @if($reward)
        <div class="w-full max-w-md mx-auto">
            <div class="flex flex-col items-center space-y-6">
 
                <!-- Section 1: Reward Image & Basic Details -->
                <div class="text-center w-full">
                    {{-- Reward Image/Icon Container --}}
                    <div class="w-full h-32 bg-gray-100 flex items-center justify-center rounded-lg mb-4 shadow-sm">
                        @if($reward->image_path)
                            <img src="{{ asset('storage/' . $reward->image_path) }}" alt="{{ $reward->name }}" class="h-full w-full object-contain rounded-lg">
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
                            @else
                                {{-- Default Fallback Icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        @endif
                    </div>

                    {{-- Basic Reward Info in Yellow Warning Container --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-yellow-700 mb-1"><span class="font-semibold">Redeeming:</span> {{ $reward->name }}</p>
                        <p class="text-sm text-yellow-700">
                            <span class="font-semibold">Available:</span> 
                            {{ $availableQuantity != -1 ? $availableQuantity : 'Unlimited' }}
                        </p>
                    </div>
                </div>

            <!-- Section 2: Purchase Form -->
            <div class="flex flex-col items-center w-full">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Confirm Purchase?</h2>
                
                {{-- System Reward: Quantity Selector --}}
                @if ($reward->type == 'system')
                    <div class="mb-4 w-full max-w-xs">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 text-center">Quantity:</label>
                        <input 
                            type="number" 
                            id="quantity" 
                            wire:model.live="redeemQuantity" 
                            min="1"
                            max="{{ $reward->quantity <= -1 ? 999 : $reward->quantity }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-center"
                            {{ $reward->quantity == 0 && $reward->quantity != -1 ? 'disabled' : '' }}
                        >
                        
                        @if($reward->quantity == 0 && $reward->quantity != -1)
                            <p class="text-xs text-red-500 mt-1 text-center">This item is sold out.</p>
                        @endif
                    </div>
                @endif

                {{-- Cost Display --}}
                <div class="flex items-center justify-center mb-2">
                    <span class="text-lg font-semibold text-gray-700 mr-2">
                        {{ $reward->type == 'system' ? 'Total Cost:' : 'Cost:' }}
                    </span>
                    <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full">
                        <span class="font-bold text-white drop-shadow">{{ $this->getTotalCost() }}</span>
                        {{-- Diamond Icon --}}
                        <svg class="w-5 h-5 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </svg>
                    </div>
                </div>
                
                {{-- User Points Display --}}
                @auth
                <div class="flex items-center justify-center text-sm text-gray-600 mb-6">
                    <span class="mr-1">Your Points:</span>
                    <span>{{ Auth::user()->points }}</span>
                    <svg class="w-4 h-4 text-gray-600 ml-1" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                    </svg>
                </div>
                @else
                <div class="flex items-center justify-center text-sm text-red-600 mb-6">
                    <span>Please login to purchase rewards</span>
                </div>
                @endauth
            </div>

            <!-- Section 3: Confirm Button -->
            <div class="w-full flex justify-center" x-data="{ 
                confirmPurchase() {
                    Swal.fire({
                        title: 'Confirm Purchase',
                        html: '<div>Are you sure you want to spend <b>{{ $this->getTotalCost() }}</b> points on this reward?</div>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, purchase it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        focusConfirm: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.confirmRedemption();
                        }
                    });
                }
            }">
                <button 
                    type="button"
                    x-on:click="confirmPurchase()"
                    class="w-full max-w-xs bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $this->isButtonDisabled() ? 'disabled' : '' }}
                >
                    <span wire:loading wire:target="confirmRedemption">Processing...</span>
                    <span wire:loading.remove wire:target="confirmRedemption">Confirm</span>
                </button>
            </div>

            <!-- Section 4: Error Message -->
            <div class="text-center w-full">
                @if($this->getErrorMessage())
                    <p class="text-xs text-red-500 mt-2">{{ $this->getErrorMessage() }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="w-full max-w-md mx-auto">
            <div class="p-8 text-center text-gray-500 bg-white rounded-lg shadow-sm border border-gray-200">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">No Reward Selected</h3>
                <p class="text-gray-500">Please select a reward to redeem.</p>
            </div>
        </div>
    @endif
</div>
</div>
