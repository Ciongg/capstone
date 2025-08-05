<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Survey;
use App\Services\TestTimeService;

class HandleSurveyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-survey-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set surveys to finished when their end date has passed and publish pending surveys when their start date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = TestTimeService::now();
        
        // Find surveys that are published or ongoing and have end dates in the past
        // Using whereIn and index on status and end_date for optimization
        $expiredCount = Survey::whereIn('status', ['published', 'ongoing'])
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $now)
            ->update(['status' => 'finished']);

        // Find pending surveys whose start date has passed and set to published
        // Using index on status and start_date for optimization
        $publishedCount = Survey::where('status', 'pending')
            ->whereNotNull('start_date')
            ->where('start_date', '<=', $now)
            ->update(['status' => 'published']);

        $this->info("Set $expiredCount expired surveys as finished.");
        $this->info("Set $publishedCount pending surveys as published.");
    }
}
