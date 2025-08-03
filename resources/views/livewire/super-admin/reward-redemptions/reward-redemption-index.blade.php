<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Reward Management</h2>
                
                @if(session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif
                
                <!-- Tab Navigation - Added Manage Rewards tab -->
                <div class="border-b border-gray-200 mb-6" x-data="{ 
                    tab: window.location.hash ? window.location.hash.substring(1) : 'redemptions',
                    updateHash() {
                        window.location.hash = this.tab;
                    }
                }">
                    <nav class="flex -mb-px">
                        <button 
                            x-on:click="tab = 'manage'; updateHash()" 
                            :class="{ 'border-blue-500 text-blue-600': tab === 'manage', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'manage' }" 
                            class="w-1/3 py-3 px-1 text-center border-b-2 font-medium text-sm"
                        >
                            Manage Rewards
                        </button>
                        <button 
                            x-on:click="tab = 'vouchers'; updateHash()" 
                            :class="{ 'border-blue-500 text-blue-600': tab === 'vouchers', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'vouchers' }" 
                            class="w-1/3 py-3 px-1 text-center border-b-2 font-medium text-sm"
                        >
                            Voucher Inventory
                        </button>
                        <button 
                            x-on:click="tab = 'redemptions'; updateHash()" 
                            :class="{ 'border-blue-500 text-blue-600': tab === 'redemptions', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'redemptions' }" 
                            class="w-1/3 py-3 px-1 text-center border-b-2 font-medium text-sm"
                        >
                            Reward Redemptions
                        </button>
                    </nav>
                    
                    <!-- Tab Content - Added Manage Rewards content -->
                    <div class="pt-4">
                        <!-- Vouchers Tab -->
                        <div x-show="tab === 'vouchers'" x-cloak>
                            @livewire('super-admin.vouchers.voucher-inventory-index')
                        </div>
                        
                        <!-- Redemptions Tab -->
                        <div x-show="tab === 'redemptions'" x-cloak>
                            <!-- Status explanation notice -->
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                                <p><strong>Note:</strong> System and voucher rewards are automatically marked as completed.</p>
                            </div>
                            
                            <!-- Search Box -->
                            <div class="mb-4">
                                <input type="text" 
                                    wire:model.live.debounce.300ms="searchTerm" 
                                    placeholder="Search by user UUID or reward name..." 
                                    class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            
                            <!-- Status Filter Buttons -->
                            <div class="mb-4 flex space-x-2">
                                <button wire:click="filterByStatus('all')" 
                                    class="px-4 py-2 text-sm rounded {{ $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                    All
                                </button>
                                <button wire:click="filterByStatus('pending')" 
                                    class="px-4 py-2 text-sm rounded {{ $statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200' }}">
                                    Pending ({{ $pendingCount }})
                                </button>
                                <button wire:click="filterByStatus('completed')" 
                                    class="px-4 py-2 text-sm rounded {{ $statusFilter === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-200' }}">
                                    Completed ({{ $completedCount }})
                                </button>
                                <button wire:click="filterByStatus('rejected')" 
                                    class="px-4 py-2 text-sm rounded {{ $statusFilter === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                                    Rejected ({{ $rejectedCount }})
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                            <th class="py-3 px-6 text-left">ID</th>
                                            <th class="py-3 px-6 text-left">User</th>
                                            <th class="py-3 px-6 text-left">Reward</th>
                                            <th class="py-3 px-6 text-left">Quantity</th>
                                            <th class="py-3 px-6 text-left">Points Spent</th>
                                            <th class="py-3 px-6 text-left">Type</th>
                                            <th class="py-3 px-6 text-left">Status</th>
                                            <th class="py-3 px-6 text-left">Created At</th>
                                            <th class="py-3 px-6 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 text-sm">
                                        @forelse($redemptions as $redemption)
                                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                <td class="py-3 px-6">{{ $redemption->id }}</td>
                                                <td class="py-3 px-6">{{ $redemption->user->name }}</td>
                                                <td class="py-3 px-6">{{ $redemption->reward->name }}</td>
                                                <td class="py-3 px-6">{{ $redemption->quantity }}</td>
                                                <td class="py-3 px-6">{{ $redemption->points_spent }}</td>
                                                <td class="py-3 px-6">{{ $redemption->reward->type }}</td>
                                                <td class="py-3 px-6">
                                                    <span class="px-2 py-1 rounded text-xs {{ 
                                                        $redemption->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 
                                                        ($redemption->status === 'completed' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') 
                                                    }}">
                                                        {{ ucfirst($redemption->status) }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-6">{{ $redemption->created_at->format('M d, Y') }}</td>
                                                <td class="py-3 px-6">
                                                    <button 
                                                        x-data
                                                        x-on:click="
                                                            $wire.set('selectedRedemptionId', null).then(() => {
                                                                $wire.set('selectedRedemptionId', {{ $redemption->id }});
                                                                $nextTick(() => $dispatch('open-modal', { name: 'reward-redemption-modal' }));
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
                                                <td colspan="9" class="py-3 px-6 text-center">No reward redemptions found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                {{ $redemptions->links() }}
                            </div>
                        </div>
                        
                        <!-- Manage Rewards Tab -->
                        <div x-show="tab === 'manage'" x-cloak>
                            @livewire('super-admin.vouchers.voucher-manager')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing redemption details -->
    <x-modal name="reward-redemption-modal" title="Redemption Details" focusable>
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading details...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @if($selectedRedemptionId)
                    @livewire('super-admin.reward-redemptions.modal.reward-redemption-modal', ['redemptionId' => $selectedRedemptionId], key('redemption-modal-' . $selectedRedemptionId))
                @else
                    <p class="text-gray-500">No redemption selected.</p>
                @endif
            </div>
        </div>
    </x-modal>
</div>
