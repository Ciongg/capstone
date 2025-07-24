<div>
    <!-- Status explanation notice -->
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
        <p><strong>Note:</strong> This page shows all registered merchants. Use this page to manage merchant records.</p>
    </div>
    
    <!-- Search and Create Button Row -->
    <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-2">
        <input type="text" 
               wire:model.live.debounce.300ms="searchTerm" 
               placeholder="Search merchants by name or code..." 
               class="flex-1 w-full md:w-auto px-4 py-2 border rounded-lg md:mr-2 mb-2 md:mb-0">
        <button
            x-data
            x-on:click="$dispatch('open-modal', { name: 'create-merchant-modal' })"
            class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-lg shadow-md transition-all duration-200 flex items-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Merchant
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Name</th>
                    <th class="py-3 px-6 text-left">Merchant Code</th>
                    <th class="py-3 px-6 text-left">Created At</th>
                    <th class="py-3 px-6 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                @forelse($merchants as $merchant)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6">{{ $merchant->id }}</td>
                        <td class="py-3 px-6">{{ $merchant->name }}</td>
                        <td class="py-3 px-6">{{ $merchant->merchant_code }}</td>
                        <td class="py-3 px-6">{{ $merchant->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-6">
                            <button 
                                x-data
                                x-on:click="$wire.set('selectedMerchantId', null).then(() => {
                                    $wire.set('selectedMerchantId', {{ $merchant->id }});
                                    $nextTick(() => $dispatch('open-modal', { name: 'manage-merchant-modal' }));
                                })"
                                class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm flex items-center"
                            >
                              
                                Update
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-3 px-6 text-center">No merchants found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $merchants->links() }}
    </div>

    <!-- Modal for creating merchant -->
    <x-modal name="create-merchant-modal" title="Create Merchant" focusable>
        <div class="p-6 relative min-h-[200px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading...</p>
                </div>
            </div>
            <div wire:loading.remove class="flex-1">
                @livewire('super-admin.merchants.modal.create-merchant-modal', key('create-merchant-modal'))
            </div>
        </div>
    </x-modal>

    <!-- Modal for managing merchant -->
    <x-modal name="manage-merchant-modal" title="Manage Merchant" focusable>
        <div class="p-6 relative min-h-[200px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading...</p>
                </div>
            </div>
            <div wire:loading.remove class="flex-1">
                @if($selectedMerchantId)
                    @livewire('super-admin.merchants.modal.manage-merchant-modal', ['merchantId' => $selectedMerchantId], key('manage-merchant-modal-' . $selectedMerchantId))
                @else
                    <p class="text-gray-500">No merchant selected.</p>
                @endif
            </div>
        </div>
    </x-modal>
</div> 