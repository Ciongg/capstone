<?php

namespace App\Livewire\Surveys;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;

class FormBuilder extends Component
{
    public $survey;
    public $pages = [];
    public $questionTypes = ['page', 'multiple_choice', 'essay', 'radio'];
    public $questions = [];
    public $choices = [];
    public $activePageId = null; // Track the active page
    public $selectedQuestionId = null;

    public function mount(Survey $survey)
    {
        $this->survey = $survey;
        $this->loadPages();

        $this->activePageId = collect($this->pages)->first()->id ?? null;
    }

    public function loadPages()
    {
        $this->pages = $this->survey->pages()->with('questions.choices')->orderBy('page_number')->get();

        // Clear the questions and choices arrays to avoid conflicts
        $this->questions = [];
        $this->choices = [];

        // Load questions and choices into arrays for editing
        foreach ($this->pages as $page) {
            foreach ($page->questions as $question) {
                $this->questions[$question->id] = $question->toArray();
                foreach ($question->choices as $choice) {
                    $this->choices[$choice->id] = $choice->toArray();
                }
            }
        }
    }


    public function setActivePage($pageId)
    {
        $this->activePageId = $pageId;
        $this->selectedQuestionId = null; // Deselect any selected question
    }

    public function selectQuestion($questionId)
    {
        $this->selectedQuestionId = $questionId;

        // Set the active page to the page where the question resides
        $question = SurveyQuestion::findOrFail($questionId);
        $this->activePageId = $question->survey_page_id;
    }
    


    public function addPage()
    {
        // Get the last page number and increment it for the new page
        $lastPageNumber = $this->survey->pages()->max('page_number') ?? 0;

        // Create a new page
        $newPage = $this->survey->pages()->create([
            'page_number' => $lastPageNumber + 1,
            'title' => 'Untitled Page',
            'subtitle' => '',
        ]);

        // Reload the pages to reflect the new page
        $this->loadPages();

        // Set the newly added page as the active page
        $this->activePageId = $newPage->id;
    }

    public function addQuestion($type)
    {
        if ($type === 'page') {
            $this->addPage(); // Call the addPage method for the page type
            return;
        }

        if (!$this->activePageId) {
            return; // Ensure an active page is selected
        }

        $page = SurveyPage::findOrFail($this->activePageId);

        // Determine the order for the new question
        $insertAfterOrder = 0;
        if ($this->selectedQuestionId) {
            $selectedQuestion = SurveyQuestion::findOrFail($this->selectedQuestionId);
            $insertAfterOrder = $selectedQuestion->order;
        }

        // Increment the order of subsequent questions
        $page->questions()
            ->where('order', '>', $insertAfterOrder)
            ->increment('order');

        // Create the new question
        $question = $page->questions()->create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $page->id,
            'question_text' => '',
            'question_type' => $type,
            'order' => $insertAfterOrder + 1,
            'required' => false,
        ]);

        // Reload the pages to ensure proper indexing
        $this->loadPages();

        // Set the newly added question as the selected question
        $this->selectedQuestionId = $question->id;
    }

    public function addChoice($questionId)
    {
        $choice = SurveyChoice::create([
            'survey_question_id' => $questionId,
            'choice_text' => '',
        ]);

        $this->choices[$choice->id] = $choice->toArray();

        $this->loadPages();
    }

    public function updateQuestion($questionId, $newText = null)
    {
        $question = SurveyQuestion::findOrFail($questionId);
        
        // Preferably get fresh value from front-end input, not stale array
        $question->update([
            'question_text' => $newText ?? $this->questions[$questionId]['question_text'],
        ]);
    
        $this->loadPages();
    }
    

    public function updateChoice($choiceId)
    {
        $choice = SurveyChoice::findOrFail($choiceId);
        $choice->update([
            'choice_text' => $this->choices[$choiceId]['choice_text'],
        ]);

        $this->loadPages();
    }

    public function updatePage($pageId, $field, $value)
    {
        $page = SurveyPage::findOrFail($pageId);
        $page->update([$field => $value]);

        $this->loadPages();
    }

    public function removeQuestion($questionId)
    {
        $question = SurveyQuestion::findOrFail($questionId);
        $question->choices()->delete(); // Remove associated choices
        $question->delete();

        unset($this->questions[$questionId]);

        $this->loadPages();
    }

    public function removeChoice($choiceId)
    {
        SurveyChoice::findOrFail($choiceId)->delete();

        unset($this->choices[$choiceId]);

        $this->loadPages();
    }

    public function removePage($pageId)
    {
        $page = SurveyPage::findOrFail($pageId);
        $page->questions()->each(function ($question) {
            $question->choices()->delete(); // Remove associated choices
            $question->delete(); // Remove questions
        });
        $page->delete();

        $remainingPages = $this->survey->pages()->orderBy('page_number')->get();
        foreach ($remainingPages as $index => $remainingPage) {
            $remainingPage->update(['page_number' => $index + 1]);
        }


        $this->loadPages();

        if ($this->activePageId === $pageId) {
            $this->activePageId = $remainingPages->first()->id ?? null;
        }
        
    }
    public function deleteAll()
    {
        // Delete all questions and their choices
        SurveyQuestion::where('survey_id', $this->survey->id)->each(function ($question) {
            $question->choices()->delete();
            $question->delete();
        });

        // Delete all pages
        SurveyPage::where('survey_id', $this->survey->id)->delete();

        // Reload the pages to reflect the changes
        $this->loadPages();

        // Reset active page and selected question
        $this->activePageId = null;
        $this->selectedQuestionId = null;
    }

    public function render()
    {
        return view('livewire.surveys.form-builder');
    }
}
