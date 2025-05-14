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
                'description' => 'Boost your survey to get more visibility in the feed for 3 days',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 300,
                'quantity' => -1, // -1 means infinite
                'type' => 'system',
                'image_path' => null, // Changed
            ],
            [
                'name' => 'Experience Level Increase',
                'description' => 'Instantly gain 500 XP to level up faster',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 200,
                'quantity' => -1, // -1 means infinite
                'type' => 'system',
                'image_path' => null, // Changed
            ],
           
        ];
        
        // Voucher rewards with limited quantity
        $companies = ['Kape Kuripot', 'Chixsilog', 'Havens Brew', 'Potato Corner', 'Jollibee', 'McDo'];
        $voucherTypes = [
            'Buy 1 Take 1',
            '50% Off Coupon',
            '₱50 Gift Card',
            '₱100 Gift Card',
            '₱200 Gift Card',
            '₱500 Gift Card',
            'Free Item'
        ];
        
        $voucherRewards = [];
        
        foreach ($companies as $index => $company) {
            // Associate each company with 1-2 voucher types
            $voucherType = $voucherTypes[$index % count($voucherTypes)];
            
            // Set different points based on the value
            $pointCost = 0;
            if (strpos($voucherType, '50 Gift') !== false) {
                $pointCost = 100;
            } elseif (strpos($voucherType, '100 Gift') !== false) {
                $pointCost = 200;
            } elseif (strpos($voucherType, '200 Gift') !== false) {
                $pointCost = 300;
            } elseif (strpos($voucherType, '500 Gift') !== false) {
                $pointCost = 500;
            } else {
                // For non-specific amounts, assign a random point value
                $pointCost = rand(1, 4) * 100; // 100, 200, 300, or 400
            }
            
            $voucherRewards[] = [
                'name' => "$company $voucherType",
                'description' => "Redeem this voucher at any participating $company branch",
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => $pointCost,
                'quantity' => rand(5, 20), // Limited quantity between 5-20
                'type' => 'voucher',
                'image_path' => null, // Placeholder images would be set in a real app
            ];
            
            // Add a second voucher type for some companies
            if ($index % 3 == 0) {
                $secondType = $voucherTypes[($index + 4) % count($voucherTypes)];
                $secondPointCost = rand(1, 5) * 100;
                
                $voucherRewards[] = [
                    'name' => "$company $secondType",
                    'description' => "Limited time offer from $company",
                    'status' => Reward::STATUS_AVAILABLE,
                    'cost' => $secondPointCost,
                    'quantity' => rand(5, 20),
                    'type' => 'voucher',
                    'image_path' => null,
                ];
            }
        }
        
        // Add some monetary rewards
        $monetaryRewards = [
            [
                'name' => '₱100 PayMaya Credit',
                'description' => 'Get ₱100 credited to your PayMaya account',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 200,
                'quantity' => 10,
                'type' => 'monetary',
                'image_path' => null, // Changed
            ],
            [
                'name' => '₱200 GCash Transfer',
                'description' => 'Get ₱200 sent to your GCash account',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 300,
                'quantity' => 10,
                'type' => 'monetary',
                'image_path' => null, // Changed
            ],
        ];
        
        // Insert all rewards into the database
        foreach (array_merge($systemRewards, $voucherRewards, $monetaryRewards) as $reward) {
            Reward::create($reward);
        }
    }
}
