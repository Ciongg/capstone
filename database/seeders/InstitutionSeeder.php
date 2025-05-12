<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institution; // Import the Institution model
use Illuminate\Support\Facades\DB; // Import DB facade

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing institutions first to avoid duplicates if re-running
        DB::table('institutions')->delete();

        Institution::create([
            'name' => 'Adamson University',
            'domain' => 'adamson.edu.ph'
        ]);

         Institution::create([
            'name' => 'Unibersidad ng Pilipinas',
            'domain' => 'up.edu.ph'
        ]);

         Institution::create([
            'name' => 'national university',
            'domain' => 'nu.edu.ph'
        ]);
        // You can add more institutions here if needed
        // Institution::create(['name' => 'Another University', 'domain' => 'another.edu']);
    }
}
