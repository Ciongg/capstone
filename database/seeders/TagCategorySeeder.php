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
        DB::table('tag_categories')->insert([
            ['id' => 1, 'name' => 'Age Group', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Gender & Identity', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Location', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Education Level', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Employment Status', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Industry / Work Field', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'Income Level', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'Household / Living Situation', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'Tech Usage & Internet Behavior', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'Interests & Lifestyle Preferences', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
    }
}
