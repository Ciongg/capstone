<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Update Merchant</h3>
            <p class="text-gray-600 text-sm">
                Make changes to the merchant details below.
            </p>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <form x-data="{ showUpdateConfirm: false, showDeleteConfirm: false, fileName: '' }" wire:submit.prevent="updateMerchant" class="space-y-4">
                <!-- Merchant Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Merchant Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Name of the merchant"
                    >
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <!-- Merchant Code -->
                <div>
                    <label for="merchant_code" class="block text-sm font-medium text-gray-700 mb-1">Merchant Code</label>
                    <input
                        type="text"
                        id="merchant_code"
                        wire:model="merchant_code"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Unique merchant code"
                    >
                    @error('merchant_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Partner Type -->
                <div>
                    <label for="partner_type" class="block text-sm font-medium text-gray-700 mb-1">Partner Type</label>
                    <select
                        id="partner_type"
                        wire:model="partner_type"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    >
                        <option value="Merchant">Merchant</option>
                        <option value="Affiliate">Affiliate</option>
                    </select>
                    @error('partner_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Merchant Logo (copied style from announcement manage) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Merchant Logo</label>
                    
                    <!-- Upload New Logo -->
                    <div class="flex flex-col items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Click to upload new logo</span>
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
                    
                    <!-- Current Logo Preview -->
                    <div class="mt-4 flex justify-center">
                        @if($currentImage && !$imageMarkedForDeletion)
                            <div class="text-center relative">
                                <label class="block text-xs text-gray-600 mb-1">Current Logo:</label>
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Merchant Logo" class="h-40 object-cover rounded border border-gray-300 mx-auto">
                                    <button 
                                        type="button" 
                                        wire:click="markImageForDeletion" 
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                        title="Delete logo"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @elseif($imageMarkedForDeletion)
                            <div class="text-center">
                                <label class="block text-xs text-gray-600 mb-1">Logo marked for deletion:</label>
                                <div class="relative">
                                    <div class="h-40 w-64 bg-gray-200 rounded border border-gray-300 mx-auto flex items-center justify-center">
                                        <div class="text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <p class="text-sm">Logo will be deleted on update</p>
                                            <button 
                                                type="button" 
                                                wire:click="$set('imageMarkedForDeletion', false)" 
                                                class="mt-2 text-blue-500 hover:text-blue-700 text-sm"
                                            >
                                                Cancel deletion
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- New Logo Preview -->
                        @if ($image)
                            <div class="text-center ml-4 relative">
                                <span class="block text-xs text-gray-700 mb-1">New Logo Preview:</span>
                                <div class="relative">
                                    <img src="{{ $image->temporaryUrl() }}" alt="New Logo Preview" class="h-40 object-cover rounded border border-gray-300 mx-auto">
                                    <button 
                                        type="button" 
                                        wire:click="removeImagePreview" 
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                        title="Remove logo"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    @error('image') <span class="text-red-500 text-sm mt-1 text-center block">{{ $message }}</span> @enderror
                </div>

                <!-- Contact Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="name@example.com"
                    >
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Contact Number -->
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number (Optional)</label>
                    <input
                        type="text"
                        id="contact_number"
                        wire:model="contact_number"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="+1 555 123 4567"
                    >
                    @error('contact_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        maxlength="1028"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Short description about the sponsor/partner (max 1028 characters)"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Max 1028 characters.</p>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('close-modal', { name: 'manage-merchant-modal' })"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to delete this merchant? This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.deleteMerchant();
                            }
                        })"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-md"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to update this merchant?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.updateMerchant();
                            }
                        })"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md"
                    >
                        Update Merchant
                    </button>
                </div>
            </form>
            @if($showSuccess)
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4 flex justify-between items-center" role="alert">
                    <p>{{ $message }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush