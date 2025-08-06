<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use App\Services\TestTimeService;

class HandleAnnouncementDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-announcement-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set announcements as inactive if their end date has passed, and active if their start date is now or past and end date not passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = TestTimeService::now();

        // Set expired announcements to inactive
        $expiredCount = 0;
        Announcement::where('active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now)
            ->get()
            ->each(function ($announcement) use (&$expiredCount) {
                $announcement->active = false;
                $announcement->save();
                $expiredCount++;
            });

        // Set eligible announcements to active
        $activeCount = 0;
        Announcement::where('active', false)
            ->where(function($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            })
            ->get()
            ->each(function ($announcement) use (&$activeCount) {
                $announcement->active = true;
                $announcement->save();
                $activeCount++;
            });

        $this->info("Set $expiredCount expired announcements as inactive.");
        $this->info("Set $activeCount eligible announcements as active.");
    }
}
