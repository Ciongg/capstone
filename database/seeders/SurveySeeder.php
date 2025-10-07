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
use App\Models\SurveyTopic;
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
        
        // Predefined academic survey titles
        $surveyTitles = [
            'Student Satisfaction and Campus Experience Survey',
            'Academic Workload and Stress Assessment',
            'Career Readiness and Professional Development Survey',
            'Learning Environment and Teaching Methods Evaluation',
            'Technology in Education: Usage and Preferences',
            'Campus Facilities and Resources Satisfaction Survey',
            'Student Health and Wellbeing Assessment',
            'Graduate Outcomes and Alumni Experience Survey',
            'University Services Evaluation Survey',
            'Academic Integrity and Ethics in Research Survey',
            'Online Learning Experience Assessment',
            'Course Materials and Resources Effectiveness Survey'
        ];
        
        // Predefined survey descriptions
        $surveyDescriptions = [
            'This survey aims to collect data on student satisfaction with various aspects of campus life and academic experiences. Your feedback will help us improve student services and campus facilities.',
            'The purpose of this survey is to assess student workload across different programs and identify factors contributing to academic stress. Results will inform academic policy improvements.',
            'This survey evaluates how well our institution prepares students for their future careers. Your responses will help enhance career services and professional development opportunities.',
            'Help us understand the effectiveness of different teaching methods and learning environments. Your feedback will contribute to enhancing educational quality and instructional approaches.',
            'This survey examines how technology is integrated into your learning experience. Results will guide technology investments and digital learning strategy development.',
            'We seek your feedback on campus facilities, library resources, and study spaces. This information will help prioritize facility improvements and resource allocations.',
            'This confidential survey assesses student health and wellbeing to help us develop better support services and resources for a healthier campus community.',
            'For recent graduates: share your experiences transitioning to professional life and how your education prepared you for your career path.',
            'Help us evaluate the quality and accessibility of university services including advising, financial aid, registration, and technical support.',
            'This survey explores understanding of academic integrity policies and ethical research practices among students and faculty.',
            'Evaluate your experience with online and hybrid learning models. Your input will help improve digital course delivery and remote learning support.',
            'This assessment examines the quality, relevance, and accessibility of course materials and learning resources across programs.'
        ];
        
        // Predefined page titles
        $pageTitles = [
            'Demographic Information',
            'Academic Experience',
            'Campus Life Evaluation',
            'Student Services',
            'Technology and Resources',
            'General Feedback',
            'Health and Wellbeing',
            'Institutional Assessment',
            'Future Directions',
            'Career Development'
        ];
        
        // Predefined question templates by type
        $questionTemplates = [
            'multiple_choice' => [
                'Which of the following campus resources have you utilized this semester? (Select all that apply)',
                'Which factors influenced your decision to attend this university? (Select all that apply)',
                'Which of the following academic support services would you like to see expanded? (Select all that apply)',
                'Which of the following technology tools do you regularly use for your coursework? (Select all that apply)',
                'Which campus facilities do you use most frequently? (Select all that apply)'
            ],
            'radio' => [
                'How often do you visit the university library?',
                'What is your primary mode of transportation to campus?',
                'Which learning format do you prefer for most of your courses?',
                'How would you rate your overall satisfaction with your academic program?',
                'How frequently do you participate in extracurricular activities?'
            ],
            'likert' => [
                'Please rate your agreement with the following statements about faculty engagement:',
                'Please indicate your satisfaction level with the following campus services:',
                'Rate your agreement with these statements about academic advising:',
                'Please rate these aspects of course delivery and instruction:',
                'Indicate your satisfaction with these aspects of university communication:'
            ],
            'essay' => [
                'Please describe any challenges you have faced during your academic journey and how the university could better support students in similar situations.',
                'What changes would you suggest to improve the quality of education in your program?',
                'Describe your most meaningful learning experience at this institution and why it had an impact on you.',
                'What are your thoughts on how the university could better prepare students for their future careers?',
                'Please provide feedback on how campus facilities and resources could be improved to better support your academic success.'
            ],
            'short_text' => [
                'What is your current major/program of study?',
                'What is one word that describes your university experience so far?',
                'What specific skill have you developed most during your studies?',
                'Name one thing the university does exceptionally well.',
                'What is the biggest challenge you face as a student?'
            ],
            'rating' => [
                'How would you rate the quality of academic advising you have received?',
                'Rate the accessibility of professors outside of class hours:',
                'How would you rate the value of your education relative to its cost?',
                'Rate the quality of technical support services:',
                'How would you rate campus food services?'
            ],
            'date' => [
                'When did you first enroll at this university?',
                'When do you expect to complete your current program?',
                'When did you last meet with your academic advisor?',
                'When did you last use university career services?',
                'When did you last participate in a university-sponsored event?'
            ]
        ];
        
        // Predefined choices for multiple choice and radio questions
        $choiceOptions = [
            'library' => ['Library', 'Writing Center', 'Tutoring Services', 'Academic Advising', 'Career Services', 'Counseling Services', 'Student Health Center', 'None of the above'],
            'decision' => ['Academic reputation', 'Program offerings', 'Financial aid/scholarships', 'Location', 'Campus facilities', 'Recommendation from family/friends', 'Cost of attendance', 'Career opportunities'],
            'support' => ['Tutoring', 'Writing assistance', 'Research support', 'Technical help', 'Math center', 'Language learning', 'Academic coaching', 'Study skills workshops'],
            'tech_tools' => ['Learning Management System', 'Online research databases', 'E-books/digital textbooks', 'Video conferencing', 'Cloud storage', 'Note-taking apps', 'Study group platforms', 'AI writing assistants'],
            'facilities' => ['Library', 'Student center', 'Recreational facilities', 'Computer labs', 'Study rooms', 'Dining halls', 'Science labs', 'Art studios'],
            'frequency' => ['Daily', 'Several times a week', 'Once a week', 'Several times a month', 'Monthly', 'Once a semester', 'Never'],
            'transportation' => ['Walking', 'Biking', 'Public transportation', 'Personal vehicle', 'Carpooling', 'University shuttle', 'Other'],
            'format' => ['Fully in-person', 'Hybrid (mix of in-person and online)', 'Mostly online with some in-person components', 'Fully online synchronous', 'Fully online asynchronous'],
            'satisfaction' => ['Very satisfied', 'Satisfied', 'Neutral', 'Unsatisfied', 'Very unsatisfied'],
            'participation' => ['Very frequently', 'Frequently', 'Occasionally', 'Rarely', 'Never']
        ];
        
        // Predefined Likert scale rows
        $likertRowSets = [
            'faculty' => [
                'Professors are knowledgeable in their subject areas',
                'Faculty are accessible outside of class hours',
                'Instructors provide helpful feedback on assignments',
                'Professors effectively engage students during class',
                'Faculty incorporate current research into their teaching'
            ],
            'campus_services' => [
                'Registration process efficiency',
                'Financial aid services and support',
                'Academic advising quality',
                'Campus dining options and quality',
                'Campus safety and security measures'
            ],
            'advising' => [
                'My advisor is knowledgeable about degree requirements',
                'My advisor is accessible when I need assistance',
                'Advising sessions are helpful for course planning',
                'My advisor helps me understand career options',
                'My advisor refers me to appropriate resources when needed'
            ],
            'instruction' => [
                'Course content is relevant to my educational goals',
                'Instructional methods effectively support learning',
                'Course materials are engaging and up-to-date',
                'Assessment methods fairly measure learning',
                'Course workload is appropriate for credit hours'
            ],
            'communication' => [
                'Timeliness of important announcements',
                'Clarity of degree requirements and policies',
                'Responsiveness to student inquiries',
                'Effectiveness of the university website',
                'Communication about campus events and resources'
            ]
        ];
        
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
        
        // Get all tag categories and their tags
        $tagCategories = TagCategory::with('tags')->get();
        
        if ($tagCategories->isEmpty()) {
            $this->command->warn('No tag categories found. Please run TagCategorySeeder first.');
            return;
        }
        
        // Flatten the collection of tags
        $availableTags = $tagCategories->flatMap(function ($category) {
            return $category->tags;
        });
        
        // Get all available survey topics - Remove conditional seeding
        $surveyTopics = SurveyTopic::all();
        
        if ($surveyTopics->isEmpty()) {
            // Just report error instead of calling the seeder again
            $this->command->warn('No survey topics found. Please run SurveyTopicSeeder first.');
            return;
        }
        
        // Make sure storage/app/public/surveys directory exists (create if not)
        $surveysStoragePath = storage_path('app/public/surveys');
        if (!File::isDirectory($surveysStoragePath)) {
            File::makeDirectory($surveysStoragePath, 0755, true);
        }

        // Get all images from storage/app/public/surveys
        $imageFiles = [];
        $storageFiles = File::files($surveysStoragePath);
        foreach ($storageFiles as $file) {
            $imageFiles[] = 'surveys/' . $file->getFilename(); // Path relative to 'public' disk
        }
        if (empty($imageFiles)) {
            $this->command->warn('No images found in storage/app/public/surveys. No images will be assigned to surveys.');
        }
        
        // Create 10 random surveys
        for ($i = 0; $i < 40; $i++) {
            $surveyStatus = 'published';
            // Change to 30% probability for advanced surveys, 70% for basic
            $surveyType = $faker->boolean(30) ? 'advanced' : 'basic';
            
            // Set fixed points based on survey type
            $points = ($surveyType === 'basic') ? 10 : 20;
            
            // Select a random researcher user
            if ($researcherUsers->isEmpty()) {
                $this->command->error('Cannot create surveys as no researcher users are available.');
                return; // Stop if no researchers are available after the check
            }
            $user = $researcherUsers->random();
            
            // Create the survey with structured content
            $survey = Survey::create([
                'user_id' => $user->id,
                'title' => $surveyTitles[$i % count($surveyTitles)],  // Use predefined titles
                'description' => $surveyDescriptions[$i % count($surveyDescriptions)],  // Use predefined descriptions
                'status' => $surveyStatus,
                'type' => $surveyType,
                'survey_topic_id' => $surveyTopics->random()->id,
                'target_respondents' => $faker->numberBetween(30, 100),
                'start_date' => $faker->dateTimeBetween('-1 month', '+1 week'),
                'end_date' => $faker->dateTimeBetween('+1 week', '+3 months'),
                'points_allocated' => $points,
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
                    'title' => $pageTitles[($pageNumber + $i) % count($pageTitles)],  // Use predefined page titles
                ]);
                
                // Create questions with specified types for this page
                foreach ($questionTypes as $qOrder => $questionType) {
                    // Get predefined question for this type
                    $questions = $questionTemplates[$questionType];
                    $questionText = $questions[$qOrder % count($questions)];
                    
                    $questionData = [
                        'survey_id' => $survey->id,
                        'survey_page_id' => $page->id,
                        'question_text' => $questionText,
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
                    
                    // For Likert questions, add structured scales
                    if ($questionType === 'likert') {
                        $questionData['likert_columns'] = json_encode([
                            'Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'
                        ]);
                        
                        // Get predefined Likert rows based on question topic
                        $likertKeys = array_keys($likertRowSets);
                        $selectedKey = $likertKeys[$qOrder % count($likertKeys)];
                        $questionData['likert_rows'] = json_encode($likertRowSets[$selectedKey]);
                    }
                    
                    $question = SurveyQuestion::create($questionData);
                    
                    // For question types that need choices, use predefined choices
                    if (in_array($questionType, ['multiple_choice', 'radio'])) {
                        // Determine which choice set to use based on the question
                        $choiceSetKey = 'satisfaction'; // Default choice set
                        
                        if (stripos($questionText, 'campus resources') !== false) {
                            $choiceSetKey = 'library';
                        } elseif (stripos($questionText, 'decision') !== false) {
                            $choiceSetKey = 'decision';
                        } elseif (stripos($questionText, 'academic support') !== false) {
                            $choiceSetKey = 'support';
                        } elseif (stripos($questionText, 'technology') !== false) {
                            $choiceSetKey = 'tech_tools';
                        } elseif (stripos($questionText, 'facilities') !== false) {
                            $choiceSetKey = 'facilities';
                        } elseif (stripos($questionText, 'often') !== false || stripos($questionText, 'frequently') !== false) {
                            $choiceSetKey = 'frequency';
                        } elseif (stripos($questionText, 'transportation') !== false) {
                            $choiceSetKey = 'transportation';
                        } elseif (stripos($questionText, 'format') !== false) {
                            $choiceSetKey = 'format';
                        } elseif (stripos($questionText, 'satisfaction') !== false) {
                            $choiceSetKey = 'satisfaction';
                        } elseif (stripos($questionText, 'participate') !== false) {
                            $choiceSetKey = 'participation';
                        }
                        
                        $choiceSet = $choiceOptions[$choiceSetKey];
                        
                        // If multiple choice, use all options; if radio, use a subset
                        $choices = ($questionType === 'multiple_choice') ? $choiceSet : array_slice($choiceSet, 0, 5);
                        
                        foreach ($choices as $index => $choiceText) {
                            SurveyChoice::create([
                                'survey_question_id' => $question->id,
                                'choice_text' => $choiceText,
                                'order' => $index + 1,
                                'is_other' => ($index == count($choices) - 1 && $questionType === 'multiple_choice' && $faker->boolean(20)),
                            ]);
                        }
                    }
                }
            }
        }
        
        $this->command->info('Created x surveys with structured academic content!');
    }
}
          