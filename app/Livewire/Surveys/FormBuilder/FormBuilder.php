<?php

namespace App\Livewire\Surveys\FormBuilder;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyPage;
use App\Models\SurveyQuestion;
use App\Models\SurveyChoice;
use Illuminate\Support\Facades\DB; // Import DB facade

class FormBuilder extends Component
{
    public $survey;
    public $pages = [];
    public $questionTypes = ['page', 'multiple_choice', 'radio', 'likert', 'essay', 'short_text', 'rating', 'date' ];
    public $questions = [];
    public $choices = [];

    public $activePageId = null; // Track the active page
    public $selectedQuestionId = null;
    
    public $surveyTitle;
    public $hasResponses = false; 
    public $ratingStars = [];
    public $likertColumns = [];
    public $likertRows = [];
    
    // Add loading states
    public $loadingAddChoice = [];
    public $loadingAddOther = [];
    public $loadingDeleteChoice = [];
    public $loadingAddLikertColumn = [];
    public $loadingAddLikertRow = [];
    public $loadingDeleteLikertColumn = [];
    public $loadingDeleteLikertRow = [];
    public $loadingMoveChoice = [];
    public $loadingUpdateRating = [];
    public $loadingDeletePage = [];
    public $loadingDeleteQuestion = [];
    public $loadingMoveQuestion = [];
    public $loadingAddPage = false;

    // Add save status tracking
    public $saveStatus = ''; // '', 'saving', 'saved'
    public $saveMessage = 'Saving changes...'; // Add this line to define the save message property
    
    // Add the listener property
    protected $listeners = [
        'surveyTitleUpdated' => 'updateTitleFromEvent',
        'settingsOperationCompleted' => 'handleSettingsOperationCompleted', // New listener
        'setSaveStatus' => 'handleSetSaveStatus', // New listener for save status
        'surveySettingsUpdated' => 'handleSurveySettingsUpdated', // Add this listener
    ];

   public function mount(Survey $survey)
   {
       $this->survey = $survey;
       $this->hasResponses = $survey->responses()->exists(); // Set hasResponses
       
       // Check if survey has responses and update status if needed
       $this->checkAndUpdateSurveyStatus();
       
       $this->loadPages();
       $this->surveyTitle = $survey->title; // Make sure it's initialized
       $this->activePageId = null;
       $this->selectedQuestionId = null;
   }
   
   /**
    * Check if survey has responses and update status to 'ongoing' if needed
    */
   protected function checkAndUpdateSurveyStatus()
   {
       // Only change status if the survey has responses and is in draft or published state
       if ($this->hasResponses && in_array($this->survey->status, ['pending', 'published'])) {
           $previousStatus = $this->survey->status;
           $this->survey->status = 'ongoing';
           $this->survey->save();
           
       }
   }

   public function loadPages()
   {
       $this->pages = $this->survey->pages()
           ->with(['questions' => function ($query) {
               // Eager load choices and order them
               $query->with(['choices' => function ($choiceQuery) {
                   $choiceQuery->orderBy('order'); // Ensure choices are ordered
               }])->orderBy('order'); // Ensure questions are ordered
           }])
           ->orderBy('order') // Ensure pages are ordered
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
                       $this->questions[$question->id]['limit_condition'] = $question->limit_condition;
                       $this->questions[$question->id]['max_answers'] = $question->max_answers;
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

       $this->activePageId = $pageId;
       $this->selectedQuestionId = null; // Deselect any selected question


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
       $this->setSaving();
       $this->loadingUpdateRating[$questionId] = true;
       
       $question = SurveyQuestion::findOrFail($questionId);
       $question->stars = $this->ratingStars[$questionId] ?? 5;
       $question->save();
       $this->loadPages();
       
       $this->loadingUpdateRating[$questionId] = false;
       $this->setSaved();
   }

   public function updatePage($pageId, $field, $value)
   {
       $this->setSaving();
       
       $page = SurveyPage::findOrFail($pageId);
       $page->update([$field => $value]);

       $this->loadPages();
       $this->setSaved();
   }


   public function updateQuestion($questionId)
   {
       $this->setSaving();
       
       $question = SurveyQuestion::findOrFail($questionId);
       $questionData = $this->questions[$questionId] ?? [];

       $updateData = [
           'question_text' => $questionData['question_text'] ?? '',
           'required' => $questionData['required'] ?? false,
       ];

       // Handle multiple choice specific fields
       if ($question->question_type === 'multiple_choice') {
           $limitCondition = $questionData['limit_condition'] ?? null;
           // Ensure condition is valid or null
           if (!in_array($limitCondition, ['at_most', 'equal_to', null])) {
               $limitCondition = null;
           }

           $maxAnswers = null;
           if (in_array($limitCondition, ['at_most', 'equal_to'])) {
               // Validate max_answers is a positive integer if condition is set
               $maxInput = $questionData['max_answers'] ?? null;
               if (is_numeric($maxInput) && $maxInput >= 1) {
                   $maxAnswers = (int)$maxInput;
               } else {
                   // If condition is set but max_answers is invalid, throw validation error
                   // reset the condition to avoid saving invalid state
                   // $limitCondition = null;
                   // Or better: Add validation rule
                   $this->addError('questions.' . $questionId . '.max_answers', 'Number is required when limit is set.');
                   $this->saveStatus = '';
                   return; 
               }
           }

           $updateData['limit_condition'] = $limitCondition;
           $updateData['max_answers'] = $maxAnswers;
       }

       $question->update($updateData);
       $this->setSaved();
   }


   public function updateChoice($choiceId)
   {
       $this->setSaving();
       
       $choice = SurveyChoice::findOrFail($choiceId);
       $choice->update([
           'choice_text' => $this->choices[$choiceId]['choice_text'],
       ]);

       $this->loadPages();
       $this->setSaved();
   }
 
   public function updateLikertColumn($questionId, $colIndex)
   {
       $this->setSaving();
       $this->saveLikert($questionId);
       $this->setSaved();
   }

   public function updateLikertRow($questionId, $rowIndex)
   {
       $this->setSaving();
       $this->saveLikert($questionId);
       $this->setSaved();
   }
 
   protected function saveLikert($questionId)
   {
       $question = SurveyQuestion::find($questionId);
       $question->likert_columns = $this->likertColumns[$questionId] ?? [];
       $question->likert_rows = $this->likertRows[$questionId] ?? [];
       $question->save();
       $this->loadPages();
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



   public function publishSurvey()
   {
       // Validate: at least 1 page
       if ($this->pages->isEmpty()) {
           $this->dispatch('showErrorAlert', message: 'You must have at least 1 page in your survey before publishing.');
           return;
       }

       // Validate: at least 6 REQUIRED questions total
       $totalRequiredQuestions = 0;
       foreach ($this->pages as $page) {
           // Only count questions where required = true
           $totalRequiredQuestions += $page->questions->where('required', true)->count();
       }
       
       if ($totalRequiredQuestions < 6) {
           $this->dispatch('showErrorAlert', message: 'Your survey must have at least 6 required questions before publishing. Please mark at least ' . (6 - $totalRequiredQuestions) . ' more questions as required.');
           return;
       }

       // Prevent publishing advanced survey if no demographic is set
       if ($this->survey->type === 'advanced') {
           if ($this->survey->is_institution_only) {
               // Institution-only: require at least one institution tag
               if ($this->survey->institutionTags()->count() < 1) {
                   $this->dispatch('showErrorAlert', message: 'You must set at least one demographic (institution tag) before publishing an advanced survey.');
                   return;
               }
           } else {
               // Public: require at least one general tag
               if ($this->survey->tags()->count() < 1) {
                   $this->dispatch('showErrorAlert', message: 'You must set at least one demographic (general tag) before publishing an advanced survey.');
                   return;
               }
           }
       }

       // Check if the survey already has responses
       if ($this->hasResponses) {
           // If it has responses, set status to ongoing
           $this->survey->status = 'ongoing';
       } else {
           // If it has no responses, set status to published
           $this->survey->status = 'published';
       }
       $this->survey->save();
       
       // Create announcement if is_announced is true
       if ($this->survey->is_announced) {
           $this->createSurveyAnnouncement();
       }
   
       $this->loadPages(); 
   }

   /**
    * Create an announcement for the published survey
    */
   private function createSurveyAnnouncement()
   {
       try {
           $user = auth()->user();
           
           // Always use the current user's institution_id for the announcement
           $institutionId = $user->institution_id;
           
           // Determine target audience based on survey setting
           $targetAudience = $this->survey->is_institution_only ? 'institution_specific' : 'public';
           
           // Copy survey image to announcements folder if it exists
           $announcementImagePath = null;
           if ($this->survey->image_path) {
               // Get the original file extension
               $originalPath = $this->survey->image_path;
               $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
               
               // Create new filename for announcement
               $newFileName = 'survey_' . $this->survey->uuid . '_announcement.' . $extension;
               $newPath = 'announcements/' . $newFileName;
               
               // Copy the file
               if (\Storage::disk('public')->exists($originalPath)) {
                   \Storage::disk('public')->copy($originalPath, $newPath);
                   $announcementImagePath = $newPath;
               }
           }
           
           // Create the announcement with survey_id to maintain relationship
           \App\Models\Announcement::create([
               'title' => $this->survey->title,
               'description' => $this->survey->description ?: 'A new survey has been published. Click to participate!',
               'image_path' => $announcementImagePath,
               'target_audience' => $targetAudience,
               'institution_id' => $institutionId,
               'active' => true,
               'url' => route('surveys.answer', ['survey' => $this->survey->uuid]),
               'start_date' => now(),
               'end_date' => $this->survey->end_date,
               'survey_id' => $this->survey->id, // Link to the survey
           ]);
           
           \Log::info('Survey announcement created successfully', [
               'survey_id' => $this->survey->id,
               'survey_uuid' => $this->survey->uuid,
               'target_audience' => $targetAudience,
               'institution_id' => $institutionId
           ]);
           
       } catch (\Exception $e) {
           \Log::error('Failed to create survey announcement', [
               'survey_id' => $this->survey->id,
               'error' => $e->getMessage()
           ]);
           
           // Don't fail the survey publishing if announcement creation fails
           // Just log the error and continue
       }
   }
   
   public function unpublishSurvey()
   {
       // Unpublishing always sets it back to pending
       $this->survey->status = 'pending';
       $this->survey->save();
       
       // Delete any related announcements
       \App\Models\Announcement::where('survey_id', $this->survey->id)->delete();
  
       $this->loadPages(); 
   }

  


   // --- Reordering Methods ---

   public function movePageUp($pageId)
   {
       $this->moveItemOrder(SurveyPage::class, $pageId, 'up', ['survey_id' => $this->survey->id]);
   }

   public function movePageDown($pageId)
   {
       $this->moveItemOrder(SurveyPage::class, $pageId, 'down', ['survey_id' => $this->survey->id]);
   }

   public function moveQuestionUp($questionId)
   {
       $this->loadingMoveQuestion[$questionId] = true;
       
       $question = SurveyQuestion::find($questionId);
       if ($question) {
           $this->moveItemOrder(SurveyQuestion::class, $questionId, 'up', ['survey_page_id' => $question->survey_page_id]);
       }
       
       $this->loadingMoveQuestion[$questionId] = false;
   }

   public function moveQuestionDown($questionId)
   {
       $this->loadingMoveQuestion[$questionId] = true;
       
       $question = SurveyQuestion::find($questionId);
       if ($question) {
           $this->moveItemOrder(SurveyQuestion::class, $questionId, 'down', ['survey_page_id' => $question->survey_page_id]);
       }
       
       $this->loadingMoveQuestion[$questionId] = false;
   }

   public function moveChoiceUp($choiceId)
   {
       $this->loadingMoveChoice[$choiceId] = true;
       
       $choice = SurveyChoice::find($choiceId);
       if ($choice) {
           $this->moveItemOrder(SurveyChoice::class, $choiceId, 'up', ['survey_question_id' => $choice->survey_question_id]);
       }
       
       $this->loadingMoveChoice[$choiceId] = false;
   }

   public function moveChoiceDown($choiceId)
   {
       $this->loadingMoveChoice[$choiceId] = true;
       
       $choice = SurveyChoice::find($choiceId);
       if ($choice) {
           $this->moveItemOrder(SurveyChoice::class, $choiceId, 'down', ['survey_question_id' => $choice->survey_question_id]);
       }
       
       $this->loadingMoveChoice[$choiceId] = false;
   }

   /**
    * Helper function to move an item up or down in order within a scope.
    *
    * @param string $modelClass The model class (e.g., SurveyPage::class)
    * @param int $itemId The ID of the item to move
    * @param string $direction 'up' or 'down'
    * @param array $scope Conditions to define the scope (e.g., ['survey_id' => 1])
    */
   protected function moveItemOrder(string $modelClass, int $itemId, string $direction, array $scope)
   {
       DB::transaction(function () use ($modelClass, $itemId, $direction, $scope) {
           $item = $modelClass::where($scope)->findOrFail($itemId);
           $currentOrder = $item->order;

           if ($direction === 'up') {
               if ($currentOrder <= 1) return; // Already at the top
               $swapItem = $modelClass::where($scope)->where('order', $currentOrder - 1)->first();
               if ($swapItem) {
                   $item->order--;
                   $swapItem->order++;
                   $item->save();
                   $swapItem->save();
               } else {
                   // Fix potential gap if swap item doesn't exist (shouldn't normally happen)
                   $item->order--;
                   $item->save();
               }
           } elseif ($direction === 'down') {
               $maxOrder = $modelClass::where($scope)->max('order');
               if ($currentOrder >= $maxOrder) return; // Already at the bottom
               $swapItem = $modelClass::where($scope)->where('order', $currentOrder + 1)->first();
               if ($swapItem) {
                   $item->order++;
                   $swapItem->order--;
                   $item->save();
                   $swapItem->save();
               } else {
                    // Fix potential gap if swap item doesn't exist (shouldn't normally happen)
                   $item->order++;
                   $item->save();
               }
           }
       });

       $this->loadPages(); // Reload data after reordering
   }

   public function handleSettingsOperationCompleted(string $status, string $message)
   {
       session()->flash($status, $message);
   }

   // New method to handle save status from modal
   public function handleSetSaveStatus($status, $message = null)
   {
       $this->saveStatus = $status;
       
       // Set the message based on status or use the provided message
       if ($message) {
           $this->saveMessage = $message;
       } else {
           $this->saveMessage = $status === 'saving' ? 'Saving changes...' : 'Changes saved!';
       }
       
       if ($status === 'saved') {
           $this->dispatch('clearSaveStatus');
       }
   }

   // Add this new method to handle survey settings updates
   public function handleSurveySettingsUpdated($surveyId)
   {
       // Refresh the survey model to get latest data
       $this->survey = $this->survey->fresh();
       $this->hasResponses = $this->survey->responses()->exists();
       $this->checkAndUpdateSurveyStatus();
   }

























   public function addItem($type, $parentId = null, $options = [])
   {
       // Set loading state for choice-related operations
       if ($type === 'choice' && $parentId) {
           $this->loadingAddChoice[$parentId] = true;
       } elseif ($type === 'otherOption' && $parentId) {
           $this->loadingAddOther[$parentId] = true;
       } elseif ($type === 'likertColumn' && $parentId) {
           $this->loadingAddLikertColumn[$parentId] = true;
       } elseif ($type === 'likertRow' && $parentId) {
           $this->loadingAddLikertRow[$parentId] = true;
       } elseif ($type === 'page') {
           $this->loadingAddPage = true;
       }

       switch($type) {
           case 'page':
               // Get the highest order and increment it for the new page
               $lastOrder = $this->survey->pages()->max('order') ?? 0;
               
               $newItem = $this->survey->pages()->create([
                   'page_number' => $lastOrder + 1,
                   'order' => $lastOrder + 1,
                   'title' => $options['title'] ?? 'Untitled Page',
                   'subtitle' => $options['subtitle'] ?? '',
               ]);
               
               $this->dispatch('pageAdded', pageId: $newItem->id);
               
               // Clear loading state
               $this->loadingAddPage = false;
               break;
               
           case 'question':
               if (!$parentId) return;
               $page = SurveyPage::findOrFail($parentId);
               $questionType = $options['question_type'] ?? 'multiple_choice';
               $selectedQuestionId = $this->selectedQuestionId;
               
               // Determine order - same logic as before
               if ($selectedQuestionId === null) {
                   $newOrder = 1;
                   $page->questions()->increment('order');
               } else {
                   $selectedQuestion = SurveyQuestion::find($selectedQuestionId);
                   $newOrder = $selectedQuestion->order + 1;
                   $page->questions()->where('order', '>=', $newOrder)->increment('order');
               }
               
               // Create question with the specific type
               $questionData = [
                   'survey_id' => $this->survey->id,
                   'survey_page_id' => $page->id,
                   'question_text' => $options['text'] ?? 'Enter Question Title',
                   'question_type' => $questionType,
                   'order' => $newOrder,
                   'required' => false,
               ];
               
               $newItem = $page->questions()->create($questionData);
               
               // Handle type-specific initialization
               $this->initializeQuestionType($newItem, $questionType);
               $this->selectedQuestionId = $newItem->id;
               $this->activePageId = $page->id;
               $this->dispatch('questionAdded', questionId: $newItem->id, pageId: $page->id);
               break;
               
           case 'choice':
               if (!$parentId) return; // Need a question ID
            
           $question = SurveyQuestion::findOrFail($parentId);
            
            // Check if "Other" choice exists
            $otherChoice = $question->choices()->where('is_other', true)->first();
            
            // Get max order
            $maxOrder = $question->choices()->max('order') ?? 0;
            
            if ($otherChoice) {
                // If "Other" exists, insert new choice before it
                $order = $otherChoice->order; // Take Other's current position
                
                // Increment Other and any choices with same or higher order
                $question->choices()->where('order', '>=', $order)->increment('order');
                
                // Create new choice at the position before Other
                $newItem = SurveyChoice::create([
                    'survey_question_id' => $parentId,
                    'choice_text' => $options['text'] ?? 'Option ' . $order,
                    'order' => $order,
                    'is_other' => $options['is_other'] ?? false,
                ]);
            } else {
                // If no Other exists, just add with next order number
                $order = $maxOrder + 1;
                
                $newItem = SurveyChoice::create([
                    'survey_question_id' => $parentId,
                    'choice_text' => $options['text'] ?? 'Option ' . $order,
                    'order' => $order,
                    'is_other' => $options['is_other'] ?? false,
                ]);
            }
            
            // Clear loading state
            $this->loadingAddChoice[$parentId] = false;
            break;

           case 'otherOption':
           // New case for adding "Other" option
           if (!$parentId) return; // Need a question ID
            
           $question = SurveyQuestion::findOrFail($parentId);
            
           // Check if 'Other' option already exists
           $existingOther = $question->choices()->where('is_other', true)->exists();
            
           if (!$existingOther) {
               // Always use max order + 1 to ensure it's at the end
               $maxOrder = $question->choices()->max('order') ?? 0;
               $order = $maxOrder + 1;
               
               $newItem = SurveyChoice::create([
                   'survey_question_id' => $parentId,
                   'choice_text' => $options['text'] ?? 'Other',
                   'order' => $order,
                   'is_other' => true, // Mark as 'Other'
               ]);
           }
            
           // Clear loading state
           $this->loadingAddOther[$parentId] = false;
           break;

           case 'likertColumn':
               if (!$parentId) return; // Need a question ID
                
               $question = SurveyQuestion::find($parentId);
               $columns = is_array($question->likert_columns) ? 
                   $question->likert_columns : 
                   (json_decode($question->likert_columns, true) ?: []);
                   
               $nextNumber = count($columns) + 1;
               $columns[] = $options['text'] ?? 'Option ' . $nextNumber;
               
               $question->likert_columns = json_encode($columns);
               $question->save();
               
               // Clear loading state
               $this->loadingAddLikertColumn[$parentId] = false;
               
               $newItem = $question;
               break;
               
           case 'likertRow':
                if (!$parentId) return; // Need a question ID

                 $question = SurveyQuestion::find($parentId);
                   $rows = is_array($question->likert_rows) ?
                    $question->likert_rows :
                     (json_decode($question->likert_rows, true) ?: []);

                   $nextNumber = count($rows) + 1;
                   $rows[] = 'Statement ' . $nextNumber;

                   $question->likert_rows = json_encode($rows);
                   $question->save();

                   // Clear loading state
                   $this->loadingAddLikertRow[$parentId] = false;

                   $newItem = $question;
               break;
       }
       
       $this->loadPages();
       return $newItem ?? null;
   }

   // Helper method for question type initialization
   protected function initializeQuestionType($question, $type)
   {
       if ($type === 'rating') {
           $this->ratingStars[$question->id] = 5;
       }
       else if ($type === 'likert') {
           $defaultColumns = ['Agree', 'Neutral', 'Disagree'];
           $defaultRows = ['Statement 1', 'Statement 2', 'Statement 3'];
           $this->likertColumns[$question->id] = $defaultColumns;
           $this->likertRows[$question->id] = $defaultRows;
           $question->likert_columns = $defaultColumns;
           $question->likert_rows = $defaultRows;
           $question->save();
       }
       else if ($type === 'multiple_choice' || $type === 'radio') {
           // Add default choices
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
   }







   
   public function removeItem($type, $itemId)
   {
       // Set loading state for deletion operations
       if ($type === 'choice') {
           $this->loadingDeleteChoice[$itemId] = true;
       } elseif ($type === 'likertColumn') {
           $this->loadingDeleteLikertColumn[$itemId] = true;
       } elseif ($type === 'likertRow') {
           $this->loadingDeleteLikertRow[$itemId] = true;
       } elseif ($type === 'page') {
           $this->loadingDeletePage[$itemId] = true;
       } elseif ($type === 'question') {
           $this->loadingDeleteQuestion[$itemId] = true;
       }

       switch($type) {
       case 'page':
           $page = SurveyPage::findOrFail($itemId);
           $deletedOrder = $page->order;
           
           // Delete associated questions & choices
           foreach ($page->questions as $question) {
               $question->choices()->delete();
               $question->delete();
           }
           $page->delete();
           
           // Update order of subsequent pages
           SurveyPage::where('survey_id', $this->survey->id)
               ->where('order', '>', $deletedOrder)
               ->decrement('order');
               
           // Update active page if needed
           $firstPage = $this->survey->pages()->orderBy('order')->first();
           if ($this->activePageId === $itemId) {
               $this->activePageId = $firstPage ? $firstPage->id : null;
               $this->selectedQuestionId = null;
               $this->dispatch('pageSelected', pageId: $this->activePageId);
           }
           
           // Clear loading state
           $this->loadingDeletePage[$itemId] = false;
           break;
           
       case 'question':
           $question = SurveyQuestion::findOrFail($itemId);
           $pageId = $question->survey_page_id;
           $deletedOrder = $question->order;
           
           // Delete choices and question
           $question->choices()->delete();
           $question->delete();
           
           // Update order of subsequent questions
           SurveyQuestion::where('survey_page_id', $pageId)
               ->where('order', '>', $deletedOrder)
               ->decrement('order');
               
           // Reset selection if this question was selected
           if ($this->selectedQuestionId === $itemId) {
               $this->selectedQuestionId = null;
               $this->activePageId = $pageId;
               $this->dispatch('pageSelected', pageId: $pageId);
           }
           
           // Clear loading state
           $this->loadingDeleteQuestion[$itemId] = false;
           break;
           
       case 'choice':
           $choice = SurveyChoice::findOrFail($itemId);
           $questionId = $choice->survey_question_id;
           $deletedOrder = $choice->order;
           
           $choice->delete();
           
           // Reorder remaining choices
           SurveyChoice::where('survey_question_id', $questionId)
               ->where('order', '>', $deletedOrder)
               ->decrement('order');
            
           // Clear loading state
           $this->loadingDeleteChoice[$itemId] = false;
           break;
           
       case 'likertColumn':
       // Parse itemId to extract questionId and colIndex
       list($questionId, $colIndex) = explode('-', $itemId);
       
       // Remove the column
       $columns = $this->likertColumns[$questionId] ?? [];
       unset($columns[(int)$colIndex]);
       $columns = array_values($columns);

       // Renumber only columns with default name
       $optionNumber = 1;
       foreach ($columns as $i => &$col) {
           if (preg_match('/^Option \d+$/', $col)) {
               $col = 'Option ' . $optionNumber;
               $optionNumber++;
           }
       }

       $this->likertColumns[$questionId] = $columns;

       // Save to DB
       $question = SurveyQuestion::find($questionId);
       if ($question) {
           $question->likert_columns = json_encode($columns);
           $question->save();
       }
       
       // Clear loading state
       $this->loadingDeleteLikertColumn[$itemId] = false;
       break;
       
       case 'likertRow':
       // Parse itemId to extract questionId and rowIndex
       list($questionId, $rowIndex) = explode('-', $itemId);
       
       // Remove the row
       $rows = $this->likertRows[$questionId] ?? [];
       unset($rows[(int)$rowIndex]);
       $rows = array_values($rows);

       // Renumber only rows with default name
       $statementNumber = 1;
       foreach ($rows as $i => &$row) {
           if (preg_match('/^Statement \d+$/', $row)) {
               $row = 'Statement ' . $statementNumber;
               $statementNumber++;
           }
       }

       $this->likertRows[$questionId] = $rows;

       // Save to DB
       $question = SurveyQuestion::find($questionId);
       if ($question) {
           $question->likert_rows = json_encode($rows);
           $question->save();
       }
       
       // Clear loading state
       $this->loadingDeleteLikertRow[$itemId] = false;
       break;
       }
       
       $this->loadPages();
   }


   // Helper methods to show save status
   private function setSaving()
   {
       $this->saveStatus = 'saving';
       $this->saveMessage = 'Saving changes...';
   }

   private function setSaved()
   {
       $this->saveStatus = 'saved';
       $this->saveMessage = 'Changes saved!';
       // Clear the saved status after 2 seconds
       $this->dispatch('clearSaveStatus');
   }

   public function render()
   {
       return view('livewire.surveys.form-builder.form-builder');
   }
}

