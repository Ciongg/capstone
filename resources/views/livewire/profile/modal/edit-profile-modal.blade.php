<form wire:submit.prevent="save" class="space-y-6" x-data="{
    confirmProfileSave() {
        Swal.fire({
            title: 'Confirm Profile Update',
            html: '<div>Are you sure you want to update your profile?<br><small>Once updated, you will not be able to change your profile information again for 4 months.</small></div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, update',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.save();
            }
        });
    }
}">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Edit Profile</h2>
        @if($canUpdateProfile)
            <span class="text-green-500 text-sm font-medium">Available for update</span>
        @else
            <span class="text-red-500 text-sm font-medium italic">{{ $timeUntilUpdateText }}</span>
        @endif
    </div>

    <!-- Profile Photo Upload (styled like survey banner image upload) -->
    <div>
        <label class="block font-semibold mb-2 text-center">Profile Photo</label>
        <div class="flex flex-col items-center justify-center w-full">
            <label for="profile-photo" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 @if(!$canUpdateProfile) opacity-50 cursor-not-allowed @endif">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <!-- Icon -->
                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <!-- Text -->
                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 2MB)</p>
                    <p x-text="fileName ? fileName : 'No file chosen'" class="text-xs text-gray-600 mt-2"></p>
                </div>
                <input id="profile-photo"
                       type="file"
                       class="hidden"
                       wire:model.defer="photo"
                       accept="image/*"
                       @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                       @if(!$canUpdateProfile) disabled @endif />
            </label>
            <div wire:loading wire:target="photo" class="text-sm text-gray-500 mt-1">Uploading...</div>
            <!-- Image Preview -->
            @if ($photo)
                <div class="mt-4 flex flex-col items-center">
                    <span class="block text-sm font-medium text-gray-700 mb-1">Uploaded Profile Photo Preview:</span>
                    <div class="relative w-32 h-32 flex items-center justify-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-gray-300 flex items-center justify-center">
                            <img src="{{ $photo->temporaryUrl() }}" alt="New Profile Photo Preview" class="w-full h-full object-cover">
                        </div>
                        @if($canUpdateProfile)
                        <button 
                            type="button" 
                            wire:click="removePhotoPreview" 
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 z-10"
                            title="Remove photo"
                            style="box-shadow: 0 2px 6px rgba(0,0,0,0.15);"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            @elseif ($user->profile_photo_path)
                <div class="mt-4 flex flex-col items-center">
                    <span class="block text-sm font-medium text-gray-700 mb-1">Current Profile Photo:</span>
                    <div class="relative w-32 h-32 flex items-center justify-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-gray-300 flex items-center justify-center">
                            <img src="{{ asset('storage/' . ($user->profile_photo_path)) }}" alt="Survey Banner" class="max-h-40 rounded shadow" />
                        </div>
                        @if($canUpdateProfile)
                        <button 
                            type="button" 
                            wire:click="deleteCurrentPhoto" 
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 z-10"
                            title="Delete photo"
                            style="box-shadow: 0 2px 6px rgba(0,0,0,0.15);"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            @endif
            @error('photo') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- First Name -->
    <div>
        <label for="first_name" class="block font-semibold mb-1">First Name</label>
        <input type="text" id="first_name" wire:model.defer="first_name" 
               class="w-full border rounded px-3 py-2 @if(!$canUpdateProfile) bg-gray-100 cursor-not-allowed @endif" 
               placeholder="e.g. John" 
               @if(!$canUpdateProfile) disabled @endif />
        @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- Last Name -->
    <div>
        <label for="last_name" class="block font-semibold mb-1">Last Name</label>
        <input type="text" id="last_name" wire:model.defer="last_name" 
               class="w-full border rounded px-3 py-2 @if(!$canUpdateProfile) bg-gray-100 cursor-not-allowed @endif" 
               placeholder="e.g. Doe" 
               @if(!$canUpdateProfile) disabled @endif />
        @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- Phone Number -->
    <div>
        <label for="phone_number" class="block font-semibold mb-1">Phone Number</label>
        <input type="text" id="phone_number" wire:model.defer="phone_number" 
               class="w-full border rounded px-3 py-2 @if(!$canUpdateProfile) bg-gray-100 cursor-not-allowed @endif" 
               placeholder="e.g. 09691590326" inputmode="numeric" pattern="[0-9]*" maxlength="11" 
               @if(!$canUpdateProfile) disabled @endif />
        @error('phone_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        @if($errors->has('phone_number') && strlen($phone_number ?? '') < 11)
            <span class="text-red-500 text-sm">Phone number must be exactly 11 digits.</span>
        @endif
    </div>

    <!-- Email Address -->
    <div>
        <label for="email" class="block font-semibold mb-1">Email Address</label>
        <input type="email" id="email" value="{{ $email }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" disabled />
    </div>

    <!-- Error Message Display -->
    @if (session()->has('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-3 text-sm text-red-800 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Save Button -->
    @if($canUpdateProfile)
    <button 
        type="button"
        class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 w-full flex items-center justify-center"
        x-on:click="confirmProfileSave()"
        wire:loading.attr="disabled"
        style="min-width: 180px;"
    >
        <span class="flex items-center justify-center w-full">
            <span wire:loading.remove wire:target="save">Save Changes</span>
            <span wire:loading wire:target="save" class="flex items-center ml-2">
                <svg class="animate-spin h-5 w-5 text-white mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </span>
        </span>
    </button>
    @endif
    <div class="mt-2">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 text-sm text-yellow-800 rounded">
            <strong>Note:</strong> Once updated, you will not be able to change your profile information again for 4 months. This is to ensure data integrity.
        </div>
    </div>
</form>
