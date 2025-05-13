<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SurveyTopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            'Education',
            'Psychology',
            'Health',
            'Mental Health',
            'Technology',
            'Social Studies',
            'Business',
            'Employment',
            'Environment',
            'Governance',
            'Arts',
            'Marketing',
            'Economics',
        ];

        $now = Carbon::now();
        
        foreach ($topics as $topic) {
            DB::table('survey_topic')->insert([
                'name' => $topic,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
