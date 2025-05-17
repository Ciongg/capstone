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
            // Determine store name and promo from reward name
            // Assumes reward name is like "CompanyName PromoType"
            $nameParts = explode(' ', $reward->name, 2);
            $storeName = $nameParts[0];
            $promo = $nameParts[1] ?? 'Special Offer';
            if (count($nameParts) > 2) { // If company name has spaces
                $companyNameWords = [];
                $promoWords = [];
                $foundPromoStart = false;
                $possiblePromoKeywords = ['Buy 1 Take 1', 'Off Coupon', 'Gift Card', 'Free Item']; // Add more as needed

                foreach (explode(' ', $reward->name) as $word) {
                    if (!$foundPromoStart) {
                        $companyNameWords[] = $word;
                        // Check if the current part of the name matches a promo keyword start
                        foreach ($possiblePromoKeywords as $keyword) {
                            if (str_starts_with(implode(' ', array_slice(explode(' ', $reward->name), count($companyNameWords) -1 )), $keyword)) {
                                $foundPromoStart = true;
                                // The word before the keyword might still be part of the company name if it's short
                                // This is a heuristic and might need refinement
                                if (count($companyNameWords) > 1 && strlen(end($companyNameWords)) < 3 && !$this->isKnownCompanyNamePart(end($companyNameWords))) {
                                     // e.g. "Jollibee ₱50 Gift Card" - Jollibee is company, ₱50 Gift Card is promo
                                } else if (count($companyNameWords) > 1) {
                                     // Take all but the last word as company name if a promo keyword is found
                                     $lastWord = array_pop($companyNameWords);
                                     $promoWords[] = $lastWord;
                                }
                                break;
                            }
                        }
                    }
                    if ($foundPromoStart) {
                        $promoWords[] = $word;
                    }
                }
                 if (empty($promoWords) && !empty($companyNameWords)) { // If no promo keyword found, assume last word is promo
                    $promoWords[] = array_pop($companyNameWords);
                }

                $storeName = trim(implode(' ', $companyNameWords));
                $promo = trim(implode(' ', $promoWords));
                if (empty($promo)) $promo = 'Special Offer';


            }


            // Create multiple instances for each voucher reward
            $numberOfInstances = $reward->quantity > 0 ? $reward->quantity : 10; // If reward has limited qty, use that, else create 10

            for ($i = 0; $i < $numberOfInstances; $i++) {
                Voucher::create([
                    'reward_id' => $reward->id,
                    'reference_no' => Str::upper(Str::random(3)) . '-' . rand(10000, 99999) . '-' . Str::upper(Str::random(2)),
                    'store_name' => $storeName,
                    'promo' => $promo,
                    'cost' => $reward->cost,
                    'level_requirement' => ceil($reward->cost / 100), // Example logic
                    'expiry_date' => Carbon::now()->addMonths(rand(1, 6)),
                    'availability' => 'available',
                    'image_path' => $reward->image_path, // Use image from parent reward
                ]);
            }
        }
    }

    private function isKnownCompanyNamePart(string $word): bool
    {
        // Add short words that can be part of company names
        $knownParts = ['McDo', 'Nu', 'AdU'];
        return in_array($word, $knownParts);
    }
}
