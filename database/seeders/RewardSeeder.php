<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

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
                'image_path' => null, // Changed
            ],
            [
                'name' => 'Experience Level Increase',
                'description' => 'Instantly gain 10 XP to level up faster',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 10,
                'quantity' => -1, // -1 means infinite
                'type' => 'system',
                'image_path' => null, // Changed
            ],
            
        ];
        
        // Specific voucher rewards with fixed quantity and images
        $voucherRewards = [
            [
                'name' => 'Buy 1 Take 1 Coffee',
                'description' => 'Redeem this voucher for a Buy 1 Take 1 coffee at any participating Coffee Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 600,
                'quantity' => 20, // Fixed quantity of 10
                'type' => 'voucher',
                'image_path' => 'voucher-images/Coffee.jpg', // Image path for Coffee Place
            ],
            [
                'name' => '₱50 Gift Card',
                'description' => 'Redeem this voucher for a ₱50 gift card at any Milk Tea Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 750,
                'quantity' => 15, // Fixed quantity of 10
                'type' => 'voucher',
                'image_path' => 'voucher-images/Milk-tea.jpg', // Image path for Milktea
            ],
            [
                'name' => 'Buy 1 Take 1 Chicken Meal',
                'description' => 'Redeem this voucher for a Buy 1 Take 1 chicken meal at any Chicken Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 800,
                'quantity' => 10, // Fixed quantity of 10
                'type' => 'voucher',
                'image_path' => 'voucher-images/Chicken.jpg', // Image path for Chicken Company
            ],
        ];
        
        
        // Insert all rewards into the database
        foreach (array_merge($systemRewards, $voucherRewards) as $reward) {
            Reward::create($reward);
        }
    }
}
