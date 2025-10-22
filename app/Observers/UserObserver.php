<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "deleted" event.
     * This triggers for soft deletes too.
     */
    public function deleted(User $user): void
    {
        // Soft delete all surveys created by this user
        // Use 'finished' status which should be a valid value in the status check constraint
        $surveyCount = $user->surveys()->count();
        $user->surveys()->update(['status' => 'finished']);
        
        // Log the action
        Log::info("User {$user->id} ({$user->email}) was archived. {$surveyCount} surveys were marked as archived.");
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Restore surveys that were archived due to user being archived
        $surveyCount = $user->surveys()->where('status', 'finished')->count();
        
        // Update the status to 'published' for surveys that were marked as finished
        $user->surveys()->where('status', 'finished')->update(['status' => 'published']);
        
        // Log the action
        Log::info("User {$user->id} ({$user->email}) was restored. {$surveyCount} surveys were restored to published status.");

    }
}