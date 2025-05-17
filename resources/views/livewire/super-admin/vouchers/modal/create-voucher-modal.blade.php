<div>
    @if($showSuccess)
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
            <div class="mt-4 flex justify-end">
                <button
                    wire:click="closeModal"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >
                    Close
                </button>
            </div>
        </div>
    @else
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-2">Create New Voucher</h3>
                <p class="text-gray-600 text-sm">
                    Fill out this form to create a new voucher that users can redeem with their points.
                </p>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <form wire:submit.prevent="createVoucher" class="space-y-4" x-data="{ fileName: '' }">
                    <!-- Voucher Image Upload -->
                    <div>
                        <label class="block font-semibold mb-2 text-center">Voucher Image</label>
                        <div class="flex flex-col items-center justify-center w-full">
                            <!-- Custom styled label acting as the input area -->
                            <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <!-- Icon -->
                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <!-- Text -->
                                    <p class="mb-2 text-sm text-gray-500">
                                        <span class="font-semibold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 2MB)</p>
                                    <!-- Display selected file name -->
                                    <p x-text="fileName ? fileName : 'No file chosen'" class="text-xs text-gray-600 mt-2"></p>
                                </div>
                                <!-- Hidden actual file input -->
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

                            <!-- Image Preview -->
                            @if ($image)
                                <div class="mt-4">
                                    <span class="block text-sm font-medium text-gray-700 mb-1">Image Preview:</span>
                                    <img src="{{ $image->temporaryUrl() }}" alt="Voucher Preview" class="max-h-40 rounded shadow">
                                </div>
                            @endif
                            
                            @error('image') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Store Name -->
                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <input
                            type="text"
                            id="store_name"
                            wire:model="store_name"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="Store or vendor name"
                        >
                        @error('store_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Voucher Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Voucher Name</label>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="Name of the voucher reward"
                        >
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Promo Description -->
                    <div>
                        <label for="promo" class="block text-sm font-medium text-gray-700 mb-1">Promo Description</label>
                        <textarea
                            id="promo"
                            wire:model="promo"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="Description of what this voucher offers"
                        ></textarea>
                        @error('promo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Points Cost -->
                    <div>
                        <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">Points Cost</label>
                        <input
                            type="number"
                            id="cost"
                            wire:model="cost"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            min="0"
                            placeholder="Number of points required"
                        >
                        @error('cost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity to Create</label>
                        <input
                            type="number"
                            id="quantity"
                            wire:model="quantity"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            min="1"
                            placeholder="Number of vouchers to create"
                        >
                        <p class="text-xs text-gray-500 mt-1">Each voucher will have a unique reference number automatically generated</p>
                        @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Level Requirement -->
                    <div>
                        <label for="level_requirement" class="block text-sm font-medium text-gray-700 mb-1">Level Requirement</label>
                        <input
                            type="number"
                            id="level_requirement"
                            wire:model="level_requirement"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            min="0"
                            placeholder="Minimum user level (0 = no requirement)"
                        >
                        @error('level_requirement') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date (Optional)</label>
                        <input
                            type="date"
                            id="expiry_date"
                            wire:model="expiry_date"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                        @error('expiry_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button
                            type="submit"
                            class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#03b8ff]"
                        >
                            Create Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
