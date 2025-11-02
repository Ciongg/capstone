<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Merchant;

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
                'image_filename' => 'surveyBoost.svg', // Change to image_filename
            ],
            [
                'name' => 'Experience Level Increase',
                'description' => 'Instantly gain 10 XP to level up faster',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 10,
                'quantity' => -1,
                'type' => 'system',
                'image_filename' => 'levelUp.svg', // Change to image_filename
            ],
        ];

        // Ensure storage/app/public/voucher-images exists
        if (!File::isDirectory(storage_path('app/public/voucher-images'))) {
            File::makeDirectory(storage_path('app/public/voucher-images'), 0755, true);
        }
        
        // Ensure storage/app/public/reward-images exists for system rewards
        if (!File::isDirectory(storage_path('app/public/reward-images'))) {
            File::makeDirectory(storage_path('app/public/reward-images'), 0755, true);
        }

        // Copy system reward images
        foreach ($systemRewards as &$reward) {
            $sourcePath = public_path('images/rewards/' . $reward['image_filename']);
            $destPath = storage_path('app/public/reward-images/' . $reward['image_filename']);
            if (File::exists($sourcePath) && !File::exists($destPath)) {
                File::copy($sourcePath, $destPath);
            }
            $reward['image_path'] = 'reward-images/' . $reward['image_filename'];
            unset($reward['image_filename']);
        }
        unset($reward);

        // Fetch merchants for association
        $coffeeMerchant = Merchant::where('name', 'Coffee Company')->first();
        $milkTeaMerchant = Merchant::where('name', 'Milk Tea Company')->first();
        $chickenMerchant = Merchant::where('name', 'Chicken Company')->first();

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
                'merchant_id' => $coffeeMerchant ? $coffeeMerchant->id : null,
            ],
            [
                'name' => '₱50 Gift Card',
                'description' => 'Redeem this voucher for a ₱50 gift card at any Milk Tea Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 750,
                'quantity' => 15,
                'type' => 'voucher',
                'image_filename' => 'Milk-tea.jpg',
                'merchant_id' => $milkTeaMerchant ? $milkTeaMerchant->id : null,
            ],
            [
                'name' => 'Buy 1 Take 1 Chicken Meal',
                'description' => 'Redeem this voucher for a Buy 1 Take 1 chicken meal at any Chicken Company branch',
                'status' => Reward::STATUS_AVAILABLE,
                'cost' => 800,
                'quantity' => 10,
                'type' => 'voucher',
                'image_filename' => 'Chicken.jpg',
                'merchant_id' => $chickenMerchant ? $chickenMerchant->id : null,
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


// <?php

// namespace Database\Seeders;

// use App\Models\Reward;
// use App\Models\Merchant;
// use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\File;

// class RewardSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {

//         $this->seedSystemRewards();

//         $this->seedVoucherRewards();
//     }

//     /**
//      * Seed system (infinite) rewards.
//      */
//     private function seedSystemRewards(): void
//     {
//         $systemRewards = [
//             [
//                 'name' => 'Survey Boost',
//                 'description' => 'Allocate +5 more points to your survey to increase survey visibility',
//                 'status' => Reward::STATUS_AVAILABLE,
//                 'cost' => 100,
//                 'quantity' => -1,
//                 'type' => 'system',
//                 'image_filename' => 'surveyBoost.svg',
//             ],
//             [
//                 'name' => 'Experience Level Increase',
//                 'description' => 'Instantly gain 10 XP to level up faster',
//                 'status' => Reward::STATUS_AVAILABLE,
//                 'cost' => 10,
//                 'quantity' => -1,
//                 'type' => 'system',
//                 'image_filename' => 'levelUp.svg',
//             ],
//         ];

//         $this->ensureDirectory(storage_path('app/public/reward-images'));

//         foreach ($systemRewards as &$reward) {
//             $reward['image_path'] = $this->copyImage(
//                 public_path('images/rewards/' . $reward['image_filename']),
//                 storage_path('app/public/reward-images/' . $reward['image_filename'])
//             );
//             unset($reward['image_filename']);
//         }

//         Reward::insert($systemRewards);
//     }

//     /**
//      * Seed merchant voucher rewards.
//      */
//     private function seedVoucherRewards(): void
//     {
//         $this->ensureDirectory(storage_path('app/public/voucher-images'));

//         $coffeeMerchant = Merchant::where('name', 'Coffee Company')->first();
//         $milkTeaMerchant = Merchant::where('name', 'Milk Tea Company')->first();
//         $chickenMerchant = Merchant::where('name', 'Chicken Company')->first();

//         $voucherRewards = [
//             [
//                 'name' => 'Buy 1 Take 1 Coffee',
//                 'description' => 'Redeem this voucher for a Buy 1 Take 1 coffee at any participating Coffee Company branch',
//                 'status' => Reward::STATUS_AVAILABLE,
//                 'cost' => 600,
//                 'quantity' => 20,
//                 'type' => 'voucher',
//                 'image_filename' => 'Coffee.jpg',
//                 'merchant_id' => $coffeeMerchant?->id,
//             ],
//             [
//                 'name' => '₱50 Gift Card',
//                 'description' => 'Redeem this voucher for a ₱50 gift card at any Milk Tea Company branch',
//                 'status' => Reward::STATUS_AVAILABLE,
//                 'cost' => 750,
//                 'quantity' => 15,
//                 'type' => 'voucher',
//                 'image_filename' => 'Milk-tea.jpg',
//                 'merchant_id' => $milkTeaMerchant?->id,
//             ],
//             [
//                 'name' => 'Buy 1 Take 1 Chicken Meal',
//                 'description' => 'Redeem this voucher for a Buy 1 Take 1 chicken meal at any Chicken Company branch',
//                 'status' => Reward::STATUS_AVAILABLE,
//                 'cost' => 800,
//                 'quantity' => 10,
//                 'type' => 'voucher',
//                 'image_filename' => 'Chicken.jpg',
//                 'merchant_id' => $chickenMerchant?->id,
//             ],
//         ];

//         foreach ($voucherRewards as &$reward) {
//             $reward['image_path'] = $this->copyImage(
//                 public_path('images/vouchers/' . $reward['image_filename']),
//                 storage_path('app/public/voucher-images/' . $reward['image_filename'])
//             );
//             unset($reward['image_filename']);
//         }

//         Reward::insert($voucherRewards);
//     }

//     /**
//      * Utility to ensure a directory exists.
//      */
//     private function ensureDirectory(string $path): void
//     {
//         if (!File::isDirectory($path)) {
//             File::makeDirectory($path, 0755, true);
//         }
//     }

//     /**
//      * Utility to safely copy an image and return its storage path.
//      */
//     private function copyImage(string $source, string $destination): string
//     {
//         if (File::exists($source) && !File::exists($destination)) {
//             File::copy($source, $destination);
//         }

//         return str_contains($destination, 'reward-images')
//             ? 'reward-images/' . basename($destination)
//             : 'voucher-images/' . basename($destination);
//     }
// }
