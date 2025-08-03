<div>

    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Update Reward</h3>
            <p class="text-gray-600 text-sm">
                Make changes to the reward details below.
            </p>
        </div>
        
        <div class="border-t border-gray-200 pt-4">
            <form wire:submit.prevent="updateReward" class="space-y-4" x-data="{ fileName: '' }">
                <!-- Reward Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reward Image</label>
                    
                    <!-- Upload New Image -->
                    <div class="flex flex-col items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Click to upload new image</span>
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 2MB)</p>
                                <p x-text="fileName ? fileName : 'No file chosen'" class="text-xs text-gray-600 mt-2"></p>
                            </div>
                            <input 
                                type="file"
                                class="hidden"
                                wire:model="image"
                                accept="image/*"
                                @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" 
                            />
                        </label>

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="image" class="text-sm text-gray-500 mt-1">Uploading...</div>
                    </div>
                    
                    <!-- Current Image Preview - Moved below upload and centered -->
                    <div class="mt-4 flex justify-center">
                        @if($currentImage)
                            <div class="text-center">
                                <label class="block text-xs text-gray-600 mb-1">Current Image:</label>
                                <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Reward Image" class="h-40 object-cover rounded border border-gray-300 mx-auto">
                            </div>
                        @endif
                        
                        <!-- New Image Preview -->
                        @if ($image)
                            <div class="text-center ml-4">
                                <span class="block text-xs text-gray-700 mb-1">New Image Preview:</span>
                                <img src="{{ $image->temporaryUrl() }}" alt="New Image Preview" class="h-40 object-cover rounded border border-gray-300 mx-auto">
                            </div>
                        @endif
                    </div>
                    
                    @error('image') <span class="text-red-500 text-sm mt-1 text-center block">{{ $message }}</span> @enderror
                </div>

                <!-- Reward Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Reward Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Name of the reward"
                    >
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Merchant Dropdown -->
                <div>
                    <label for="merchant_id" class="block text-sm font-medium text-gray-700 mb-1">Merchant</label>
                    <select
                        id="merchant_id"
                        wire:model="merchant_id"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        required
                    >
                        <option value="">Select a merchant</option>
                        @forelse($merchants as $merchant)
                            <option value="{{ $merchant->id }}">{{ $merchant->name }}</option>
                        @empty
                            <option disabled>No merchants available</option>
                        @endforelse
                    </select>
                    @error('merchant_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Description of what this reward offers"
                    ></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-2">
                        <select
                            id="status"
                            wire:model="status"
                            class="w-130 border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="sold_out">Sold Out</option>
                        </select>
                        <button
                            type="button"
                            x-data
                            x-on:click="Swal.fire({
                                title: 'Update Status?',
                                text: 'Are you sure you want to update the status of this reward?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#03b8ff',
                                cancelButtonColor: '#aaa',
                                confirmButtonText: 'Yes, update it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $wire.updateReward();
                                }
                            })"
                            class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm font-medium"
                        >
                            Update Status
                        </button>
                    </div>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Points Cost -->
                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">Points Cost</label>
                    <input
                        type="number"
                        id="cost"
                        wire:model="cost"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        min="0"
                    >
                    @error('cost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Rank Requirement -->
                <div>
                    <label for="rank_requirement" class="block text-sm font-medium text-gray-700 mb-1">Rank Requirement</label>
                    <select
                        id="rank_requirement"
                        wire:model="rank_requirement"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    >
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                        <option value="diamond">Diamond</option>
                    </select>
                    @error('rank_requirement') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Quantity Display or Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    @if($type == 'Voucher' || $type == 'voucher')
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <!-- Available Vouchers - More prominent with green styling -->
                            <div class="mb-3 bg-green-50 rounded-md p-2 border border-green-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-md font-medium text-green-700">Available Vouchers:</span>
                                    <span class="text-md font-bold text-green-700">{{ $availableVouchers }}</span>
                                </div>
                            </div>
                            
                            <!-- Total Vouchers - More subdued styling -->
                            <div class="mb-3 bg-gray-100 rounded-md p-2 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-md text-gray-600">Total Vouchers:</span>
                                    <span class="text-md font-medium text-gray-600">{{ $totalVouchers }}</span>
                                </div>
                            </div>
                            
                            <!-- Restock Vouchers Section - Improved visibility -->
                            <div class="mt-4 pt-3 border-t border-gray-200">
                                <label for="restock_quantity" class="block text-sm font-medium text-gray-700 mb-1">Restock Vouchers</label>
                                <div class="flex items-center">
                                    <input
                                        type="number"
                                        id="restock_quantity"
                                        wire:model="restockQuantity"
                                        class="w-full border-gray-300 bg-white rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                        min="1"
                                        max="100"
                                        placeholder="Number of vouchers to create"
                                    >
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="Swal.fire({
                                            title: 'Add Voucher Stock?',
                                            text: 'Are you sure you want to add ' + $wire.restockQuantity + ' new vouchers to the inventory?',
                                            icon: 'question',
                                            showCancelButton: true,
                                            confirmButtonColor: '#10b981',
                                            cancelButtonColor: '#aaa',
                                            confirmButtonText: 'Yes, add stock!'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $wire.restockVouchers();
                                            }
                                        })"
                                        class="ml-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md text-sm font-medium whitespace-nowrap"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Stock
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Add more vouchers with the same settings as existing ones
                                </p>
                                @error('restockQuantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @else
                        <div class="flex items-center">
                            <input
                                type="number"
                                id="quantity"
                                wire:model="quantity"
                                class="w-full border-gray-300 bg-white rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                min="-1"
                            >
                            <div class="ml-2 text-gray-500 text-xs">
                                <span class="block">-1 = Unlimited</span>
                            </div>
                        </div>
                        @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>

                <!-- Reward Type (Display Only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reward Type</label>
                    <div class="w-full border border-gray-300 rounded-md px-4 py-2 bg-gray-100 text-gray-600">
                        {{ $type }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Reward type cannot be changed</p>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('close-modal', { name: 'manage-voucher-modal' })"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md"
                    >
                        Cancel
                    </button>
                    @if($type == 'voucher' || $type == 'Voucher')
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Delete Voucher?',
                            text: 'Are you sure you want to delete this voucher? This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.deleteReward();
                            }
                        })"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-md"
                    >
                        Delete
                    </button>
                    @endif
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Update Reward?',
                            text: 'Are you sure you want to update this reward?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#03b8ff',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.updateReward();
                            }
                        })"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md"
                    >
                        Update Reward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
