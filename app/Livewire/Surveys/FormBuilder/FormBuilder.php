<?php

namespace App\Livewire\Surveys\FormBuilder;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;

class FormBuilder extends Component
{
    public $survey;
    public $pages = [];
    public $questionTypes = ['page', 'multiple_choice', 'essay', 'radio', 'date', 'rating', 'short_text', 'likert'];
    public $questions = [];
    public $choices = [];
    public $activePageId = null; // Track the active page
    public $selectedQuestionId = null;
    public $surveyTitle;
    public $isLocked = false;
    public $ratingStars = [];
    public $likertColumns = [];
    public $likertRows = [];

    public function mount(Survey $survey)
    {
        $this->survey = $survey;
        $this->isLocked = $survey->responses()->exists();
        $this->loadPages();
        $this->surveyTitle = $survey->title;
        $this->activePageId = null;
        $this->selectedQuestionId = null;
    }

    public function loadPages()
    {
        $this->pages = $this->survey->pages()
            ->with(['questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
            ->orderBy('page_number')
            ->get();

        $this->questions = [];
        $this->choices = [];

        foreach ($this->pages as $page) {
            foreach ($page->questions as $question) {
                $this->questions[$question->id] = $question->toArray();
                if ($question->question_type === 'rating') {
                    $this->ratingStars[$question->id] = $question->stars ?? 5;
                }
                if ($question->question_type === 'likert') {
                    // Always decode to array
                    $this->likertColumns[$question->id] = is_array($question->likert_columns)
                        ? $question->likert_columns
                        : (json_decode($question->likert_columns, true) ?: []);
                    $this->likertRows[$question->id] = is_array($question->likert_rows)
                        ? $question->likert_rows
                        : (json_decode($question->likert_rows, true) ?: []);
                }
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
    
    public function updateRatingStars($questionId)
    {
        $question = SurveyQuestion::findOrFail($questionId);
        $question->stars = $this->ratingStars[$questionId] ?? 5;
        $question->save();
        $this->loadPages();
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
            $this->addPage();
            return;
        }

        if (!$this->activePageId) {
            return;
        }

        $page = SurveyPage::findOrFail($this->activePageId);

        // Determine the order for the new question
        $insertAfterOrder = 0;
        if ($this->selectedQuestionId) {
            $selectedQuestion = SurveyQuestion::findOrFail($this->selectedQuestionId);
            $insertAfterOrder = $selectedQuestion->order;
        }

        // Increment the order of subsequent questions BEFORE creating the new question
        $page->questions()
            ->where('order', '>', $insertAfterOrder)
            ->increment('order');

        // Now create the new question with an empty title and correct order
        $question = $page->questions()->create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $page->id,
            'question_text' => '', // Always empty
            'question_type' => $type,
            'order' => $insertAfterOrder + 1,
            'required' => false,
        ]);

        if ($type === 'rating') {
            $this->ratingStars[$question->id] = 5;
        }

        if ($type === 'likert') {
            // Give 3 default columns and 3 default rows
            $defaultColumns = ['Agree', 'Neutral', 'Disagree'];
            $defaultRows = ['Statement 1', 'Statement 2', 'Statement 3'];
            $this->likertColumns[$question->id] = $defaultColumns;
            $this->likertRows[$question->id] = $defaultRows;

            // Save to DB immediately
            $question->likert_columns = $defaultColumns;
            $question->likert_rows = $defaultRows;
            $question->save();
        }

        // Set the newly added question as the selected question
        $this->selectedQuestionId = $question->id;

        // Reload all pages and questions to ensure state is correct
        $this->loadPages();
    }

    public function addChoice($questionId)
    {
        // Count existing choices for this question
        $existingChoicesCount = SurveyChoice::where('survey_question_id', $questionId)->count();
        $order = $existingChoicesCount + 1;
        $choiceText = 'Option ' . $order;

        $choice = SurveyChoice::create([
            'survey_question_id' => $questionId,
            'choice_text' => $choiceText,
            'order' => $order,
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
        $pageId = $question->survey_page_id; // Get the page ID for the question

        // Delete associated choices
        $question->choices()->delete();

        // Delete the question
        $question->delete();

        // Remove the question from the local state
        unset($this->questions[$questionId]);

        // Update the order of subsequent questions on the same page
        SurveyQuestion::where('survey_page_id', $pageId)
            ->where('order', '>', $question->order)
            ->orderBy('order')
            ->get()
            ->each(function ($subsequentQuestion) {
                $subsequentQuestion->decrement('order');
            });

        // Reload only the affected page's questions
        $this->pages = $this->survey->pages()
            ->with(['questions' => function ($query) use ($pageId) {
                $query->where('survey_page_id', $pageId)->orderBy('order');
            }])
            ->orderBy('page_number')
            ->get();

        // Reset the selected question if it was the one being deleted
        if ($this->selectedQuestionId === $questionId) {
            $this->selectedQuestionId = null;
        }
    }

    public function removeChoice($choiceId)
    {
        $choice = SurveyChoice::findOrFail($choiceId);
        $questionId = $choice->survey_question_id;
        $deletedOrder = $choice->order;

        // Delete the choice
        $choice->delete();

        // Remove from local state
        unset($this->choices[$choiceId]);

        // Get all subsequent choices for this question, ordered by 'order'
        $subsequentChoices = SurveyChoice::where('survey_question_id', $questionId)
            ->where('order', '>', $deletedOrder)
            ->orderBy('order')
            ->get();

        foreach ($subsequentChoices as $subChoice) {
            // Decrement the order
            $subChoice->order = $subChoice->order - 1;

            // If the choice_text matches "Option X", update it to the new order
            if (preg_match('/^Option \d+$/', $subChoice->choice_text)) {
                $subChoice->choice_text = 'Option ' . $subChoice->order;
            }

            $subChoice->save();
        }

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

    public function updateSurveyTitle()
    {
        $this->survey->title = $this->surveyTitle;
        $this->survey->save();
    }

    public function publishSurvey()
    {
        $this->survey->status = 'published';
        $this->survey->save();
    }

    public function unpublishSurvey()
    {
        $this->survey->status = 'pending';
        $this->survey->save();
    }

    public function openSurveySettings()
    {
        // You can emit an event or set a property to show a modal/settings panel
        // $this->dispatch('openSurveySettingsModal');
    }

    public function addLikertColumn($questionId)
    {
        $question = SurveyQuestion::find($questionId);
        $columns = is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []);
        $nextNumber = count($columns) + 1;
        $columns[] = 'Option ' . $nextNumber;
        $question->likert_columns = json_encode($columns);
        $question->save();
        $this->loadPages();
    }

    public function removeLikertColumn($questionId, $colIndex)
    {
        // Remove the column
        $columns = $this->likertColumns[$questionId] ?? [];
        unset($columns[$colIndex]);
        $columns = array_values($columns);

        // Renumber only columns with default name
        $optionNumber = 1;
        foreach ($columns as $i => &$col) {
            if (preg_match('/^Option \d+$/', $col)) {
                $col = 'Option ' . $optionNumber;
            }
            $optionNumber++;
        }

        $this->likertColumns[$questionId] = $columns;

        // Save to DB
        $question = SurveyQuestion::find($questionId);
        $question->likert_columns = json_encode($columns);
        $question->save();

        $this->loadPages();
    }

    public function updateLikertColumn($questionId, $colIndex)
    {
        $this->saveLikert($questionId);
    }

    public function addLikertRow($questionId)
    {
        $question = SurveyQuestion::find($questionId);
        $rows = is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []);
        $nextNumber = count($rows) + 1;
        $rows[] = 'Statement ' . $nextNumber;
        $question->likert_rows = json_encode($rows);
        $question->save();
        $this->loadPages();
    }

    public function removeLikertRow($questionId, $rowIndex)
    {
        // Remove the row
        $rows = $this->likertRows[$questionId] ?? [];
        unset($rows[$rowIndex]);
        $rows = array_values($rows);

        // Renumber only rows with default name
        $statementNumber = 1;
        foreach ($rows as $i => &$row) {
            if (preg_match('/^Statement \d+$/', $row)) {
                $row = 'Statement ' . $statementNumber;
            }
            $statementNumber++;
        }

        $this->likertRows[$questionId] = $rows;

        // Save to DB
        $question = SurveyQuestion::find($questionId);
        $question->likert_rows = json_encode($rows);
        $question->save();

        $this->loadPages();
    }

    public function updateLikertRow($questionId, $rowIndex)
    {
        $this->saveLikert($questionId);
    }

    protected function saveLikert($questionId)
    {
        $question = SurveyQuestion::find($questionId);
        $question->likert_columns = $this->likertColumns[$questionId] ?? [];
        $question->likert_rows = $this->likertRows[$questionId] ?? [];
        $question->save();
        $this->loadPages();
    }

    public function render()
    {
        if ($this->survey->status === 'pending' && $this->isLocked) {
            $this->survey->status = 'ongoing';
            $this->survey->save();
        }

        return view('livewire.surveys.form-builder.form-builder');
    }
}
