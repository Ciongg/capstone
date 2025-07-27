<?php

namespace App\Livewire\Profile\Modal;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditProfileModal extends Component
{
    use WithFileUploads;

    public $user;
    public $first_name;
    public $last_name;
    public $phone_number;
    public $photo;
    public $email;

    public function mount($user)
    {
        $this->user = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->phone_number = $user->phone_number;
        $this->email = $user->email;
    }

    public function save()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'nullable|digits:11|unique:users,phone_number,' . $this->user->id,
            'photo' => 'nullable|image|max:2048',
        ]);

        $this->user->first_name = $this->first_name;
        $this->user->last_name = $this->last_name;
        $this->user->phone_number = $this->phone_number;

        if ($this->photo) {
            // Delete old photo if exists
            if ($this->user->profile_photo_path) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $path = $this->photo->store('profile-photos', 'public');
            $this->user->profile_photo_path = $path;
        }

        $this->user->save();
        $this->dispatch('close-modal', name: 'edit-profile-modal');
        $this->dispatch('profileSaved');
    }

    public function render()
    {
        return view('livewire.profile.modal.edit-profile-modal');
    }
}
