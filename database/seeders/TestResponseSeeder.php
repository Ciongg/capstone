<?php

namespace Database\Seeders;

use App\Models\Response;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TestResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get user ID (Miguel Inciong)
        $userId = 7;
        
        // Set number of responses to create per survey
        $responsesPerSurvey = 30;
        
        // Get available surveys
        $surveys = Survey::all();
        
        if ($surveys->isEmpty()) {
            $this->command->error('No surveys found. Please run SurveySeeder first.');
            return;
        }
        
        foreach ($surveys as $survey) {
            $this->command->info("Creating {$responsesPerSurvey} responses for survey: {$survey->title}");
            
            // Get all questions for this survey with their choices
            $questions = SurveyQuestion::where('survey_id', $survey->id)
                ->with('choices')
                ->get();
                
            if ($questions->isEmpty()) {
                $this->command->warn("No questions found for survey ID {$survey->id}. Skipping.");
                continue;
            }
            
            // Create responses for this survey
            for ($i = 0; $i < $responsesPerSurvey; $i++) {
                // Create response record
                $response = Response::create([
                    'user_id' => $userId,
                    'survey_id' => $survey->id,
                    'reported' => false,
                ]);
                
                // Answer each question in this survey
                foreach ($questions as $question) {
                    switch ($question->question_type) {
                        case 'multiple_choice':
                            $this->createMultipleChoiceAnswer($response, $question, $faker);
                            break;
                            
                        case 'radio':
                            $this->createRadioAnswer($response, $question, $faker);
                            break;
                            
                        case 'likert':
                            $this->createLikertAnswer($response, $question, $faker);
                            break;
                            
                        case 'essay':
                            $this->createEssayAnswer($response, $question, $faker);
                            break;
                            
                        case 'short_text':
                            $this->createShortTextAnswer($response, $question, $faker);
                            break;
                            
                        case 'rating':
                            $this->createRatingAnswer($response, $question, $faker);
                            break;
                            
                        case 'date':
                            $this->createDateAnswer($response, $question, $faker);
                            break;
                            
                        default:
                            // For any other question type, create a generic answer
                            Answer::create([
                                'response_id' => $response->id,
                                'survey_question_id' => $question->id,
                                'answer' => 'Default answer for ' . $question->question_type,
                            ]);
                    }
                }
            }
        }
        
        $this->command->info("Created responses for all surveys successfully!");
    }
    
    /**
     * Create answer for multiple choice question
     */
    private function createMultipleChoiceAnswer($response, $question, $faker)
    {
        $choices = $question->choices;
        
        if ($choices->isEmpty()) {
            Answer::create([
                'response_id' => $response->id,
                'survey_question_id' => $question->id,
                'answer' => 'No choices available',
            ]);
            return;
        }
        
        // Select 1-3 random choices, limited by max_answers if set
        $maxSelections = $question->max_answers ?? $faker->numberBetween(1, min(3, $choices->count()));
        $selectedCount = $faker->numberBetween(1, $maxSelections);
        
        // Get random choices
        $selectedChoices = $choices->random($selectedCount);
        $selectedIds = $selectedChoices->pluck('id')->toArray();
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => json_encode($selectedIds),
            // Add "other" text for approximately 10% of responses that include an "other" choice
            'other_text' => $selectedChoices->where('is_other', true)->isNotEmpty() && $faker->boolean(10) 
                ? $faker->sentence(4) 
                : null,
        ]);
    }
    
    /**
     * Create answer for radio question
     */
    private function createRadioAnswer($response, $question, $faker)
    {
        $choices = $question->choices;
        
        if ($choices->isEmpty()) {
            Answer::create([
                'response_id' => $response->id,
                'survey_question_id' => $question->id,
                'answer' => 'No choices available',
            ]);
            return;
        }
        
        // Select one random choice
        $selectedChoice = $choices->random();
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => (string)$selectedChoice->id,
            // Add "other" text if the selected choice is an "other" option (20% chance)
            'other_text' => $selectedChoice->is_other && $faker->boolean(20)
                ? $faker->sentence(4)
                : null,
        ]);
    }
    
    /**
     * Create answer for likert scale question
     */
    private function createLikertAnswer($response, $question, $faker)
    {
        // Get likert rows and columns
        $rows = json_decode($question->likert_rows ?? '[]', true);
        $columns = json_decode($question->likert_columns ?? '[]', true);
        
        if (empty($rows) || empty($columns)) {
            Answer::create([
                'response_id' => $response->id,
                'survey_question_id' => $question->id,
                'answer' => 'Invalid likert configuration',
            ]);
            return;
        }
        
        // Generate response for each row
        $likertResponses = [];
        foreach (array_keys($rows) as $rowIndex) {
            // For each row, select a random column
            $likertResponses[$rowIndex] = $faker->numberBetween(0, count($columns) - 1);
        }
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => json_encode($likertResponses),
        ]);
    }
    
    /**
     * Create answer for essay question
     */
    private function createEssayAnswer($response, $question, $faker)
    {
        // Map of keywords in questions to appropriate response themes
        $responseTemplates = [
            'challenges' => [
                "The biggest challenge I've faced is balancing work and study. The university could improve by offering more evening classes and flexible deadlines.",
                "Finding affordable housing near campus has been difficult. The university could partner with local housing providers for student discounts.",
                "As an international student, language barriers were initially challenging. More accessible language support services would be beneficial.",
                "Mental health struggles affected my academic performance. Expanding counseling services and reducing stigma would help students seeking support."
            ],
            'improve' => [
                "The curriculum could be improved by incorporating more practical, hands-on experiences alongside theoretical knowledge.",
                "More industry partnerships would enhance the relevance of our coursework to real-world applications.",
                "Smaller class sizes would allow for more meaningful discussions and personalized feedback from professors.",
                "Integrating more technology and digital skills training across all programs would better prepare students for the modern workforce."
            ],
            'learning' => [
                "My group research project on sustainable urban development was meaningful because it connected classroom concepts to real community issues.",
                "The guest lecture series featuring industry professionals gave me valuable insights into potential career paths and practical applications.",
                "Working as a teaching assistant allowed me to deepen my understanding of the subject while developing leadership and communication skills.",
                "The study abroad program broadened my perspective and helped me understand global dimensions of my field I wouldn't have grasped otherwise."
            ],
            'careers' => [
                "The university should establish more internship partnerships with local and national companies to provide students with relevant work experience.",
                "Career counseling services could be more specialized by field of study rather than offering general advice to all students.",
                "Alumni networking events and mentorship programs would create valuable connections between current students and professionals.",
                "More workshops on emerging industry trends and practical skills would better prepare students for evolving job markets."
            ],
            'facilities' => [
                "The library needs extended hours, especially during exam periods, and more quiet study spaces to accommodate student needs.",
                "Computer labs should be updated with industry-standard software relevant to each program of study.",
                "More accessible study spaces with reliable Wi-Fi, charging stations, and comfortable seating would enhance productivity.",
                "The science labs need modernization to reflect current research practices and provide students with experience using up-to-date equipment."
            ]
        ];
        
        // Default responses if no match found
        $defaultResponses = [
            "I've had a generally positive experience with this aspect of university life, though there's always room for improvement.",
            "This area needs significant attention from university administration to better serve student needs.",
            "My experiences have been mixed, with some positive elements but also clear areas where changes would be beneficial.",
            "Compared to other universities I'm familiar with, this institution performs relatively well in this regard."
        ];
        
        // Select appropriate response based on question content
        $selectedResponses = $defaultResponses;
        
        foreach ($responseTemplates as $keyword => $responses) {
            if (stripos($question->question_text, $keyword) !== false) {
                $selectedResponses = $responses;
                break;
            }
        }
        
        // Select a random response from the appropriate category
        $essayResponse = $selectedResponses[array_rand($selectedResponses)];
        
        // Add some variability (20% chance of adding an extra sentence)
        if ($faker->boolean(20)) {
            $essayResponse .= " " . $faker->sentence(8, true);
        }
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => $essayResponse,
        ]);
    }
    
    /**
     * Create answer for short text question
     */
    private function createShortTextAnswer($response, $question, $faker)
    {
        // Map keywords to appropriate short responses
        $shortAnswerMap = [
            'major' => ['Computer Science', 'Psychology', 'Business Administration', 'Nursing', 'Engineering', 'Biology', 'Communications', 'Education'],
            'word' => ['Challenging', 'Rewarding', 'Enlightening', 'Stressful', 'Valuable', 'Engaging', 'Overwhelming', 'Transformative'],
            'skill' => ['Critical thinking', 'Time management', 'Research methodology', 'Public speaking', 'Data analysis', 'Technical writing', 'Leadership', 'Problem-solving'],
            'well' => ['Supportive professors', 'Career preparation', 'Research opportunities', 'Community atmosphere', 'Diverse perspectives', 'Academic resources', 'Networking events'],
            'challenge' => ['Time management', 'Financial pressures', 'Work-life balance', 'Difficult coursework', 'Social isolation', 'Mental health management', 'Housing issues']
        ];
        
        // Default short answers
        $defaultShortAnswers = ['Good', 'Needs improvement', 'Satisfactory', 'Excellent', 'Variable', 'Adequate', 'Insufficient', 'Outstanding'];
        
        // Select appropriate response based on question content
        $selectedAnswers = $defaultShortAnswers;
        
        foreach ($shortAnswerMap as $keyword => $answers) {
            if (stripos($question->question_text, $keyword) !== false) {
                $selectedAnswers = $answers;
                break;
            }
        }
        
        // Select a random response from the appropriate category
        $shortAnswer = $selectedAnswers[array_rand($selectedAnswers)];
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => $shortAnswer,
        ]);
    }
    
    /**
     * Create answer for rating question
     */
    private function createRatingAnswer($response, $question, $faker)
    {
        // Get the number of stars for this question (default to 5 if not specified)
        $stars = $question->stars ?? 5;
        
        // Create a rating biased toward the middle and high values (bell curve with slight positive skew)
        $weights = [];
        for ($i = 1; $i <= $stars; $i++) {
            // Weight formula creates higher probability for middle-high values
            $weight = 100 - abs(($i - ($stars * 0.7)) * 15);
            $weights[$i] = max(5, $weight); // Ensure minimum 5% chance for any value
        }
        
        // Select rating based on weights
        $rating = $this->weightedRandom($weights, $faker);
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => (string)$rating,
        ]);
    }
    
    /**
     * Create answer for date question
     */
    private function createDateAnswer($response, $question, $faker)
    {
        // Determine appropriate date range based on question
        $dateRange = ['-2 years', 'now'];
        
        if (stripos($question->question_text, 'enroll') !== false) {
            $dateRange = ['-4 years', '-1 month'];
        } elseif (stripos($question->question_text, 'complete') !== false || 
                  stripos($question->question_text, 'expect') !== false) {
            $dateRange = ['+3 months', '+2 years'];
        } elseif (stripos($question->question_text, 'advisor') !== false) {
            $dateRange = ['-6 months', '+1 week'];
        } elseif (stripos($question->question_text, 'career') !== false) {
            $dateRange = ['-1 year', '+2 months'];
        } elseif (stripos($question->question_text, 'event') !== false) {
            $dateRange = ['-2 months', '+1 month'];
        }
        
        // Generate a date in the appropriate range
        $date = $faker->dateTimeBetween($dateRange[0], $dateRange[1])->format('Y-m-d');
        
        Answer::create([
            'response_id' => $response->id,
            'survey_question_id' => $question->id,
            'answer' => $date,
        ]);
    }
    
    /**
     * Helper function to select a random element based on weights
     */
    private function weightedRandom($weights, $faker) 
    {
        $totalWeight = array_sum($weights);
        $randomValue = $faker->numberBetween(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($weights as $value => $weight) {
            $currentWeight += $weight;
            if ($randomValue <= $currentWeight) {
                return $value;
            }
        }
        
        return array_key_first($weights); // Fallback
    }
}
