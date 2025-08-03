<div>
    @if(session()->has('modal_message'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
            <p>{{ session('modal_message') }}</p>
        </div>
    @endif

    @if($redemption)
        <div class="flex">
            <!-- Left side: Image -->
            <div class="w-1/3 pr-6">
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-lg mb-4">
                    @php
                        $rewardType = $redemption->reward->type;
                    @endphp
                    
                    @if($redemption->reward->image_path)
                        <img src="{{ asset('storage/' . $redemption->reward->image_path) }}" 
                             alt="{{ $redemption->reward->name }}" 
                             class="h-full w-full object-cover rounded-lg">
                    @else
                        @if($rewardType == 'system')
                            {{-- System Reward Icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @elseif($rewardType == 'voucher')
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
            </div>

            <!-- Right side: Information -->
            <div class="w-2/3">
                <div class="space-y-3">
                    <div>
                        <span class="font-bold">ID:</span> {{ $redemption->id }}
                    </div>
                    <div>
                        <span class="font-bold">User UUID:</span> {{ $redemption->user->uuid }}
                    </div>
                    <div>
                        <span class="font-bold">User:</span> {{ $redemption->user->name }}
                    </div>
                    <div>
                        <span class="font-bold">Reward:</span> {{ $redemption->reward->name }}
                    </div>
                    <div>
                        <span class="font-bold">Points Spent:</span> {{ $redemption->points_spent }}
                    </div>
                    
                    
                    <div>
                        <span class="font-bold">Type:</span> {{ ucfirst($redemption->reward->type) }}
                    </div>
                    <div>
                        <span class="font-bold">Status:</span> 
                        <span class="px-2 py-1 rounded text-xs {{ 
                            $redemption->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 
                            ($redemption->status === 'completed' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') 
                        }}">
                            {{ ucfirst($redemption->status) }}
                        </span>
                    </div>
                    <div>
                        <span class="font-bold">Created At:</span> {{ $redemption->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>

                <!-- Status Update Section -->
                <div class="mt-6">
                    <div class="text-sm mb-2 text-gray-600">
                        Update redemption status:
                    </div>

                    <form wire:submit.prevent="updateStatus" class="flex items-center space-x-3">
                        <select
                            wire:model="selectedStatus"
                            class="w-full md:w-auto border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        
                        <button 
                            type="button"
                            x-data="{}"
                            x-on:click="
                                const status = $wire.selectedStatus;
                                Swal.fire({
                                    title: 'Update Redemption Status?',
                                    text: 'Are you sure you want to change the status to ' + status + '?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#aaa',
                                    confirmButtonText: 'Yes, update it!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.updateStatus(status);
                                    }
                                })
                            "
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md"
                        >
                            Update Redemption
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            <div class="flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p>Loading redemption details...</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
