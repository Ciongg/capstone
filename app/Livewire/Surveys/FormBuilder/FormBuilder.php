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
    public $hasResponses = false; 
    public $ratingStars = [];
    public $likertColumns = [];
    public $likertRows = [];

    // Add the listener property
    protected $listeners = ['surveyTitleUpdated' => 'updateTitleFromEvent'];

    public function mount(Survey $survey)
    {
        $this->survey = $survey;
        $this->hasResponses = $survey->responses()->exists(); // Set hasResponses
        $this->loadPages();
        $this->surveyTitle = $survey->title; // Make sure it's initialized
        $this->activePageId = null;
        $this->selectedQuestionId = null;
    }

    public function loadPages()
    {
        $this->pages = $this->survey->pages()
            ->with(['questions' => function ($query) {
                // Eager load choices and order them
                $query->with(['choices' => function ($choiceQuery) {
                    $choiceQuery->orderBy('order'); // Ensure choices are ordered
                }])->orderBy('order');
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
                    $this->likertColumns[$question->id] = is_array($question->likert_columns)
                        ? $question->likert_columns
                        : (json_decode($question->likert_columns, true) ?: []);
                    $this->likertRows[$question->id] = is_array($question->likert_rows)
                        ? $question->likert_rows
                        : (json_decode($question->likert_rows, true) ?: []);
                }
                if ($question->question_type === 'multiple_choice') {
                    $this->questions[$question->id]['limit_answers'] = (bool)($question->limit_answers ?? false);
                }
                foreach ($question->choices as $choice) {
                    // Include is_other in the choices array
                    $this->choices[$choice->id] = $choice->toArray();
                }
            }
        }
    }

    public function setActivePage($pageId)
    {
        // Update state on the server
        $this->activePageId = $pageId;
        $this->selectedQuestionId = null; // Deselect any selected question

        // Dispatch event to signal Alpine to update its state explicitly
        $this->dispatch('pageSelected', pageId: $pageId);
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

        // Set the newly added page as the active page immediately
        $this->activePageId = $newPage->id;
        $this->selectedQuestionId = null; // Deselect any question when adding a page

        // Reload the pages AFTER setting the ID
        $this->loadPages();

        // Dispatch event AFTER loadPages
        $this->dispatch('pageAdded', pageId: $newPage->id);
    }

    public function addQuestion($type)
    {
        if ($type === 'page') {
            $this->addPage();
            return;
        }

        if (!$this->activePageId) {
            // Ensure an active page exists or create one
            $firstPage = $this->survey->pages()->orderBy('page_number')->first();
            if (!$firstPage) {
                $this->addPage();
                $firstPage = $this->survey->pages()->orderBy('page_number')->first();
            }
            $this->activePageId = $firstPage->id;
        }

        $page = SurveyPage::findOrFail($this->activePageId);
        $newOrder = 1; // Default order

        // Determine the order based on selection context
        if ($this->selectedQuestionId === null) {
            // --- Page is selected: Add to the very beginning ---
            $newOrder = 1; // New question will be order 1
            // Increment the order of ALL existing questions on this page
            $page->questions()->increment('order');
        } else {
            // --- Question is selected: Insert after selected question ---
            $selectedQuestion = SurveyQuestion::find($this->selectedQuestionId);
            // Ensure selected question is on the active page, fallback to beginning if not
            if ($selectedQuestion && $selectedQuestion->survey_page_id == $this->activePageId) {
                $newOrder = $selectedQuestion->order + 1;
                // Increment the order of subsequent questions on this page
                $page->questions()->where('order', '>=', $newOrder)->increment('order');
            } else {
                // Fallback: If selected question isn't on this page, add to the beginning
                $newOrder = 1;
                $page->questions()->increment('order');
            }
        }

        // Create the new question with the calculated order and default text
        $question = $page->questions()->create([
            'survey_id' => $this->survey->id,
            'survey_page_id' => $page->id,
            'question_text' => 'Enter Question Here', // Set default question text
            'question_type' => $type,
            'order' => $newOrder,
            'required' => false,
        ]);

        // Handle specific question type initializations (rating, likert)
        if ($type === 'rating') {
            $this->ratingStars[$question->id] = 5;
        }
        if ($type === 'likert') {
            $defaultColumns = ['Agree', 'Neutral', 'Disagree'];
            $defaultRows = ['Statement 1', 'Statement 2', 'Statement 3'];
            $this->likertColumns[$question->id] = $defaultColumns;
            $this->likertRows[$question->id] = $defaultRows;
            $question->likert_columns = $defaultColumns;
            $question->likert_rows = $defaultRows;
            $question->save();
        }
        // Add default choices for multiple choice
        if ($type === 'multiple_choice') {
            SurveyChoice::create([
                'survey_question_id' => $question->id,
                'choice_text' => 'Option 1',
                'order' => 1,
            ]);
            SurveyChoice::create([
                'survey_question_id' => $question->id,
                'choice_text' => 'Option 2',
                'order' => 2,
            ]);
        }

        $this->loadPages(); // Reload data BEFORE setting the selected ID

        // Set the new question as selected AFTER reloading data
        $this->selectedQuestionId = $question->id;
        $this->activePageId = $page->id; // Ensure active page is correct

        // Dispatch event for Alpine focus/scroll
        $this->dispatch('questionAdded', questionId: $question->id, pageId: $page->id);
    }

    public function addChoice($questionId)
    {
        $question = SurveyQuestion::findOrFail($questionId);
        // Ensure 'Other' option stays last if it exists
        $otherOption = $question->choices()->where('is_other', true)->first();
        $orderOffset = $otherOption ? 1 : 0; // If 'Other' exists, new choices go before it

        $existingChoicesCount = $question->choices()->count();
        $order = $existingChoicesCount + 1 - $orderOffset;
        $choiceText = 'Option ' . $order;

        // If 'Other' exists, increment its order
        if ($otherOption) {
            $otherOption->increment('order');
        }

        $choice = SurveyChoice::create([
            'survey_question_id' => $questionId,
            'choice_text' => $choiceText,
            'order' => $order,
            'is_other' => false, // Explicitly false
        ]);

        // No need to update local state here, loadPages will handle it
        $this->loadPages();
    }

    public function addOtherOption($questionId)
    {
        $question = SurveyQuestion::findOrFail($questionId);

        // Check if 'Other' option already exists
        $existingOther = $question->choices()->where('is_other', true)->exists();

        if (!$existingOther) {
            $order = $question->choices()->count() + 1; // Add as the last option

            SurveyChoice::create([
                'survey_question_id' => $questionId,
                'choice_text' => 'Other', // Default text
                'order' => $order,
                'is_other' => true, // Mark as 'Other'
            ]);

            $this->loadPages(); // Reload to reflect changes
        } else {
            // Optional: Add feedback if 'Other' already exists
            session()->flash('error', 'An "Other" option already exists for this question.');
        }
    }

    public function updateQuestion($questionId)
    {
        $question = SurveyQuestion::findOrFail($questionId);

        // Prepare data, ensuring max_answers is null if limit_answers is false
        $limitAnswers = $this->questions[$questionId]['limit_answers'] ?? false;
        $maxAnswers = $limitAnswers ? ($this->questions[$questionId]['max_answers'] ?? null) : null;

        // Update the question fields
        $question->update([
            'question_text' => $this->questions[$questionId]['question_text'] ?? '',
            'required' => $this->questions[$questionId]['required'] ?? false,
            'limit_answers' => $limitAnswers, // Correctly included
            'max_answers' => $maxAnswers,     // Correctly included
        ]);
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
        $isOther = $choice->is_other; // Check if it was the 'Other' option

        // Delete the choice
        $choice->delete();

        // Remove from local state (though loadPages will overwrite)
        unset($this->choices[$choiceId]);

        // Get all subsequent choices for this question, ordered by 'order'
        $subsequentChoices = SurveyChoice::where('survey_question_id', $questionId)
            ->where('order', '>', $deletedOrder)
            ->orderBy('order')
            ->get();

        foreach ($subsequentChoices as $subChoice) {
            // Decrement the order
            $newOrder = $subChoice->order - 1;
            $subChoice->order = $newOrder;

            // If the choice_text matches "Option X" AND it's not the 'Other' option, update it
            if (!$subChoice->is_other && preg_match('/^Option \d+$/', $subChoice->choice_text)) {
                $subChoice->choice_text = 'Option ' . $newOrder;
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
        // Removed the lock check
        if ($this->survey) { 
            $this->survey->title = $this->surveyTitle;
            $this->survey->save();
            // Dispatch the event here too!
            $this->dispatch('surveyTitleUpdated', title: $this->surveyTitle); 
        }
    }

    // Method to handle the event
    public function updateTitleFromEvent($title)
    {
        $this->surveyTitle = $title;
    }

    public function publishSurvey()
    {
        // Check if the survey already has responses
        if ($this->hasResponses) {
            // If it has responses, set status to ongoing
            $this->survey->status = 'ongoing';
        } else {
            // If it has no responses, set status to published
            $this->survey->status = 'published';
        }
        $this->survey->save();
        // Reload pages to potentially update UI elements dependent on status
        $this->loadPages(); 
    }

    public function unpublishSurvey()
    {
        // Unpublishing always sets it back to pending
        $this->survey->status = 'pending';
        $this->survey->save();
        // Reload pages to potentially update UI elements dependent on status
        $this->loadPages(); 
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

    public function getPageForQuestion($questionId)
    {
        if (!$questionId) return null;
        
        foreach ($this->questions as $qId => $question) {
            if ($qId == $questionId) {
                return $question['survey_page_id'];
            }
        }
        
        return null;
    }

    public function render()
    {
        return view('livewire.surveys.form-builder.form-builder', [
            'questions' => $this->questions,
            'choices' => $this->choices,
            'pages' => $this->pages,
            'activePageId' => $this->activePageId,
            'selectedQuestionId' => $this->selectedQuestionId,
            'surveyTitle' => $this->surveyTitle,
            'hasResponses' => $this->hasResponses,
            'ratingStars' => $this->ratingStars,
            'likertColumns' => $this->likertColumns,
            'likertRows' => $this->likertRows,
        ]);
    }
}
