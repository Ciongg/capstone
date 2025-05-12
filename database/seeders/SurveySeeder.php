<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use App\Models\User;
use App\Models\Tag;
use App\Models\TagCategory;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all users with the 'researcher' type
        $researcherUsers = User::where('type', 'researcher')->get();

        if ($researcherUsers->isEmpty()) {
            $this->command->info('No researcher users found. Creating a default researcher to assign surveys.');
            // Optionally, create a default researcher user if none exist
            $defaultResearcher = User::factory()->create([
                'name' => 'Default Researcher',
                'email' => 'researcher@example.com',
                'password' => bcrypt('password'),
                'type' => 'researcher', // Ensure this user is a researcher
            ]);
            $researcherUsers = collect([$defaultResearcher]);
        }
        
        // Create or ensure tag categories and tags exist
        $this->ensureTagsExist();
        
        // Get all available tags
        $availableTags = Tag::all();
        
        // Make sure storage/app/public/surveys directory exists (create if not)
        if (!File::isDirectory(storage_path('app/public/surveys'))) {
            File::makeDirectory(storage_path('app/public/surveys'), 0755, true);
        }
        
        // Get available image files from storage
        $imageFiles = Storage::disk('public')->files('surveys');
        
        // Create 10 random surveys
        for ($i = 0; $i < 10; $i++) {
            $surveyStatus = 'published';
            $surveyType = $faker->randomElement(['basic', 'advanced']);
            
            // Set fixed points based on survey type
            $points = ($surveyType === 'basic') ? 10 : 30;
            
            // Select a random researcher user
            if ($researcherUsers->isEmpty()) {
                $this->command->error('Cannot create surveys as no researcher users are available.');
                return; // Stop if no researchers are available after the check
            }
            $user = $researcherUsers->random();
            
            // Create the survey
            $survey = Survey::create([
                'user_id' => $user->id,
                'title' => $faker->sentence(4),
                'description' => $faker->paragraph(),
                'status' => $surveyStatus,
                'type' => $surveyType,
                'points' => $points,
                'target_respondents' => $faker->numberBetween(30, 100),
                'start_date' => $faker->dateTimeBetween('-1 month', '+1 week'),
                'end_date' => $faker->dateTimeBetween('+1 week', '+3 months'),
                'points_allocated' => $points, // Same as points, no random multiplier
                'image_path' => !empty($imageFiles) ? $imageFiles[array_rand($imageFiles)] : null,
            ]);
            
            // Assign random tags to this survey (between 3 and 5 tags)
            $tagCount = $faker->numberBetween(3, 5);
            $randomTags = $availableTags->random($tagCount);
            
            foreach ($randomTags as $tag) {
                DB::table('survey_tag')->insert([
                    'tag_id' => $tag->id,
                    'survey_id' => $survey->id,
                    'tag_name' => $tag->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Create exactly 3 pages with specific question types for each
            $pageStructure = [
                1 => ['multiple_choice', 'radio', 'likert'],
                2 => ['essay', 'short_text'],
                3 => ['rating', 'date']
            ];
            
            foreach ($pageStructure as $pageNumber => $questionTypes) {
                $page = SurveyPage::create([
                    'survey_id' => $survey->id,
                    'page_number' => $pageNumber,
                    'title' => $faker->sentence(3),
                ]);
                
                // Create questions with specified types for this page
                foreach ($questionTypes as $qOrder => $questionType) {
                    $questionData = [
                        'survey_id' => $survey->id,
                        'survey_page_id' => $page->id,
                        'question_text' => $faker->sentence(6, true) . '?',
                        'question_type' => $questionType,
                        'order' => $qOrder + 1,
                        'required' => true,
                    ];
                    
                    // For multiple choice questions, add limit conditions with 20% probability
                    if ($questionType === 'multiple_choice' && $faker->boolean(20)) {
                        $questionData['limit_condition'] = $faker->randomElement(['at_most', 'equal_to']);
                        $questionData['max_answers'] = $faker->numberBetween(1, 5);
                    }
                    
                    // For rating questions, add stars
                    if ($questionType === 'rating') {
                        $questionData['stars'] = $faker->randomElement([5, 7, 10]);
                    }
                    
                    // For Likert questions, add scales
                    if ($questionType === 'likert') {
                        $questionData['likert_columns'] = json_encode([
                            'Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'
                        ]);
                        
                        $likertRows = [];
                        $rowCount = $faker->numberBetween(2, 5);
                        for ($r = 0; $r < $rowCount; $r++) {
                            $likertRows[] = $faker->sentence(4);
                        }
                        $questionData['likert_rows'] = json_encode($likertRows);
                    }
                    
                    $question = SurveyQuestion::create($questionData);
                    
                    // For question types that need choices
                    if (in_array($questionType, ['multiple_choice', 'radio'])) {
                        $numChoices = $faker->numberBetween(2, 6);
                        for ($c = 1; $c <= $numChoices; $c++) {
                            SurveyChoice::create([
                                'survey_question_id' => $question->id,
                                'choice_text' => $faker->words(rand(1, 4), true),
                                'order' => $c,
                                'is_other' => ($c === $numChoices && $faker->boolean(20)), // 20% chance last option is "Other"
                            ]);
                        }
                    }
                }
            }
        }
        
        $this->command->info('Created 10 surveys with standardized pages and question types!');
    }

    /**
     * Ensure tags and tag categories exist
     */
    private function ensureTagsExist()
    {
        // Check if tags already exist
        if (Tag::count() > 0) {
            return;
        }
        
        // Create tag categories
        $categories = [
            'Age Group' => ['18-24', '25-34', '35-44', '45-54', '55+'],
            'Gender' => ['Male', 'Female', 'Non-binary', 'Prefer not to say'],
            'Education' => ['High School', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD'],
            'Employment' => ['Student', 'Employed', 'Self-employed', 'Unemployed', 'Retired'],
            'Interests' => ['Technology', 'Health', 'Education', 'Entertainment', 'Sports', 'Finance', 'Food']
        ];
        
        foreach ($categories as $categoryName => $tagNames) {
            $category = TagCategory::create(['name' => $categoryName]);
            
            foreach ($tagNames as $tagName) {
                Tag::create([
                    'tag_category_id' => $category->id,
                    'name' => $tagName
                ]);
            }
        }
    }
}
