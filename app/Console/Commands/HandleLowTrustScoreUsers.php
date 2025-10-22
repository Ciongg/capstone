<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HandleLowTrustScoreUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-low-trust-score-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archives users with extremely low trust scores (20 and below)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find users with trust score of 20 or below that aren't already archived
        $lowTrustUsers = User::where('trust_score', '<=', 20)
            ->whereNull('deleted_at')
            ->get();
            
        $count = $lowTrustUsers->count();
        
        // Archive each low trust score user
        $lowTrustUsers->each(function ($user) {
            // Count surveys before archiving for logging
            $surveyCount = $user->surveys()->count();
            
            
            // The UserObserver will handle archiving related surveys
            $user->delete();
            
        });

        
        return 0;
    }
}
