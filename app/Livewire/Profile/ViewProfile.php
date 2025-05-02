<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use App\Models\User;

class ViewProfile extends Component
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
