<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Create New Institution</h3>
            <p class="text-gray-600 text-sm">
                Fill out this form to create a new institution.
            </p>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <form wire:submit.prevent="createInstitution" class="space-y-4">
                <!-- Institution Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Institution Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
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
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Unique institution domain"
                    >
                    @error('domain') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="pt-4 flex justify-end">
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to create this institution?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, create it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.createInstitution();
                            }
                        })"
                        class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#03b8ff]"
                    >
                        Create Institution
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
