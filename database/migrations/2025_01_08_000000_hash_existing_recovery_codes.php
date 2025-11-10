<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $settings = DB::table('user_two_factor_settings')
            ->whereNotNull('recovery_codes')
            ->get();

        foreach ($settings as $setting) {
            $recoveryCodes = json_decode($setting->recovery_codes, true);
            
            if (empty($recoveryCodes)) {
                continue;
            }

            $hashedCodes = [];
            $needsUpdate = false;

            foreach ($recoveryCodes as $code) {
                // Check if already hashed (bcrypt hashes start with $2y$)
                if (str_starts_with($code, '$2y$')) {
                    $hashedCodes[] = $code;
                } else {
                    // Hash the plain text code
                    $hashedCodes[] = Hash::make($code);
                    $needsUpdate = true;
                }
            }

            // Only update if we found unhashed codes
            if ($needsUpdate) {
                DB::table('user_two_factor_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'recovery_codes' => json_encode($hashedCodes),
                        'updated_at' => now()
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Cannot reverse hash, so no down migration
        // If you need to rollback, you'll need to regenerate recovery codes for users
    }
};
