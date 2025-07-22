<div>
    <!-- Status explanation notice -->
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
        <p><strong>Note:</strong> This inventory shows all available voucher codes for rewards. Use this page to manage your voucher stock.</p>
    </div>
    
    <!-- Search Box -->
    <div class="mb-4">
        <input type="text" 
               wire:model.live.debounce.300ms="searchTerm" 
               placeholder="Search vouchers by reference, store name or promo..." 
               class="w-full px-4 py-2 border rounded-lg">
    </div>
    
    <!-- Filter and Create Button Row -->
    <div class="mb-4 flex flex-wrap items-center justify-between">
        <!-- Availability Filter Buttons -->
        <div class="flex space-x-2 flex-wrap mb-2 md:mb-0">
            <button wire:click="filterByAvailability('all')" 
                class="px-4 py-2 text-sm rounded {{ $availabilityFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                All
            </button>
            <button wire:click="filterByAvailability('available')" 
                class="px-4 py-2 text-sm rounded {{ $availabilityFilter === 'available' ? 'bg-green-600 text-white' : 'bg-gray-200' }}">
                Available ({{ $availableCount }})
            </button>
            <button wire:click="filterByAvailability('used')" 
                class="px-4 py-2 text-sm rounded {{ $availabilityFilter === 'used' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                Used ({{ $usedCount }})
            </button>
            <button wire:click="filterByAvailability('expired')" 
                class="px-4 py-2 text-sm rounded {{ $availabilityFilter === 'expired' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                Expired ({{ $expiredCount }})
            </button>
            <button wire:click="filterByAvailability('unavailable')" 
                class="px-4 py-2 text-sm rounded {{ $availabilityFilter === 'unavailable' ? 'bg-gray-600 text-white' : 'bg-gray-200' }}">
                Unavailable ({{ $unavailableCount }})
            </button>
        </div>
        
       
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Reference</th>
                    <th class="py-3 px-6 text-left">Store</th>
                    <th class="py-3 px-6 text-left">Promo</th>
                    <th class="py-3 px-6 text-left">Points Cost</th>
                    <th class="py-3 px-6 text-left">Status</th>
                    <th class="py-3 px-6 text-left">Expiry</th>
                    <th class="py-3 px-6 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                @forelse($vouchers as $voucher)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6">{{ $voucher->id }}</td>
                        <td class="py-3 px-6">{{ $voucher->reference_no }}</td>
                        <td class="py-3 px-6">{{ $voucher->store_name }}</td>
                        <td class="py-3 px-6">
                            <div class="truncate max-w-[200px]">{{ $voucher->promo }}</div>
                        </td>
                        <td class="py-3 px-6">{{ $voucher->cost }}</td>
                        <!-- Removed level requirement column -->
                        <td class="py-3 px-6">
                            <span class="px-2 py-1 rounded text-xs {{ 
                                $voucher->availability === 'available' ? 'bg-green-200 text-green-800' : 
                                ($voucher->availability === 'used' ? 'bg-blue-200 text-blue-800' : 
                                ($voucher->availability === 'expired' ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-gray-800'))
                            }}">
                                {{ ucfirst($voucher->availability) }}
                            </span>
                        </td>
                        <td class="py-3 px-6">
                            {{ $voucher->expiry_date ? $voucher->expiry_date->format('M d, Y') : 'No expiry' }}
                        </td>
                        <td class="py-3 px-6">
                            <button 
                                x-data
                                x-on:click="
                                    $wire.set('selectedVoucherId', null).then(() => {
                                        $wire.set('selectedVoucherId', {{ $voucher->id }});
                                        $nextTick(() => $dispatch('open-modal', { name: 'voucher-view-modal' }));
                                    })
                                "
                                class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                            >
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-3 px-6 text-center">No vouchers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $vouchers->links() }}
    </div>

    <!-- Modal for viewing voucher details - Moved from reward-redemption-index -->
    <x-modal name="voucher-view-modal" title="Voucher Details" focusable>
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading details...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @if($selectedVoucherId)
                    @livewire('super-admin.vouchers.modal.view-voucher-modal', ['voucherId' => $selectedVoucherId], key('voucher-modal-' . $selectedVoucherId))
                @else
                    <p class="text-gray-500">No voucher selected.</p>
                @endif
            </div>
        </div>
    </x-modal>

   
</div>
