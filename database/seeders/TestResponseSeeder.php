<?php

namespace Database\Seeders;

use App\Models\Response;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get user ID 1 (Miguel Inciong)
        $userId = 7;
        
        // Set number of responses to create
        $responseCount = 50;
        
        // Get available surveys with IDs 1-10
        $surveyIds = Survey::where('id', '<=', 5)->pluck('id')->toArray();
        
        // If we don't have enough surveys, create dummy survey IDs
        if (empty($surveyIds)) {
            $surveyIds = range(1, 5);
        }
        
        // Create responses
        for ($i = 0; $i < $responseCount; $i++) {
            // Select a random survey
            $surveyId = $surveyIds[array_rand($surveyIds)];
            
            // Create response record
            $response = Response::create([
                'user_id' => $userId,
                'survey_id' => $surveyId,
                'reported' => false, // Not reported by default
            ]);
            
            // Find questions for this survey
            $questions = SurveyQuestion::where('survey_id', $surveyId)->limit(5)->get();
            
            // If no questions found, create dummy answer
            if ($questions->isEmpty()) {
                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => 1, // Dummy question ID
                    'answer' => 'Test answer for response ' . $response->id,
                ]);
                continue;
            }
            
            // Create 1-3 answers for this response
            $answerCount = rand(1, 3);
            $questionIds = $questions->pluck('id')->toArray();
            
            for ($j = 0; $j < $answerCount && $j < count($questionIds); $j++) {
                Answer::create([
                    'response_id' => $response->id,
                    'survey_question_id' => $questionIds[$j],
                    'answer' => 'Test answer ' . ($j + 1) . ' for response ' . $response->id,
                ]);
            }
        }
        
        $this->command->info("Created {$responseCount} test responses for user ID {$userId} (Miguel Inciong)");
    }
}
