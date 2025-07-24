<div>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-md mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-extrabold text-gray-900">
                    Voucher Verification
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Scan result for voucher verification
                </p>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    @if(!$merchantCodeValidated)
                        <form wire:submit.prevent="submitMerchantCode">
                            <div class="mb-4">
                                <label for="merchant_code" class="block text-sm font-medium text-gray-700">Enter Merchant Code</label>
                                <input type="text" id="merchant_code" wire:model.defer="merchantCodeInput" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required autofocus>
                            </div>
                            @if($message)
                                <div class="mb-2 text-red-600 text-sm">{{ $message }}</div>
                            @endif
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Verify Merchant Code</button>
                        </form>
                    @else
                        @if($valid)
                            {{-- Show Used/Verified State - Enhanced design similar to show-redeem-voucher --}}
                            <div class="bg-green-50 p-8 rounded-lg border border-green-200 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <p class="mt-4 text-xl font-bold text-gray-700">{{ $message }}</p>
                                <p class="text-sm text-gray-600 mt-2">This voucher has been verified and marked as used.</p>
                                
                                @if($usedAt)
                                    <p class="text-sm text-gray-500 mt-2">Used on {{ $usedAt->format('M d, Y \\a\\t h:i A') }}</p>
                                @endif
                            </div>
                        @else
                            {{-- Show Invalid/Error State - Enhanced design --}}
                            <div class="bg-red-50 p-8 rounded-lg border border-red-200 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-4 text-xl font-bold text-gray-700">{{ $message }}</p>
                                @if($usedAt)
                                    <p class="text-sm text-gray-500 mt-2">Used on {{ $usedAt->format('M d, Y \\a\\t h:i A') }}</p>
                                @endif
                            </div>
                        @endif

                        @if($voucher)
                            <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
                                <p class="text-sm font-medium text-gray-700">Voucher Details:</p>
                                <div class="flex items-center mt-3">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        @if($voucher->image_path)
                                            <img src="{{ asset('storage/' . $voucher->image_path) }}" alt="{{ $voucher->reward->merchant->name ?? '' }}" class="h-16 w-16 object-cover rounded">
                                        @else
                                            <div class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-md font-bold">{{ $voucher->reward->merchant->name ?? '' }}</h4>
                                        <p class="text-sm text-gray-500">{{ $voucher->promo }}</p>
                                        <p class="text-xs text-gray-400 mt-1">Reference: {{ $voucher->reference_no }}</p>
                                        @if($voucher->expiry_date)
                                            <p class="text-xs text-{{ $voucher->expiry_date->isPast() ? 'red' : 'gray' }}-500 mt-1">
                                                Expires: {{ $voucher->expiry_date->format('M d, Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>

</div>
