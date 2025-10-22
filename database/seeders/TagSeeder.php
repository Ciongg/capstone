<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TagCategory; // Use the model for easier ID lookup

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tags first
        DB::table('tags')->delete();

        // Fetch categories into an associative array for easy lookup
        $categories = TagCategory::pluck('id', 'name');

        $tags = [
            //age
            ['category' => 'Age Group', 'name' => '18 - 24'],
            ['category' => 'Age Group', 'name' => '25 - 34'],
            ['category' => 'Age Group', 'name' => '35 - 44'],
            ['category' => 'Age Group', 'name' => '45 - 54'],
            ['category' => 'Age Group', 'name' => '55 - 59'],
            ['category' => 'Age Group', 'name' => '60+'],

            // Gender (Simplified)
            ['category' => 'Gender', 'name' => 'Male'],
            ['category' => 'Gender', 'name' => 'Female'],
            ['category' => 'Gender', 'name' => 'LGBTQ+'],
            ['category' => 'Gender', 'name' => 'Prefer not to say / Other'],

            // Location (Region) (Simplified)
            ['category' => 'Location (Region)', 'name' => 'Metro Manila'],
            ['category' => 'Location (Region)', 'name' => 'North Luzon'],
            ['category' => 'Location (Region)', 'name' => 'South Luzon'],
            ['category' => 'Location (Region)', 'name' => 'Visayas'],
            ['category' => 'Location (Region)', 'name' => 'Mindanao'],

            // Education Level (Simplified)
            ['category' => 'Education Level', 'name' => 'High School or less'],
            ['category' => 'Education Level', 'name' => 'Some College / Vocational'],
            ['category' => 'Education Level', 'name' => 'Bachelor’s Degree'],
            ['category' => 'Education Level', 'name' => 'Postgraduate Degree'],

            // Employment Status (Slightly adjusted wording)
            ['category' => 'Employment Status', 'name' => 'Employed (Full-time/Part-time)'],
            ['category' => 'Employment Status', 'name' => 'Self-Employed / Business Owner'],
            ['category' => 'Employment Status', 'name' => 'Student'],
            ['category' => 'Employment Status', 'name' => 'Unemployed / Looking for work'],
            ['category' => 'Employment Status', 'name' => 'Retired / Not in labor force'],

            // Household Income (Est.) (Example ranges, adjust as needed)
            ['category' => 'Household Income (Est.)', 'name' => 'Below PHP 15,000 / month'],
            ['category' => 'Household Income (Est.)', 'name' => 'PHP 15,000 - 49,999 / month'],
            ['category' => 'Household Income (Est.)', 'name' => 'PHP 50,000 - 99,999 / month'],
            ['category' => 'Household Income (Est.)', 'name' => 'PHP 100,000+ / month'],
            ['category' => 'Household Income (Est.)', 'name' => 'Prefer not to say'],

            // Household Composition (Simplified)
            ['category' => 'Household Composition', 'name' => 'Living Alone'],
            ['category' => 'Household Composition', 'name' => 'Living with Partner/Spouse'],
            ['category' => 'Household Composition', 'name' => 'Living with Parents/Relatives'],
            ['category' => 'Household Composition', 'name' => 'Living with Children (under 18)'],
            ['category' => 'Household Composition', 'name' => 'Shared Living (Roommates, etc.)'],

            // Primary Device Usage
            ['category' => 'Primary Device Usage', 'name' => 'Primarily Smartphone'],
            ['category' => 'Primary Device Usage', 'name' => 'Primarily Computer (Laptop/Desktop)'],
            ['category' => 'Primary Device Usage', 'name' => 'Both Smartphone and Computer Regularly'],
        ];

        $insertData = [];
        foreach ($tags as $tag) {
            // Check if category exists before trying to access its ID
            if (isset($categories[$tag['category']])) {
                $insertData[] = [
                    'tag_category_id' => $categories[$tag['category']],
                    'name' => $tag['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // Optionally log a warning if a category name doesn't match
                // Log::warning("Tag category '{$tag['category']}' not found for tag '{$tag['name']}'.");
            }
        }

        // Insert all tags in one go
        DB::table('tags')->insert($insertData);
    }
}
