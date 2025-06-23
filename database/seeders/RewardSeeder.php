<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // System rewards with infinite quantity
        $systemRewards = [
            [
                'name' => 'Survey Boost',
                'description' => 'Allocate +5 more points to your survey to increase survey visiblity',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 500,
                'quantity' => -1, // -1 means infinite
                'type' => 'system',
                'image_path' => null,
            ],
            [
                'name' => 'Experience Level Increase',
                'description' => 'Instantly gain 10 XP to level up faster',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 10,
                'quantity' => -1,
                'type' => 'system',
                'image_path' => null,
            ],
        ];

        // Ensure storage/app/public/voucher-images exists
        if (!File::isDirectory(storage_path('app/public/voucher-images'))) {
            File::makeDirectory(storage_path('app/public/voucher-images'), 0755, true);
        }

        // Define the voucher rewards and copy their images
        $voucherRewards = [
            [
                'name' => 'Buy 1 Take 1 Coffee',
                'description' => 'Redeem this voucher for a Buy 1 Take 1 coffee at any participating Coffee Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 600,
                'quantity' => 20,
                'type' => 'voucher',
                'image_filename' => 'Coffee.jpg',
            ],
            [
                'name' => '₱50 Gift Card',
                'description' => 'Redeem this voucher for a ₱50 gift card at any Milk Tea Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 750,
                'quantity' => 15,
                'type' => 'voucher',
                'image_filename' => 'Milk-tea.jpg',
            ],
            [
                'name' => 'Buy 1 Take 1 Chicken Meal',
                'description' => 'Redeem this voucher for a Buy 1 Take 1 chicken meal at any Chicken Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 800,
                'quantity' => 10,
                'type' => 'voucher',
                'image_filename' => 'Chicken.jpg',
            ],
        ];

        foreach ($voucherRewards as &$reward) {
            $sourcePath = public_path('images/vouchers/' . $reward['image_filename']);
            $destPath = storage_path('app/public/voucher-images/' . $reward['image_filename']);
            if (File::exists($sourcePath) && !File::exists($destPath)) {
                File::copy($sourcePath, $destPath);
            }
            $reward['image_path'] = 'voucher-images/' . $reward['image_filename'];
            unset($reward['image_filename']);
        }
        unset($reward);

        // Insert all rewards into the database
        foreach (array_merge($systemRewards, $voucherRewards) as $reward) {
            Reward::create($reward);
        }
    }
}
