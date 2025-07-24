<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\Voucher;
use App\Models\Merchant;
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

        // Create merchants if they don't exist
        $coffeeMerchant = Merchant::firstOrCreate([
            'name' => 'Coffee Company',
        ], [
            'merchant_code' => '1',
        ]);
        $milkTeaMerchant = Merchant::firstOrCreate([
            'name' => 'Milk Tea Company',
        ], [
            'merchant_code' => '2',
        ]);
        $chickenMerchant = Merchant::firstOrCreate([
            'name' => 'Chicken Company',
        ], [
            'merchant_code' => '3',
        ]);

        foreach ($voucherTypeRewards as $reward) {
            // Use simple mapping for store names based on image path
            $storeName = "Company"; // Default fallback
            $promo = "Special Offer"; // Default fallback
            $merchantId = null;
            // Map image paths to store names and merchants
            if (strpos($reward->image_path, 'Coffee') !== false) {
                $storeName = "Coffee Company";
                $promo = "Buy 1 Take 1";
                $merchantId = $coffeeMerchant->id;
            } 
            elseif (strpos($reward->image_path, 'Milk-tea') !== false) {
                $storeName = "Milk Tea Company";
                $promo = "₱50 Gift Card";
                $merchantId = $milkTeaMerchant->id;
            }
            elseif (strpos($reward->image_path, 'Chicken') !== false) {
                $storeName = "Chicken Company";
                $promo = "Buy 1 Take 1 Meal";
                $merchantId = $chickenMerchant->id;
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
                    'merchant_id' => $merchantId,
                ]);
            }
        }
    }
}
