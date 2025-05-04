<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads; // Add this
use Illuminate\Support\Facades\Storage; // Add this

class ViewProfile extends Component
{
    use WithFileUploads; // Add this

    public User $user;
    public $photo; // Property for the file upload

    // Validation rules for the photo
    protected $rules = [
        'photo' => 'nullable|image|max:2048', // Max 2MB, adjust as needed
    ];

    public function mount(User $user)
    {
        $this->user = $user;
    }

    // This method runs automatically when the 'photo' property is updated
    public function updatedPhoto()
    {
        $this->validateOnly('photo'); // Validate the uploaded file

        if ($this->photo) {
            // Delete the old photo if it exists
            if ($this->user->profile_photo_path) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }

            // Store the new photo (e.g., in 'storage/app/public/profile-photos')
            $path = $this->photo->store('profile-photos', 'public');

            // Update the user record
            $this->user->forceFill([
                'profile_photo_path' => $path,
            ])->save();

            // Reset the photo property to clear the input
            $this->photo = null; 
            
            // Refresh user data to show new image immediately
            $this->user = $this->user->fresh(); 

            // Optionally dispatch an event if other components need to know
            // $this->dispatch('profilePhotoUpdated'); 
        }
    }

    public function render()
    {
        return view('livewire.profile.view-profile');
    }
}

class ViewHistory extends Component
{
    public $user;

    public function mount($user)
    {
        // Ensure $user is a User model instance
        if (is_numeric($user)) {
            $this->user = User::find($user);
        } elseif ($user instanceof User) {
            $this->user = $user;
        } else {
            $this->user = null;
        }
    }

    public function render()
    {
        return view('livewire.profile.view-history');
    }
}
