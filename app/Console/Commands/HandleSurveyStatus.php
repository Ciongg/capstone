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
        
        // Expire surveys
        $expiredCount = 0;
        Survey::whereIn('status', ['published', 'ongoing'])
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $now)
            ->get()
            ->each(function ($survey) use (&$expiredCount) {
                $survey->status = 'finished';
                $survey->save();
                $expiredCount++;
            });

        // Publish pending surveys
        $publishedCount = 0;
        Survey::where('status', 'pending')
            ->whereNotNull('start_date')
            ->where('start_date', '<=', $now)
            ->get()
            ->each(function ($survey) use (&$publishedCount) {
                $survey->status = 'published';
                $survey->save();
                $publishedCount++;
            });

        $this->info("Set $expiredCount expired surveys as finished.");
        $this->info("Set $publishedCount pending surveys as published.");
    }
}
