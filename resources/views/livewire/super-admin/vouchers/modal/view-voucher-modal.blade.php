<div>
    @if(session()->has('modal_message'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
            <p>{{ session('modal_message') }}</p>
        </div>
    @endif

    @if($voucher)
        <div class="flex flex-col md:flex-row">
            <!-- Left side: Image -->
            <div class="w-full md:w-1/3 md:pr-6 mb-4 md:mb-0">
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-lg mb-4">
                    @php
                        // Ensure we have the correct path format for images
                        $voucherImagePath = $voucher->image_path ? asset('storage/' . $voucher->image_path) : null;
                        $rewardImagePath = $voucher->reward && $voucher->reward->image_path ? asset('storage/' . $voucher->reward->image_path) : null;
                    @endphp
                    
                    @if($voucherImagePath)
                        <img src="{{ $voucherImagePath }}" 
                             alt="{{ $voucher->reward->merchant->name ?? '' }}" 
                             class="h-full w-full object-cover rounded-lg">
                    @elseif($rewardImagePath)
                        <img src="{{ $rewardImagePath }}" 
                             alt="{{ $voucher->reward->merchant->name ?? '' }}" 
                             class="h-full w-full object-cover rounded-lg">
                    @else
                        {{-- Voucher Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    @endif
                </div>
                
                <!-- Reference Number in a copy-able box -->
                <div class="border border-gray-300 rounded-lg p-3 bg-gray-50">
                    <div class="text-sm text-gray-500 mb-1">Reference Number:</div>
                    <div class="font-mono font-medium text-gray-700 flex items-center justify-between">
                        <span>{{ $voucher->reference_no }}</span>
                        <button
                            onclick="navigator.clipboard.writeText('{{ $voucher->reference_no }}').then(() => alert('Reference copied to clipboard!'))"
                            class="text-blue-600 hover:text-blue-800"
                            title="Copy to clipboard"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right side: Information -->
            <div class="w-full md:w-2/3">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <div class="text-xl font-bold">{{ $voucher->reward->merchant->name ?? '' }}</div>
                        <span class="px-2 py-1 rounded text-xs {{ 
                            $voucher->availability === 'available' ? 'bg-green-200 text-green-800' : 
                            ($voucher->availability === 'used' ? 'bg-blue-200 text-blue-800' : 
                            ($voucher->availability === 'expired' ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-gray-800'))
                        }}">
                            {{ ucfirst($voucher->availability) }}
                        </span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Promo:</span> {{ $voucher->promo }}
                    </div>
                    
                    <div>
                        <span class="font-medium">Points Cost:</span> {{ $voucher->cost }} points
                    </div>
                    
                    <div>
                        <span class="font-medium">Rank Requirement:</span> {{ $voucher->reward->rank_requirement ?? 'silver' | ucfirst }}
                    </div>
                    
                    <div>
                        <span class="font-medium">Expiry Date:</span> 
                        {{ $voucher->expiry_date ? $voucher->expiry_date->format('M d, Y') : 'No expiry' }}
                    </div>
                    
                    <div>
                        <span class="font-medium">Created:</span> {{ $voucher->created_at->format('M d, Y h:i A') }}
                    </div>
                    
                    @if($voucher->reward)
                    <div>
                        <span class="font-medium">Associated Reward:</span> {{ $voucher->reward->name }}
                    </div>
                    @endif
                </div>

                <!-- Status Update Section -->
                <div class="mt-6">
                    <div class="text-sm mb-2 text-gray-600">
                        Update voucher status:
                    </div>

                    <form wire:submit.prevent="updateVoucher" class="flex items-center space-x-3">
                        <select
                            wire:model="selectedStatus"
                            class="w-full md:w-auto border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <option value="available">Available</option>
                            <option value="used">Used</option>
                            <option value="expired">Expired</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                        
                        <button 
                            type="button"
                            x-data="{}"
                            x-on:click="
                                const status = $wire.selectedStatus;
                                Swal.fire({
                                    title: 'Update Voucher Status?',
                                    text: 'Are you sure you want to change the status to ' + status + '?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#aaa',
                                    confirmButtonText: 'Yes, update it!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.updateVoucher();
                                    }
                                })
                            "
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md"
                        >
                            Update Voucher
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            <div class="flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p>Loading voucher details...</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

