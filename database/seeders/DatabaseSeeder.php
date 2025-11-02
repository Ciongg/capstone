<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Institution; // Import the Institution model
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the InstitutionSeeder first
        $this->call(InstitutionSeeder::class);

        // Find the 'Adamson University' institution
        $adamsonUniversity = Institution::where('name', 'Adamson University')->first();
        $nationalUniversity = Institution::where('name', 'National University')->first();

        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Miguel',
            'last_name' => 'Inciong',
            'email' => 'miguel.inciong@adamson.edu.ph',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
            'institution_id' => $adamsonUniversity ? $adamsonUniversity->id : null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Dion',
            'last_name' => 'Marmon',
            'email' => 'dion.marmon@adamson.edu.ph',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
            'institution_id' => $adamsonUniversity ? $adamsonUniversity->id : null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Kurt',
            'last_name' => 'Aquino',
            'email' => 'kurt.aquino@adamson.edu.ph',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
            'institution_id' => $adamsonUniversity ? $adamsonUniversity->id : null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Rence',
            'last_name' => 'Baldeo',
            'email' => 'rence.baldeo@adamson.edu.ph',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
            'institution_id' => $adamsonUniversity ? $adamsonUniversity->id : null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Institution',
            'last_name' => 'Admin',
            'email' => env('INST_ADMIN_ADU_EMAIL'),
            'password' => Hash::make(env('INST_ADMIN__ADU_PASSWORD')),
            'type' => 'institution_admin',
            'institution_id' => $adamsonUniversity ? $adamsonUniversity->id : null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Institution',
            'last_name' => 'Admin',
            'email' => env('INST_ADMIN_NU_EMAIL'),
            'password' => Hash::make(env('INST_ADMIN__NU_PASSWORD')),
            'type' => 'institution_admin',
            'institution_id' => $nationalUniversity ? $nationalUniversity->id : null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => env('SUPER_ADMIN_EMAIL'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD')),
            'type' => 'super_admin',
            'institution_id' => null,
            'is_active' => true, // Add is_active flag
            'last_active_at' => now(), 
            'is_accepted_terms' => true,
            'is_accepted_privacy_policy' => true,
        ]);





        // Updated seeder order - SurveyTopicSeeder before SurveySeeder
        $this->call([
            TagCategorySeeder::class,
            TagSeeder::class,
            SurveyTopicSeeder::class, 

            // SurveySeeder::class, 

        ]);

        // Create merchants if they don't exist (for voucher/reward association)
        \App\Models\Merchant::firstOrCreate([
            'name' => 'Coffee Company',
        ], [
            'merchant_code' => '1',
        ]);
        \App\Models\Merchant::firstOrCreate([
            'name' => 'Milk Tea Company',
        ], [
            'merchant_code' => '2',
        ]);
        \App\Models\Merchant::firstOrCreate([
            'name' => 'Chicken Company',
        ], [
            'merchant_code' => '3',
        ]);

        $this->call([
            RewardSeeder::class,      
            VoucherSeeder::class,     
            // TestResponseSeeder::class, 
        ]);
    }
}
