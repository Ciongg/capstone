<?php

namespace App\Console\Commands;

use App\Models\EmailVerification;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    protected $signature = 'otp:cleanup';
    protected $description = 'Clean up expired OTP records from the database';

    public function handle()
    {
        $deleted = EmailVerification::where('expires_at', '<', now())->delete();
        
        $this->info("Cleaned up {$deleted} expired OTP records.");
        
        return Command::SUCCESS;
    }
}