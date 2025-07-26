<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Create New Merchant</h3>
            <p class="text-gray-600 text-sm">
                Fill out this form to create a new merchant.
            </p>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <form wire:submit.prevent="createMerchant" class="space-y-4">
                <!-- Merchant Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Merchant Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
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
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Unique merchant code"
                    >
                    @error('merchant_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="pt-4 flex justify-end">
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to create this merchant?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, create it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.createMerchant();
                            }
                        })"
                        class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#03b8ff]"
                    >
                        Create Merchant
                    </button>
                </div>
            </form>
        </div>
        @if($showSuccess)
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4 flex justify-between items-center" role="alert">
                <p>{{ $message }}</p>
            </div>
        @endif
    </div>
</div> 