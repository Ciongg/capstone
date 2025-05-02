<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     
    public function run(): void
    {
        $tags = [

            // Age Groups
            ['category' => 'Age Group', 'name' => '13 – 17 years old'],
            ['category' => 'Age Group', 'name' => '18 – 24 years old'],
            ['category' => 'Age Group', 'name' => '25 – 34 years old'],
            ['category' => 'Age Group', 'name' => '35 – 44 years old'],
            ['category' => 'Age Group', 'name' => '45 – 54 years old'],
            ['category' => 'Age Group', 'name' => '55 – 64 years old'],
            ['category' => 'Age Group', 'name' => '65+ years old'],

            // Gender & Identity
            ['category' => 'Gender & Identity', 'name' => 'Male'],
            ['category' => 'Gender & Identity', 'name' => 'Female'],
            ['category' => 'Gender & Identity', 'name' => 'Non-binary / LGBTQ+'],

            // Location
            ['category' => 'Location', 'name' => 'Metro Manila'],
            ['category' => 'Location', 'name' => 'Luzon (outside Metro Manila)'],
            ['category' => 'Location', 'name' => 'Visayas'],
            ['category' => 'Location', 'name' => 'Mindanao'],
            ['category' => 'Location', 'name' => 'OFWs'],
            ['category' => 'Location', 'name' => 'International'],

            // Education Level
            ['category' => 'Education Level', 'name' => 'High School Graduate / Senior High'],
            ['category' => 'Education Level', 'name' => 'Undergraduate'],
            ['category' => 'Education Level', 'name' => 'Bachelor’s Degree Holder'],
            ['category' => 'Education Level', 'name' => 'Postgraduate'],
            ['category' => 'Education Level', 'name' => 'Vocational / Technical Graduate'],
            ['category' => 'Education Level', 'name' => 'Other'],

            // Employment Status
            ['category' => 'Employment Status', 'name' => 'Employed'],
            ['category' => 'Employment Status', 'name' => 'Self-Employed / Entrepreneur'],
            ['category' => 'Employment Status', 'name' => 'Unemployed'],
            ['category' => 'Employment Status', 'name' => 'Student'],
            ['category' => 'Employment Status', 'name' => 'Retired / Senior Citizen'],

            // Industry / Work Field
            ['category' => 'Industry / Work Field', 'name' => 'Technology & IT'],
            ['category' => 'Industry / Work Field', 'name' => 'Healthcare & Medicine'],
            ['category' => 'Industry / Work Field', 'name' => 'Education & Research'],
            ['category' => 'Industry / Work Field', 'name' => 'Business & Finance'],
            ['category' => 'Industry / Work Field', 'name' => 'Marketing & Sales'],
            ['category' => 'Industry / Work Field', 'name' => 'Engineering & Architecture'],
            ['category' => 'Industry / Work Field', 'name' => 'Arts, Media & Design'],
            ['category' => 'Industry / Work Field', 'name' => 'Law & Public Administration'],
            ['category' => 'Industry / Work Field', 'name' => 'Agriculture & Environmental Sciences'],
            ['category' => 'Industry / Work Field', 'name' => 'Retail, Hospitality & Tourism'],
            ['category' => 'Industry / Work Field', 'name' => 'Transportation & Logistics'],
            ['category' => 'Industry / Work Field', 'name' => 'Others / Unspecified'],

            // Income Level
            ['category' => 'Income Level', 'name' => 'Low-income'],
            ['category' => 'Income Level', 'name' => 'Middle-income'],
            ['category' => 'Income Level', 'name' => 'Upper-middle income'],
            ['category' => 'Income Level', 'name' => 'High-income / Wealthy'],

            // Household / Living Situation
            ['category' => 'Household / Living Situation', 'name' => 'Living with parents / Family home'],
            ['category' => 'Household / Living Situation', 'name' => 'Living alone'],
            ['category' => 'Household / Living Situation', 'name' => 'Married / Living with spouse'],
            ['category' => 'Household / Living Situation', 'name' => 'With children'],
            ['category' => 'Household / Living Situation', 'name' => 'Shared living (roommates, dorms, etc.)'],

            // Tech Usage & Internet Behavior
            ['category' => 'Tech Usage & Internet Behavior', 'name' => 'Smartphone user only'],
            ['category' => 'Tech Usage & Internet Behavior', 'name' => 'Smartphone + Laptop/Desktop user'],
            ['category' => 'Tech Usage & Internet Behavior', 'name' => 'Heavy social media user'],
            ['category' => 'Tech Usage & Internet Behavior', 'name' => 'Occasional internet user'],

            // Interests & Lifestyle Preferences
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Gaming'],
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Fitness & Wellness'],
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Sustainability & Environment'],
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Food & Beverages'],
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Entertainment'],
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Shopping & E-commerce'],
            ['category' => 'Interests & Lifestyle Preferences', 'name' => 'Travel'],
        ];

        foreach ($tags as $tag) {
            DB::table('tags')->insert([
                'tag_category_id' => DB::table('tag_categories')->where('name', $tag['category'])->value('id'),
                'name' => $tag['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
    }
}
