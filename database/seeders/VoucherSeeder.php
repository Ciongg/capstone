<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $voucherTypeRewards = Reward::where('type', 'voucher')->get();

        foreach ($voucherTypeRewards as $reward) {
            // Use simple mapping for store names based on image path
            $storeName = "Company"; // Default fallback
            $promo = "Special Offer"; // Default fallback
            
            // Map image paths to store names
            if (strpos($reward->image_path, 'Coffee') !== false) {
                $storeName = "Coffee Company";
                $promo = "Buy 1 Take 1";
            } 
            elseif (strpos($reward->image_path, 'Milk-tea') !== false) {
                $storeName = "Milk Tea Company";
                $promo = "₱50 Gift Card";
            }
            elseif (strpos($reward->image_path, 'Chicken') !== false) {
                $storeName = "Chicken Company";
                $promo = "Buy 1 Take 1 Meal";
            }

            // Create multiple instances for each voucher reward
            $numberOfInstances = $reward->quantity > 0 ? $reward->quantity : 10;

            for ($i = 0; $i < $numberOfInstances; $i++) {
                Voucher::create([
                    'reward_id' => $reward->id,
                    'reference_no' => Str::upper(Str::random(3)) . '-' . rand(10000, 99999) . '-' . Str::upper(Str::random(2)),
                    'store_name' => $storeName,
                    'promo' => $promo,
                    'cost' => $reward->cost,
                    // 'level_requirement' => ceil($reward->cost / 100), // Removed, use rank_requirement if needed
                    'expiry_date' => Carbon::now()->addMonths(rand(1, 6)),
                    'availability' => 'available',
                    'image_path' => $reward->image_path, // Use image from parent reward
                ]);
            }
        }
    }
}
