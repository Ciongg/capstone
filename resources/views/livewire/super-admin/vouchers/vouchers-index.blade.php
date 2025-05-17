<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Voucher Management</h2>
                
                @if(session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif
                
                <!-- Search, Filters, and Create Button Row -->
                <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <!-- Left Side: Search and Filters -->
                    <div class="flex-grow">
                        <!-- Search Box -->
                        <div class="mb-4 md:mb-0">
                            <input type="text" 
                                   wire:model.live.debounce.300ms="searchTerm" 
                                   placeholder="Search vouchers by reference, store, or promo..." 
                                   class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <!-- Filter Buttons (Example: Availability) -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <button wire:click="filterByAvailability('all')" 
                                class="px-3 py-1 text-sm rounded-md {{ ($availabilityFilter ?? 'all') === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                All
                            </button>
                            <button wire:click="filterByAvailability('available')" 
                                class="px-3 py-1 text-sm rounded-md {{ ($availabilityFilter ?? 'all') === 'available' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                Available
                            </button>
                            <button wire:click="filterByAvailability('used')" 
                                class="px-3 py-1 text-sm rounded-md {{ ($availabilityFilter ?? 'all') === 'used' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                Used
                            </button>
                            <button wire:click="filterByAvailability('expired')" 
                                class="px-3 py-1 text-sm rounded-md {{ ($availabilityFilter ?? 'all') === 'expired' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                Expired
                            </button>
                            {{-- Add more filters as needed --}}
                        </div>
                    </div>

                    <!-- Right Side: Create Voucher Button -->
                    <div class="mt-4 md:mt-0 md:ml-4 flex-shrink-0">
                        <button 
                            x-data 
                            @click="$dispatch('open-modal', { name: 'create-voucher-modal' })"
                            class="w-full md:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center shadow-md hover:shadow-lg transition-all"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create Voucher
                        </button>
                    </div>
                </div>
                
                {{-- Placeholder for Vouchers Table/Grid --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">ID</th>
                                <th class="py-3 px-6 text-left">Reference No</th>
                                <th class="py-3 px-6 text-left">Store Name</th>
                                <th class="py-3 px-6 text-left">Promo</th>
                                <th class="py-3 px-6 text-left">Cost</th>
                                <th class="py-3 px-6 text-left">Availability</th>
                                <th class="py-3 px-6 text-left">Expiry Date</th>
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            {{-- @forelse($vouchers as $voucher) --}}
                            {{-- Example Row --}}
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6" colspan="8">
                                    <div class="text-center text-gray-500">Voucher data will be displayed here.</div>
                                </td>
                            </tr>
                            {{-- @empty --}}
                            {{-- <tr>
                                <td colspan="8" class="py-3 px-6 text-center">No vouchers found.</td>
                            </tr> --}}
                            {{-- @endforelse --}}
                        </tbody>
                    </table>
                </div>
                
                {{-- Placeholder for Pagination --}}
                <div class="mt-4">
                    {{-- $vouchers->links() --}}
                </div>

                {{-- Remove your old fixed-position "Create Voucher" button if it was like this:
                <!-- 
                <div class="fixed bottom-6 right-6">
                    <button x-data @click="$dispatch('open-modal', { name: 'create-voucher-modal' })" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-3 rounded-full shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </button>
                </div> 
                -->
                --}}

            </div>
        </div>
    </div>

    <!-- Modal for creating a new voucher -->
    <x-modal name="create-voucher-modal" title="Create New Voucher">
        {{-- Ensure this Livewire component exists and is correctly named --}}
        @livewire('super-admin.vouchers.modal.create-voucher-modal', key('create-voucher-modal'))
    </x-modal>

    <!-- Modal for viewing voucher details (if you have one) -->
    {{-- 
    <x-modal name="view-voucher-modal" title="Voucher Details">
        @if($selectedVoucherId)
            @livewire('super-admin.vouchers.modal.view-voucher-modal', ['voucherId' => $selectedVoucherId], key('view-voucher-modal-' . $selectedVoucherId))
        @endif
    </x-modal>
    --}}
</div>
