<div class="py-6" x-data="{ tab: 'available' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-semibold text-gray-600 mb-6">My Vouchers</h2>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <div class="flex -mb-px w-full">
                <button 
                    x-on:click="tab = 'available'" 
                    :class="tab === 'available' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300'"
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition"
                >
                    Available Vouchers
                </button>
                <button 
                    x-on:click="tab = 'used'" 
                    :class="tab === 'used' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300'"
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition"
                >
                    Used Vouchers
                </button>
            </div>
        </div>
        
        <!-- Available Vouchers Tab Content -->
        <div x-show="tab === 'available'">
            @if(count($userVouchers) > 0)
                <div class="space-y-4">
                    @foreach($userVouchers as $userVoucher)
                        <div class="bg-white shadow rounded-lg overflow-hidden flex flex-col sm:flex-row sm:items-center @if($userVoucher->status === 'active') border-l-8 border-blue-500 @endif">
                            <!-- Voucher image (fixed width on all screens) -->
                            <div class="w-full sm:w-32 h-32 flex-shrink-0 overflow-hidden">
                                @if($userVoucher->voucher->image_path)
                                    <img src="{{ asset('storage/' . $userVoucher->voucher->image_path) }}" alt="{{ $userVoucher->voucher->promo }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        @if($userVoucher->rewardRedemption->reward->type == 'voucher')
                                            <!-- Voucher Icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                        @else
                                            <span class="text-gray-500">{{ $userVoucher->voucher->reward->merchant->name ?? '' }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Voucher details with min-width to prevent shrinking -->
                            <div class="p-4 flex-grow min-w-0">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 truncate">{{ $userVoucher->voucher->reward->merchant->name ?? '' }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ $userVoucher->voucher->promo }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1 mr-1">
                                            Ref: {{ $userVoucher->voucher->reference_no }}
                                        </span>
                                        
                                        @if($userVoucher->voucher->expiry_date)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mb-1 mr-1 {{ $userVoucher->voucher->expiry_date->isPast() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                Expires: {{ $userVoucher->voucher->expiry_date->format('M d, Y') }}
                                            </span>
                                        @endif
                                        
                                        <!-- Status Badge -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mb-1 mr-1
                                            {{ $userVoucher->status === 'available' ? 'bg-green-100 text-green-800' : 
                                               ($userVoucher->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                                               ($userVoucher->status === 'used' ? 'bg-gray-100 text-gray-800' : 
                                               ($userVoucher->status === 'expired' ? 'bg-red-100 text-red-800' : 
                                               'bg-yellow-100 text-yellow-800'))) }}">
                                            {{ ucfirst($userVoucher->status) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Redeem Button (mobile only) -->
                                <div class="mt-3 sm:hidden">
                                    <button 
                                        wire:click="openRedeemModal({{ $userVoucher->id }})"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#03b8ff] hover:bg-[#0295d1]"
                                        {{ !in_array($userVoucher->status, ['available', 'active']) ? 'disabled' : '' }}
                                    >
                                        <span wire:loading wire:target="openRedeemModal({{ $userVoucher->id }})" class="mr-2">
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                        {{ $userVoucher->status === 'active' ? 'Show QR Code' : 'Redeem' }}
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Redeem Button (desktop only) with fixed width -->
                            <div class="hidden sm:flex p-4 w-40 lg:w-48 flex-shrink-0 justify-center items-center">
                                <button 
                                    wire:click="openRedeemModal({{ $userVoucher->id }})"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#03b8ff] hover:bg-[#0295d1]"
                                    {{ !in_array($userVoucher->status, ['available', 'active']) ? 'disabled' : '' }}
                                >
                                    <span wire:loading wire:target="openRedeemModal({{ $userVoucher->id }})" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    {{ $userVoucher->status === 'active' ? 'Show QR Code' : 'Redeem' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <p class="text-gray-500">You don't have any available vouchers.</p>
                    <a href="{{ route('rewards.index') }}" class="mt-4 inline-block text-[#03b8ff] hover:underline">
                        Go to Rewards to redeem vouchers
                    </a>
                </div>
            @endif
        </div>
        
        <!-- Used Vouchers Tab Content -->
        <div x-show="tab === 'used'" x-cloak>
            @if(count($userVouchersHistory) > 0)
                <div class="space-y-4">
                    @foreach($userVouchersHistory as $userVoucher)
                        <div class="bg-white shadow rounded-lg overflow-hidden flex flex-col sm:flex-row">
                            <!-- Voucher image - same fix as above -->
                            <div class="w-full sm:w-32 h-32 flex-shrink-0 overflow-hidden">
                                @if($userVoucher->voucher->image_path)
                                    <img src="{{ asset('storage/' . $userVoucher->voucher->image_path) }}" alt="{{ $userVoucher->voucher->promo }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        @if($userVoucher->rewardRedemption->reward->type == 'voucher')
                                            <!-- Voucher Icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                        @else
                                            <span class="text-gray-500">{{ $userVoucher->voucher->reward->merchant->name ?? '' }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Voucher details with min-width to prevent shrinking -->
                            <div class="p-4 flex-grow min-w-0 flex flex-col justify-between">
                                <div>
                                    <div class="flex flex-wrap justify-between items-start gap-2">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $userVoucher->voucher->reward->merchant->name ?? '' }}</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $userVoucher->status === 'used' ? 'bg-gray-100 text-gray-800' : 
                                            ($userVoucher->status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($userVoucher->status) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">{{ $userVoucher->voucher->promo }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1 mr-1">
                                            Ref: {{ $userVoucher->voucher->reference_no }}
                                        </span>
                                        @if($userVoucher->used_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mb-1 mr-1">
                                                Used: {{ $userVoucher->used_at->format('M d, Y') }}
                                            </span>
                                        @endif
                                        @if($userVoucher->voucher->expiry_date)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mb-1 mr-1 {{ $userVoucher->voucher->expiry_date->isPast() && $userVoucher->status !== 'used' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $userVoucher->status === 'expired' || ($userVoucher->voucher->expiry_date->isPast() && $userVoucher->status !== 'used') ? 'Expired: ' : 'Expires: ' }} {{ $userVoucher->voucher->expiry_date->format('M d, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4">
                                    {{-- Placeholder for potential future actions or info for used vouchers --}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <p class="text-gray-500">You don't have any used or expired vouchers yet.</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Redeem Voucher Modal (only for available vouchers) -->
    <x-modal name="redeem-voucher-modal" title="Voucher Details">
        @if($selectedVoucher)
            @livewire('vouchers.modal.show-redeem-voucher', ['userVoucherId' => $selectedVoucher], key($selectedVoucher))
        @else
            <!-- Simple Loading Indicator -->
            <div class="flex flex-col justify-center items-center h-full p-6">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600 text-lg">Loading voucher details...</p>
            </div>
        @endif
    </x-modal>
    
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('voucher-expired-alert', () => {
            Swal.fire({
                icon: 'error',
                title: 'Voucher Expired',
                text: 'This voucher has already expired.',
                confirmButtonColor: '#3085d6',
            });
        });
    });
</script>
@endpush
