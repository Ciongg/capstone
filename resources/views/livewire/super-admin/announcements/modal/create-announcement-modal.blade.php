<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Create New Announcement</h3>
            <p class="text-gray-600 text-sm">
                Fill out this form to create a new announcement.
            </p>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <form wire:submit.prevent="save" class="space-y-4" x-data="{ fileName: '' }">
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
                
                <!-- Image Upload - Enhanced version -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Announcement Image</label>
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
                                id="image"
                                class="hidden"
                                wire:model="image"
                                accept="image/*"
                                @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" 
                            />
                        </label>

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="image" class="mt-2">
                            <div class="flex items-center text-blue-600 text-sm">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Uploading image...
                            </div>
                        </div>

                        <!-- Image Preview -->
                        @if ($image)
                            <div class="mt-4">
                                <span class="block text-sm font-medium text-gray-700 mb-1">Image Preview:</span>
                                <img src="{{ $image->temporaryUrl() }}" alt="Announcement Preview" class="max-h-40 rounded shadow">
                            </div>
                        @endif
                        
                        @error('image') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <!-- Target Audience - Enhanced Version -->
                <div x-data="{
                        targetAudience: @entangle('targetAudience').defer,
                        showInstitutionDropdown: @js(auth()->user()->hasRole('super_admin')) && @entangle('targetAudience').defer === 'institution_specific'
                    }" 
                    x-init="$watch('targetAudience', value => { showInstitutionDropdown = value === 'institution_specific' })"
                >
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
                                    x-model="targetAudience"
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
                                    x-model="targetAudience"
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

                    <!-- Institution Selection (only when institution_specific is selected and user is super_admin) -->
                    @if(auth()->user()->hasRole('super_admin'))
                        <div x-show="showInstitutionDropdown" x-transition>
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
                </div>
                
                <!-- Active Status - Yellow Style Version -->
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg w-full">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="active-status" 
                            wire:model="active"
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <label for="active-status" class="ml-2 text-sm font-medium text-gray-900">
                            Make this announcement active
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-600">
                        Inactive announcements won't be shown to users. Set start and end dates below to control when this announcement appears.
                    </p>
                </div>
                
                <!-- Date Control Fields -->
                <div x-data="{
                    currentTime: new Date().toISOString().slice(0, 16),
                    updateEndDateConstraints() {
                        const endDateInput = this.$refs.endDateInput;
                        const startDateInput = this.$refs.startDateInput;
                        if (endDateInput && startDateInput && startDateInput.value) {
                            endDateInput.min = startDateInput.value;
                            endDateInput.setAttribute('min', startDateInput.value);
                            
                            // Clear end date if it's now before the new start date
                            if (startDateInput.value && endDateInput.value && endDateInput.value < startDateInput.value) {
                                endDateInput.value = '';
                                // Trigger Livewire update
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
                            value="{{ old('start_date', $start_date ? \Carbon\Carbon::parse($start_date)->format('Y-m-d\TH:i') : '') }}"
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
                            value="{{ old('end_date', $end_date ? \Carbon\Carbon::parse($end_date)->format('Y-m-d\TH:i') : '') }}"
                        />
                        <p class="text-xs text-gray-500 mt-1">If not set, announcement will remain active indefinitely.</p>
                        @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- URL Field (Optional) -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Redirect URL (Optional)</label>
                        <input 
                            type="url" 
                            id="url"
                            wire:model="url"
                            class="w-full border-gray-300 rounded-md shadow-sm px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="https://example.com"
                        />
                        <p class="text-xs text-gray-500 mt-1">If set, clicking the announcement will redirect to this URL.</p>
                        @error('url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="pt-4 flex justify-end space-x-3">
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('close-modal', { name: 'create-announcement-modal' })"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Create Announcement?',
                            text: 'Are you sure you want to create this announcement?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#03b8ff',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, create it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.save();
                            }
                        })"
                        class="px-4 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#03b8ff]"
                    >
                        Create Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush
