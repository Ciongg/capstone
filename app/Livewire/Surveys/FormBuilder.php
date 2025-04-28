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
    public $questionTypes = ['add_page', 'multiple_choice', 'essay', 'radio'];
    public $questions = [];
    public $choices = [];
    public $activePageId = null; // Track the active page

    public function mount(Survey $survey)
    {
        $this->survey = $survey;
        $this->loadPages();

        $this->activePageId = collect($this->pages)->first()->id ?? null;
    }

    public function loadPages()
    {
        $this->pages = $this->survey->pages()->with('questions.choices')->orderBy('page_number')->get();

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
    }


    public function addPage()
    {
        $lastPageNumber = $this->survey->pages()->max('page_number') ?? 0;

        $this->survey->pages()->create([
            'page_number' => $lastPageNumber + 1,
        ]);

        $this->loadPages();
    }

    public function addQuestion($type)
    {
       if (!$this->activePageId) {
            return; // Ensure an active page is selected
        }

        $page = SurveyPage::findOrFail($this->activePageId);

        $order = $page->questions()->max('order') + 1;

        $question = $page->questions()->create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $page->id,
            'question_text' => '',
            'question_type' => $type,
            'order' => $order,
            'required' => false,
        ]);

        $this->questions[$question->id] = $question->toArray();

        $this->loadPages();
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

    public function updateQuestion($questionId)
    {
        $question = SurveyQuestion::findOrFail($questionId);
        $question->update([
            'question_text' => $this->questions[$questionId]['question_text'],
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

    public function render()
    {
        return view('livewire.surveys.form-builder');
    }
}
