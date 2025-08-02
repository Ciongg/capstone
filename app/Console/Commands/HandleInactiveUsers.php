<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\TestTimeService;

class HandleInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate and archive users based on inactivity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = TestTimeService::now();
        $inactiveAfter = $now->copy()->subMonths(6);
        $archiveAfter = $now->copy()->subMonths(12);

        // Set users to inactive (is_active = false), except super_admin and institution_admin
        User::where('is_active', true)
            ->whereNotNull('last_active_at')
            ->where('last_active_at', '<=', $inactiveAfter)
            ->where('last_active_at', '>', $archiveAfter)
            ->whereNotIn('type', ['super_admin', 'institution_admin'])
            ->update(['is_active' => false]);

        // Archive researchers (soft delete: set deleted_at)
        User::where('type', 'researcher')
            ->whereNull('deleted_at')
            ->whereNotNull('last_active_at')
            ->where('last_active_at', '<=', $archiveAfter)
            ->update(['deleted_at' => $now]);

        $this->info('Inactive and archived users handled.');
    }
}
