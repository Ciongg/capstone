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

            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-6">
                    @if(!$merchantCodeValidated)
                        <form wire:submit.prevent="submitMerchantCode">
                            <div class="mb-6">
                                <label for="merchant_code" class="block text-lg font-semibold text-gray-700 mb-2">Merchant Code</label>
                                
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="merchant_code" 
                                        wire:model.defer="merchantCodeInput" 
                                        class="mt-1 block w-full pl-10 py-3 text-lg border-2 border-blue-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        placeholder="Enter your merchant code"
                                        required 
                                        autofocus
                                    >
                                </div>
                            </div>
                            
                            @if($message)
                                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                                    <p class="font-medium">{{ $message }}</p>
                                </div>
                            @endif
                            
                            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 text-lg font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                                Verify Merchant Code
                            </button>
                            
                            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <span class="font-medium">Note:</span> The merchant code is a secure identifier provided by your manager or employer. If you don't have this code, please consult with your supervisor or the merchant administration team.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>

</div>
