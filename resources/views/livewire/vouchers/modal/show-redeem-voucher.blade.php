<div
    @if($userVoucher && $userVoucher->status === 'active')
        wire:poll.1000ms="updateTimer"
    @else
        wire:poll.5000ms="checkVoucherStatus"
    @endif
>
    @if($userVoucher)
        @if(!$showQrCodeView)
            {{-- Confirmation View --}}
            <div class="flex flex-col items-center space-y-4">
                <!-- Voucher Details -->
                <div class="bg-gray-50 p-4 rounded-md w-full">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-20 w-20">
                            @if($userVoucher->voucher->image_path)
                                <img src="{{ asset('storage/' . $userVoucher->voucher->image_path) }}" alt="{{ $userVoucher->voucher->store_name }}" class="h-20 w-20 object-cover rounded">
                            @else
                                <div class="h-20 w-20 bg-gray-200 rounded flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="text-md font-bold">{{ $userVoucher->voucher->store_name }}</h4>
                            <p class="text-sm text-gray-500">{{ $userVoucher->voucher->promo }}</p>
                            <p class="text-xs text-gray-400 mt-1">Reference: {{ $userVoucher->voucher->reference_no }}</p>
                            
                            @if($userVoucher->voucher->expiry_date)
                                <p class="text-xs text-{{ $userVoucher->voucher->expiry_date->isPast() ? 'red' : 'gray' }}-500 mt-1">
                                    Expires: {{ $userVoucher->voucher->expiry_date->format('M d, Y') }}
                                </p>
                            @endif
                            
                            {{-- Display the current status --}}
                            <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full text-xs font-medium 
                                {{ $userVoucher->status === 'available' ? 'bg-green-100 text-green-800' : 
                                  ($userVoucher->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                                  ($userVoucher->status === 'used' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800')) }}">
                                Status: {{ ucfirst($userVoucher->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="mb-4">
                            <p class="text-base text-gray-700 font-medium">Generate a QR code for this voucher to redeem at the store?</p>
                            <p class="text-sm text-gray-600 mt-2">This will mark your voucher as active.</p>
                        </div>
                        
                        <div class="p-4 bg-yellow-50 border border-yellow-300 rounded-md">
                            <p class="text-sm text-yellow-700 font-semibold mb-2">Important:</p>
                            <ul class="list-disc list-inside text-sm text-yellow-600 space-y-1">
                                <li>You will have <strong>30 minutes</strong> to redeem once the QR code is generated.</li>
                                <li>Ensure you are within the store vicinity.</li>
                                <li>Confirm the store is open before generating the QR code.</li>
                            </ul>
                        </div>
                    </div>
                    
                    @if(session()->has('error'))
                        <div class="mt-3 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>

                <!-- Confirmation Button -->
                <div class="w-full flex justify-center">
                    <button 
                        wire:click="redeemVoucher"
                        class="w-full max-w-xs inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#03b8ff] hover:bg-[#0295d1] transition-colors duration-200"
                        {{ $userVoucher->status === 'used' || $userVoucher->status === 'expired' ? 'disabled' : '' }}
                    >
                        <span wire:loading wire:target="redeemVoucher" class="mr-2">
                            <svg class="animate-spin -ml-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="redeemVoucher">
                            {{-- Text remains "Generate QR Code" as this view is only shown if status is 'available' --}}
                            Generate QR Code
                        </span>
                        <span wire:loading wire:target="redeemVoucher">Processing...</span>
                    </button>
                </div>
            </div>
        @else
            {{-- QR Code View --}}
            <div class="flex flex-col items-center space-y-4 p-4">
                {{-- Image, Store Name, Promo Name --}}
                <div class="flex items-center mb-2">
                    @if($userVoucher->voucher->image_path)
                        <img src="{{ asset('storage/' . $userVoucher->voucher->image_path) }}" alt="{{ $userVoucher->voucher->store_name }}" class="h-12 w-12 object-cover rounded mr-3">
                    @else
                        <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center mr-3">
                            <span class="text-gray-500 text-xs">{{ $userVoucher->voucher->store_name }}</span>
                        </div>
                    @endif
                    <div>
                        <h4 class="text-lg font-semibold">{{ $userVoucher->voucher->store_name }}</h4>
                        <p class="text-sm text-gray-600">{{ $userVoucher->voucher->promo }}</p>
                    </div>
                </div>
                
                {{-- Status badge --}}
                <div class="mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                        {{ $userVoucher->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                          ($userVoucher->status === 'used' ? 'bg-gray-100 text-gray-800' : 
                          ($userVoucher->status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-red-100 text-red-800')) }}">
                        Status: {{ ucfirst($userVoucher->status) }}
                    </span>
                </div>

                @if($userVoucher->status === 'active' && !$isExpired)
                    {{-- QR Code (Only show if voucher is active and not expired) --}}
                    <div class="my-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('voucher.verify', $userVoucher->voucher->reference_no)) }}" 
                            alt="QR Code for {{ $userVoucher->voucher->reference_no }}" 
                            class="border rounded-md">
                    </div>

                    {{-- Reference ID --}}
                    <p class="text-md font-medium text-gray-700">
                        <span class="font-bold">{{ $userVoucher->voucher->reference_no }}</span>
                    </p>

                    {{-- Countdown Timer --}}
                    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 text-center">
                        <p class="text-sm text-yellow-700 font-semibold">Time Remaining to Redeem:</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">
                            {{ $timeRemaining ?: '30:00' }}
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">QR code will expire automatically</p>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-2">Present this QR code to the store/merchant only for valid redemption.</p>
                    
                @elseif($userVoucher->status === 'active' && $isExpired)
                    {{-- Show Expired State for Active Vouchers That Timed Out --}}
                    <div class="bg-red-50 p-8 rounded-lg border border-red-200 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-4 text-xl font-bold text-gray-700">QR Code Expired</p>
                        <p class="text-sm text-gray-600 mt-2">The 30-minute window has passed. Please generate a new QR code.</p>
                        
                        <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
                            <p class="text-sm font-medium text-gray-700">Voucher Details:</p>
                            <p class="text-xs text-gray-600 mt-1">Reference: {{ $userVoucher->voucher->reference_no }}</p>
                            <p class="text-xs text-gray-600">Store: {{ $userVoucher->voucher->store_name }}</p>
                            <p class="text-xs text-gray-600">Promo: {{ $userVoucher->voucher->promo }}</p>
                        </div>
                    </div>
                @elseif($userVoucher->status === 'used')
                    {{-- Show Used State - Enhanced for post-scan display --}}
                    <div class="bg-green-50 p-8 rounded-lg border border-green-200 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="mt-4 text-xl font-bold text-gray-700">Voucher Successfully Redeemed!</p>
                        <p class="text-sm text-gray-600 mt-2">This voucher has been verified and marked as used.</p>
                        <p class="text-sm text-gray-500 mt-2">Used on {{$userVoucher->used_at->format('M d, Y \a\t h:i A')}}</p>
                        
                        <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
                            <p class="text-sm font-medium text-gray-700">Voucher Details:</p>
                            <p class="text-xs text-gray-600 mt-1">Reference: {{ $userVoucher->voucher->reference_no }}</p>
                            <p class="text-xs text-gray-600">Store: {{ $userVoucher->voucher->store_name }}</p>
                            <p class="text-xs text-gray-600">Promo: {{ $userVoucher->voucher->promo }}</p>
                        </div>
                        
                        @if(session()->has('success'))
                            <div class="mt-4 text-green-700 text-sm font-medium">
                                {{ session('success') }}
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Show Expired/Unavailable State --}}
                    <div class="bg-red-50 p-8 rounded-lg border border-red-200 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-4 text-xl font-bold text-gray-700">Voucher Not Available</p>
                        <p class="text-sm text-gray-500 mt-2">This voucher is no longer available for redemption.</p>
                        
                        <p class="mt-6 text-sm text-gray-600">Reference: {{ $userVoucher->voucher->reference_no }}</p>
                    </div>
                @endif
                
                @if(session()->has('error'))
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded w-full">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="p-4 text-center text-gray-500">Voucher not found.</div>
    @endif
</div>

