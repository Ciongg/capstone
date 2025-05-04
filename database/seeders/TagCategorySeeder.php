<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories first to avoid conflicts if re-running
        DB::table('tag_categories')->delete(); 

        DB::table('tag_categories')->insert([
            // Keep IDs consistent if possible, or let auto-increment handle it
            ['id' => 1, 'name' => 'Age Group', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Gender', 'created_at' => now(), 'updated_at' => now()], // Simplified
            ['id' => 3, 'name' => 'Location (Region)', 'created_at' => now(), 'updated_at' => now()], // Clarified scope
            ['id' => 4, 'name' => 'Education Level', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Employment Status', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Household Income (Est.)', 'created_at' => now(), 'updated_at' => now()], // Renamed
            ['id' => 7, 'name' => 'Household Composition', 'created_at' => now(), 'updated_at' => now()], // Renamed
            ['id' => 8, 'name' => 'Primary Device Usage', 'created_at' => now(), 'updated_at' => now()], // New/Refined
        ]);
    }
}
