<?php

namespace App\Livewire\Surveys\FormBuilder\Templates;

use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;

class AcademicResearchTemplate
{
    public static function createTemplate(Survey $survey)
    {
        $pages = [
            1 => [
                'title' => 'Informed Consent and Data Privacy',
                'subtitle' => 'Please read and agree to participate in this research study',
                'questions' => [
                    ['type' => 'radio', 'text' => 'INFORMED CONSENT FOR PARTICIPATION IN RESEARCH

Purpose of the Study:
This research study aims to collect data for academic purposes. Your participation is voluntary and will contribute to scientific knowledge in this field.

Data Collection and Use:
- Your responses will be collected anonymously
- Data will be used solely for research purposes
- Results may be published in academic journals or presentations
- Individual responses will remain confidential

Your Rights:
- Participation is completely voluntary
- You may withdraw at any time without penalty
- You have the right to ask questions about the research
- Your personal information will be protected according to data privacy laws

Data Privacy Act Compliance:
In accordance with the Data Privacy Act, we inform you that:
- Your personal data will be processed lawfully and fairly
- Data will be collected for specified, explicit, and legitimate purposes
- We will implement appropriate security measures to protect your data
- You have rights regarding your personal data including access, correction, and deletion

Contact Information:
If you have questions about this research, please contact the researcher through the survey platform.

By selecting "Yes, I agree to participate" below, you acknowledge that:
- You have read and understood this information
- You voluntarily agree to participate in this research
- You understand your rights regarding data privacy
- You consent to the collection and use of your responses for research purposes

Do you agree to participate in this research study?', 'required' => true,
                     'choices' => ['Yes, I agree to participate']]
                ]
            ],
            2 => [
                'title' => 'Additional Demographic Information',
                'subtitle' => 'Reseach related information about the participants',
                'questions' => [
                    ['type' => 'radio', 'text' => 'Enter Question Title', 'required' => true,
                     'choices' => ['Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5', 'Option 6']],
                    ['type' => 'radio', 'text' => 'Enter Question Title', 'required' => false,
                     'choices' => ['Option 1', 'Option 2', 'Option 3', 'Option 4']],
                    ['type' => 'radio', 'text' => 'Enter Question Title', 'required' => true,
                     'choices' => ['Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5', 'Option 6']]
                ]
            ],
            3 => [
                'title' => 'Research Questions',
                'subtitle' => 'Main survey questions for data collection',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Enter Question Title', 'required' => true,
                     'rows' => [
                         'Statement 1',
                         'Statement 2',
                         'Statement 3',
                         'Statement 4',
                         'Statement 5'
                     ]],
                    ['type' => 'multiple_choice', 'text' => 'Enter Question Title', 'required' => true,
                     'choices' => ['Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5', 'Option 6']]
                ]
            ],
            4 => [
                'title' => 'Additional Feedback',
                'subtitle' => 'Open-ended questions for detailed responses',
                'questions' => [
                    ['type' => 'essay', 'text' => 'Enter Question Title', 'required' => false],
                    ['type' => 'short_text', 'text' => 'Enter Question Title', 'required' => false],
                    ['type' => 'rating', 'text' => 'Enter Question Title', 'required' => true, 'stars' => 5]
                ]
            ]
        ];

        foreach ($pages as $pageNumber => $pageData) {
            $page = SurveyPage::create([
                'survey_id' => $survey->id,
                'page_number' => $pageNumber,
                'order' => $pageNumber,
                'title' => $pageData['title'],
                'subtitle' => $pageData['subtitle'] ?? null,
            ]);

            foreach ($pageData['questions'] as $order => $questionData) {
                $question = SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'survey_page_id' => $page->id,
                    'question_text' => $questionData['text'],
                    'question_type' => $questionData['type'],
                    'order' => $order + 1,
                    'required' => $questionData['required'],
                    'stars' => $questionData['stars'] ?? null,
                    'likert_columns' => isset($questionData['rows']) ? json_encode([
                        'Agree', 'Neutral', 'Disagree'
                    ]) : null,
                    'likert_rows' => isset($questionData['rows']) ? json_encode($questionData['rows']) : null,
                ]);

                // Create choices for radio and multiple_choice questions
                if (isset($questionData['choices'])) {
                    foreach ($questionData['choices'] as $choiceOrder => $choiceText) {
                        SurveyChoice::create([
                            'survey_question_id' => $question->id,
                            'choice_text' => $choiceText,
                            'order' => $choiceOrder + 1,
                        ]);
                    }
                }
            }
        }
    }
}
