<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Update Institution</h3>
            <p class="text-gray-600 text-sm">
                Make changes to the institution details below.
            </p>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <form x-data="{ showUpdateConfirm: false, showDeleteConfirm: false }" wire:submit.prevent="updateInstitution" class="space-y-4">
                <!-- Institution Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Institution Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Name of the institution"
                    >
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <!-- Institution Domain -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">Institution Domain</label>
                    <input
                        type="text"
                        id="domain"
                        wire:model="domain"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Unique institution domain"
                    >
                    @error('domain') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="pt-4 flex justify-end space-x-3">
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('close-modal', { name: 'manage-institution-modal' })"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to delete this institution? This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.deleteInstitution();
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
                            text: 'Do you want to update this institution?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.updateInstitution();
                            }
                        })"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md"
                    >
                        Update Institution
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
