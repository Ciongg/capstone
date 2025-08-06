<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Carbon\Carbon;
use App\Services\TestTimeService;
class HandleExpiredRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-expired-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired vouchers and user vouchers as expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = TestTimeService::now();
        $this->info("Starting expired rewards check at {$now}...");
        
        // Part 1: Update all available vouchers that have expired
        $expiredVouchersCount = Voucher::where('availability', 'available')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $now)
            ->update(['availability' => 'expired']);
            
        $this->info("Updated {$expiredVouchersCount} expired vouchers.");
        
        // Part 2: Update all user vouchers with available/active status whose vouchers have expired
        $expiredUserVouchersCount = 0;
        UserVoucher::whereIn('status', ['available', 'active'])
            ->whereHas('voucher', function ($query) use ($now) {
                $query->whereNotNull('expiry_date')
                      ->where('expiry_date', '<', $now);
            })
            ->chunkById(1000, function ($userVouchers) use ($now, &$expiredUserVouchersCount) {
                foreach ($userVouchers as $uv) {
                    $uv->status = 'expired';
                    $uv->updated_at = $now;
                    $uv->save();
                    $expiredUserVouchersCount++;
                }
            });
        
        $this->info("Updated {$expiredUserVouchersCount} expired user vouchers.");
        $this->info("Completed expired rewards check.");
        
        return 0;
    }
}
