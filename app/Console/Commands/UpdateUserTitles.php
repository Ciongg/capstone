<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserExperienceService;
use Illuminate\Console\Command;

class UpdateUserTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-titles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all user titles based on their current level';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {
            $level = UserExperienceService::calculateLevel($user->experience_points);
            $oldTitle = $user->title ?: 'Newbie';
            $newTitle = UserExperienceService::getTitleForLevel($level);
            
            if ($oldTitle !== $newTitle) {
                $user->title = $newTitle;
                $user->save();
                $updated++;
                
                $this->info("Updated user {$user->name}: Level {$level}, Title changed from '{$oldTitle}' to '{$newTitle}'");
            }
        }

        $this->info("Updated titles for {$updated} users out of {$users->count()} total users.");
    }
}
