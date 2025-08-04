<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Update Announcement</h3>
            <p class="text-gray-600 text-sm">
                Make changes to the announcement details below.
            </p>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <form wire:submit.prevent="updateAnnouncement" class="space-y-4" x-data="{ fileName: '' }">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        type="text"
                        id="title"
                        wire:model="title"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Announcement title"
                    >
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Optional description text"
                    ></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Announcement Image</label>
                    
                    <!-- Upload New Image -->
                    <div class="flex flex-col items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                    
                    <!-- Current Image Preview -->
                    <div class="mt-4 flex justify-center">
                        @if($currentImage)
                            <div class="text-center">
                                <label class="block text-xs text-gray-600 mb-1">Current Image:</label>
                                <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Announcement Image" class="h-40 object-cover rounded border border-gray-300 mx-auto">
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
                
                <!-- Target Audience -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg w-full">
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="audience-public" 
                                    name="targetAudience"
                                    wire:model.live="targetAudience" 
                                    value="public"
                                    class="w-5 h-5 text-blue-600 rounded-full focus:ring-blue-500"
                                >
                                <label for="audience-public" class="ml-2 text-sm font-medium text-gray-900">
                                    Public (All Users)
                                </label>
                            </div>
                            <p class="ml-7 text-xs text-gray-600">
                                This announcement will be shown to all users of the platform.
                            </p>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-blue-200 space-y-3">
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="audience-institution" 
                                    name="targetAudience"
                                    wire:model.live="targetAudience" 
                                    value="institution_specific"
                                    class="w-5 h-5 text-blue-600 rounded-full focus:ring-blue-500"
                                >
                                <label for="audience-institution" class="ml-2 text-sm font-medium text-gray-900">
                                    Institution Specific
                                </label>
                            </div>
                            <p class="ml-7 text-xs text-gray-600">
                                This announcement will only be shown to members of the selected institution.
                            </p>
                        </div>
                    </div>
                    @error('targetAudience') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <!-- Institution Selection -->
                @if(auth()->user()->hasRole('super_admin'))
                <div x-data="{ show: '{{ $targetAudience }}' === 'institution_specific' }" 
                     x-show="show">
                    <label for="institutionId" class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
                    <select
                        id="institutionId"
                        wire:model="institutionId"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    >
                        <option value="">Select Institution</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                        @endforeach
                    </select>
                    @error('institutionId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                @endif
                
                <!-- Active Status -->
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg w-full">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="active-status" 
                            wire:model="active"
                            :checked="@js($active ? true : false)"
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <label for="active-status" class="ml-2 text-sm font-medium text-gray-900">
                            Make this announcement active
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-600">
                        Inactive announcements won't be shown to users.
                    </p>
                </div>
                
                <!-- Date Control Fields -->
                <div x-data="{
                    updateEndDateConstraints() {
                        const endDateInput = this.$refs.endDateInput;
                        const startDateInput = this.$refs.startDateInput;
                        if (endDateInput && startDateInput && startDateInput.value) {
                            endDateInput.min = startDateInput.value;
                            endDateInput.setAttribute('min', startDateInput.value);
                            
                            if (startDateInput.value && endDateInput.value && endDateInput.value < startDateInput.value) {
                                endDateInput.value = '';
                                endDateInput.dispatchEvent(new Event('input'));
                            }
                        }
                    }
                }" x-init="$nextTick(() => updateEndDateConstraints())">
                    <!-- Start Date -->
                    <div class="mb-4">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date (Optional)</label>
                        <input 
                            type="datetime-local" 
                            id="start_date"
                            wire:model="start_date"
                            x-ref="startDateInput"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            @input="updateEndDateConstraints()"
                            @change="updateEndDateConstraints()"
                        />
                        <p class="text-xs text-gray-500 mt-1">If not set, announcement will be active immediately when marked active.</p>
                        @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date (Optional)</label>
                        <input 
                            type="datetime-local" 
                            id="end_date"
                            wire:model="end_date"
                            x-ref="endDateInput"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        />
                        <p class="text-xs text-gray-500 mt-1">If not set, announcement will remain active indefinitely.</p>
                        @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="pt-4 flex justify-end space-x-3">
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('close-modal', { name: 'manage-announcement-modal' })"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you want to delete this announcement? This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.deleteAnnouncement();
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
                            title: 'Update Announcement?',
                            text: 'Are you sure you want to update this announcement?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#03b8ff',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.updateAnnouncement();
                            }
                        })"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md"
                    >
                        Update Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
