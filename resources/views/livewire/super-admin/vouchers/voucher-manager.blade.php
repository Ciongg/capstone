<div>
    <!-- Status explanation notice -->
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
        <p><strong>Note:</strong> This section allows you to manage all rewards in the system. Update reward details or adjust inventory levels.</p>
    </div>
    
    <!-- Success Message -->
    @if($successMessage)
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.clearMessages() }, 5000)">
            <div class="flex justify-between items-center">
                <p>{{ $successMessage }}</p>
                <button @click="show = false; $wire.clearMessages()" class="text-green-700 hover:text-green-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
    
    <!-- Error Message -->
    @if($errorMessage)
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.clearMessages() }, 5000)">
            <div class="flex justify-between items-center">
                <p>{{ $errorMessage }}</p>
                <button @click="show = false; $wire.clearMessages()" class="text-red-700 hover:text-red-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    
    <!-- Search and Create Button Row -->
    <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-2">
        <input type="text" 
               wire:model.live.debounce.300ms="searchTerm" 
               placeholder="Search rewards by name or description..." 
               class="flex-1 w-full md:w-auto px-4 py-2 border rounded-lg md:mr-2 mb-2 md:mb-0">
        <button 
            x-data
            x-on:click="$dispatch('open-modal', { name: 'create-voucher-modal' })"
            class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-lg shadow-md transition-all duration-200 flex items-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Create Voucher
        </button>
    </div>
    
    <!-- Tab Navigation for Reward Types -->
    <div class="mb-4">
        <div class="flex space-x-2 flex-wrap">
            <button wire:click="filterByType('all')" 
                class="px-4 py-2 text-sm rounded {{ $typeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                All Rewards
            </button>
            <button wire:click="filterByType('voucher')" 
                class="px-4 py-2 text-sm rounded {{ $typeFilter === 'voucher' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                Voucher Rewards
            </button>
            <button wire:click="filterByType('system')" 
            class="px-4 py-2 text-sm rounded {{ $typeFilter === 'system' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                System Rewards
            </button>
        </div>
    </div>
    
    <!-- Grid of Reward Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($rewards as $reward)
            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col h-full">
                <!-- Image for reward -->
                <div class="relative w-full h-48 bg-gray-200 flex items-center justify-center">
                    @if(isset($reward->image_path) && $reward->image_path)
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
                        @else
                            {{-- Default Fallback Icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    @endif
                </div>
                
                <div class="p-6 flex flex-col flex-grow min-h-[320px]">
                    <!-- Status Badge -->
                    <div class="flex justify-between items-center mb-4">
                        <span class="px-2 py-1 text-xs rounded {{ 
                            $reward->status === 'available' ? 'bg-green-200 text-green-800' : 
                            ($reward->status === 'unavailable' ? 'bg-gray-200 text-gray-800' : 'bg-red-200 text-red-800')
                        }}">
                            {{ ucfirst($reward->status) }}
                        </span>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            {{ $reward->type }}
                        </span>
                    </div>
                    <!-- Merchant Name -->
                    @if($reward->merchant)
                        <div class="text-sm text-gray-500 mb-1">{{ $reward->merchant->name }}</div>
                    @endif
                    <!-- Reward Details -->
                    <h3 class="text-lg font-bold mb-2 text-gray-900">{{ $reward->name }}</h3>
                    <p class="text-gray-600 mb-4 text-sm line-clamp-3">{{ $reward->description }}</p>
                    <!-- Stats Row -->
                    <div class="flex justify-between text-sm text-gray-500 mb-4">
                        <div>
                            <span class="font-medium">Cost:</span> {{ $reward->cost }} points
                        </div>
                        <div>
                            <span class="font-medium">Quantity:</span> {{ $reward->quantity }}
                        </div>
                    </div>
                    <div class="mt-auto">
                        <!-- Update Button -->
                        <button 
                            x-data
                            x-on:click="
                                $wire.set('selectedRewardId', null).then(() => {
                                    $wire.set('selectedRewardId', {{ $reward->id }});
                                    $nextTick(() => $dispatch('open-modal', { name: 'manage-voucher-modal' }));
                                })
                            "
                            class="w-full bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded font-medium"
                        >
                            Update Reward
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-10 bg-gray-50 rounded-lg">
                <p class="text-gray-500">No rewards found matching your criteria.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $rewards->links() }}
    </div>

    <!-- Modal for managing reward details -->
    <x-modal name="manage-voucher-modal" title="Manage Reward" focusable>
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading reward details...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @if($selectedRewardId)
                    @livewire('super-admin.vouchers.modal.manage-voucher-modal', ['rewardId' => $selectedRewardId], key('reward-modal-' . $selectedRewardId))
                @else
                    <p class="text-gray-500">No reward selected.</p>
                @endif
            </div>
        </div>
    </x-modal>

    <!-- Modal for creating new voucher -->
    <x-modal name="create-voucher-modal" title="Create New Voucher" focusable>
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading form...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @livewire('super-admin.vouchers.modal.create-voucher-modal')
            </div>
        </div>
    </x-modal>
</div>
