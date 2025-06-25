<?php

namespace App\Livewire\Surveys\FormBuilder\Templates;

use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;

class ISO25010Template
{
    public static function createTemplate(Survey $survey)
    {
        $pages = [
            1 => [
                'title' => 'General Information',
                'subtitle' => 'Basic information about the software being evaluated',
                'questions' => [
                    ['type' => 'short_text', 'text' => 'Software/Product Name', 'required' => true],
                    ['type' => 'short_text', 'text' => 'Evaluator Name', 'required' => true],
                ]
            ],
            2 => [
                'title' => 'Functional Suitability',
                'subtitle' => 'Does the software do what it is supposed to do accurately and sufficiently?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following functional aspects:', 'required' => true,
                     'rows' => [
                         'The software provides all necessary functions',
                         'Functions work correctly and accurately',
                         'Software meets specified requirements',
                         'All features perform as expected',
                         'The software is suitable for its intended purpose'
                     ]]
                ]
            ],
            3 => [
                'title' => 'Performance Efficiency',
                'subtitle' => 'How fast and resource-friendly is the software under normal conditions?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following performance aspects:', 'required' => true,
                     'rows' => [
                         'Software responds quickly to user actions',
                         'System handles multiple tasks efficiently',
                         'Resource consumption is reasonable',
                         'Performance remains stable under load',
                         'Software scales well with increased usage'
                     ]]
                ]
            ],
            4 => [
                'title' => 'Compatibility',
                'subtitle' => 'Can the software operate in diverse environments with other products?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following compatibility aspects:', 'required' => true,
                     'rows' => [
                         'Software works well with other systems',
                         'Data can be exchanged with other applications',
                         'Software functions in different environments',
                         'Integration with existing tools is seamless',
                         'Software supports standard formats and protocols'
                     ]]
                ]
            ],
            5 => [
                'title' => 'Usability',
                'subtitle' => 'Is the system easy, efficient, and pleasant for users to interact with?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following usability aspects:', 'required' => true,
                     'rows' => [
                         'Software is easy to learn and use',
                         'User interface is intuitive and clear',
                         'Help and documentation are accessible',
                         'Software prevents user errors effectively',
                         'Overall user experience is satisfying'
                     ]]
                ]
            ],
            6 => [
                'title' => 'Reliability',
                'subtitle' => 'How stable and fault-tolerant is the system in real-world usage?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following reliability aspects:', 'required' => true,
                     'rows' => [
                         'Software operates without failures',
                         'System recovers quickly from errors',
                         'Software maintains data integrity',
                         'System availability meets requirements',
                         'Software performs consistently over time'
                     ]]
                ]
            ],
            7 => [
                'title' => 'Security',
                'subtitle' => 'Does the software prevent unauthorized access and protect data integrity?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following security aspects:', 'required' => true,
                     'rows' => [
                         'Software protects against unauthorized access',
                         'Data is encrypted and secure',
                         'User authentication is robust',
                         'Software maintains audit trails',
                         'Privacy controls are adequate'
                     ]]
                ]
            ],
            8 => [
                'title' => 'Maintainability',
                'subtitle' => 'How easy is it to update or fix issues in the system?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following maintainability aspects:', 'required' => true,
                     'rows' => [
                         'Software is easy to modify and update',
                         'Issues can be diagnosed quickly',
                         'Code quality supports maintenance',
                         'Documentation aids in maintenance tasks',
                         'Testing of modifications is straightforward'
                     ]]
                ]
            ],
            9 => [
                'title' => 'Portability',
                'subtitle' => 'Can the software run across different platforms or be easily installed?',
                'questions' => [
                    ['type' => 'likert', 'text' => 'Rate the following portability aspects:', 'required' => true,
                     'rows' => [
                         'Software can be easily installed',
                         'Software runs on different platforms',
                         'Software can be easily uninstalled',
                         'Configuration is flexible and adaptable',
                         'Migration to new environments is smooth'
                     ]]
                ]
            ],
            10 => [
                'title' => 'Overall Rating and Comments',
                'subtitle' => 'Provide your overall assessment and feedback',
                'questions' => [
                    ['type' => 'rating', 'text' => 'Overall software quality rating', 'required' => true, 'stars' => 10],
                    ['type' => 'essay', 'text' => 'Suggestions for improvement', 'required' => false]
                ]
            ]
        ];

        foreach ($pages as $pageNumber => $pageData) {
            $page = SurveyPage::create([
                'survey_id' => $survey->id,
                'page_number' => $pageNumber,
                'order' => $pageNumber, // Add this line to set the order properly
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
                        'Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'
                    ]) : null,
                    'likert_rows' => isset($questionData['rows']) ? json_encode($questionData['rows']) : null,
                ]);
            }
        }
    }
}
