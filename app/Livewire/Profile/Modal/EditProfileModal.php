<?php

namespace App\Livewire\Profile\Modal;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\TestTimeService;

class EditProfileModal extends Component
{
    use WithFileUploads;

    public $user;
    public $first_name;
    public $last_name;
    public $phone_number;
    public $photo;
    public $email;
    
    // Add these properties for cooldown
    public $canUpdateProfile = false;
    public $daysUntilProfileUpdateAvailable = 0;
    public $hoursUntilProfileUpdateAvailable = 0;
    public $minutesUntilProfileUpdateAvailable = 0;
    public $timeUntilUpdateText = '';
    public $profileUpdateCooldownDays = 120; // 120 days = 4 months cooldown
    
    protected $listeners = ['refreshEditProfileModal' => 'refreshData'];

    public function mount($user)
    {
        $this->refreshData($user);
    }
    
    /**
     * Refresh the component data with the latest user data
     */
    public function refreshData($user = null)
    {
        // If a user is passed, use it; otherwise, use the current user
        if ($user) {
            $this->user = $user;
        } else {
            // Refresh the user data to get the latest
            $this->user = $this->user->fresh();
        }
        
        $this->first_name = $this->user->first_name;
        $this->last_name = $this->user->last_name;
        $this->phone_number = $this->user->phone_number;
        $this->email = $this->user->email;
        $this->photo = null; // Reset photo upload
        
        // Check if the user can update profile
        $this->canUpdateProfile = $this->user->canUpdateProfile();
        $this->calculateTimeUntilUpdate();
    }

    /**
     * Calculate time until profile update is available in a more human-readable format
     */
    protected function calculateTimeUntilUpdate()
    {
        if ($this->canUpdateProfile) {
            $this->timeUntilUpdateText = 'Available now';
            return;
        }

        $cooldownDays = $this->profileUpdateCooldownDays;
        $nextUpdateDate = $this->user->profile_updated_at->addDays($cooldownDays);
        $now = TestTimeService::now();
        
        // Check if we're very close to the target time (less than 30 seconds away)
        if ($now->diffInSeconds($nextUpdateDate, false) < 30) {
            $this->canUpdateProfile = true;
            $this->timeUntilUpdateText = 'Available now';
            return;
        }
        
        // Calculate time differences and round to integers
        $this->daysUntilProfileUpdateAvailable = (int) floor($now->diffInDays($nextUpdateDate, false));
        $this->hoursUntilProfileUpdateAvailable = (int) floor($now->diffInHours($nextUpdateDate, false) % 24);
        $this->minutesUntilProfileUpdateAvailable = (int) floor($now->diffInMinutes($nextUpdateDate, false) % 60);
        
        // Create human-readable text with proper rounding
        if ($this->daysUntilProfileUpdateAvailable > 0) {
            $this->timeUntilUpdateText = "Available in {$this->daysUntilProfileUpdateAvailable} " . 
                ($this->daysUntilProfileUpdateAvailable == 1 ? 'day' : 'days');
        } elseif ($now->diffInHours($nextUpdateDate, false) > 0) {
            $hours = (int) floor($now->diffInHours($nextUpdateDate, false));
            $this->timeUntilUpdateText = "Available in {$hours} " . ($hours == 1 ? 'hour' : 'hours');
        } else {
            // If we're less than 1 minute away, just show "Available now"
            if ($now->diffInMinutes($nextUpdateDate, false) < 1) {
                $this->canUpdateProfile = true;
                $this->timeUntilUpdateText = 'Available now';
            } else {
                $minutes = max(1, (int) ceil($now->diffInMinutes($nextUpdateDate, false)));
                $this->timeUntilUpdateText = "Available in {$minutes} " . ($minutes == 1 ? 'minute' : 'minutes');
            }
        }
    }

    public function save()
    {
        // Check if user can update profile
        if (!$this->canUpdateProfile) {
            session()->flash('error', 'You cannot update your profile at this time. Please try again in ' . 
                $this->timeUntilUpdateText . '.');
            return;
        }
        
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

        // Update the profile_updated_at timestamp
        $this->user->profile_updated_at = TestTimeService::now();
        $this->user->save();
        
        // Refresh the user data to reflect the updated profile_updated_at
        $this->user = $this->user->fresh();
        
        // Recalculate cooldown status
        $this->canUpdateProfile = $this->user->canUpdateProfile();
        $this->calculateTimeUntilUpdate();
        
        // Show success message in the parent component
        session()->flash('profile_updated', 'Your profile has been updated successfully! You can update it again in 4 months.');
        
        $this->dispatch('close-modal', name: 'edit-profile-modal');
        $this->dispatch('profileSaved');
    }

    /**
     * Remove the uploaded photo preview
     */
    public function removePhotoPreview()
    {
        $this->photo = null;
    }

    /**
     * Delete the current profile photo from storage
     */
    public function deleteCurrentPhoto()
    {
        if ($this->user->profile_photo_path) {
            Storage::disk('public')->delete($this->user->profile_photo_path);
            
            $this->user->profile_photo_path = null;
            $this->user->save();
        }
    }
    
    public function render()
    {
        return view('livewire.profile.modal.edit-profile-modal');
    }
}
