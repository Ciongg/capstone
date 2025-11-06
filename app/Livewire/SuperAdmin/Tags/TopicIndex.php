<?php

namespace App\Livewire\SuperAdmin\Tags;

use Livewire\Component;
use App\Models\SurveyTopic;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Services\AuditLogService;

class TopicIndex extends Component
{
    use WithPagination;
    
    public $search = '';
    public $topicId = null;
    public $topicName = '';
    
    protected function getListeners()
    {
        return [
            'refreshComponent' => '$refresh'
        ];
    }
    
    protected function rules()
    {
        return [
            'topicName' => ['required', 'string', 'max:50', 
                Rule::unique('survey_topic', 'name')->ignore($this->topicId)]
        ];
    }
    
    public function updated($propertyName)
    {
        $this->resetPage();
        $this->validateOnly($propertyName);
    }
    
    public function openTopicModal($topicId = null)
    {
        $this->resetErrorBag();
        $this->topicId = $topicId;
        
        if ($topicId) {
            $topic = SurveyTopic::find($topicId);
            if ($topic) {
                $this->topicName = $topic->name;
            }
        } else {
            $this->topicName = '';
        }
    }
    
    public function saveTopic()
    {
        $this->validate();
        
        if ($this->topicId) {
            // Update existing topic
            $topic = SurveyTopic::find($this->topicId);
            $oldName = $topic->name;
            $topic->update([
                'name' => $this->topicName
            ]);
            
            // Audit log the topic update
            AuditLogService::logUpdate(
                resourceType: 'SurveyTopic',
                resourceId: $topic->id,
                before: ['name' => $oldName],
                after: ['name' => $this->topicName],
                message: "Updated survey topic from '{$oldName}' to '{$this->topicName}'"
            );
            
            $message = 'Topic updated successfully!';
        } else {
            // Create new topic
            $topic = SurveyTopic::create([
                'name' => $this->topicName
            ]);
            
            // Audit log the topic creation
            AuditLogService::logCreate(
                resourceType: 'SurveyTopic',
                resourceId: $topic->id,
                data: ['name' => $topic->name],
                message: "Created new survey topic: '{$topic->name}'"
            );
            
            $message = 'Topic created successfully!';
        }
        
        $this->dispatch('topic-saved', $message);
        $this->reset(['topicName', 'topicId']);
    }
    
    public function deleteTopic($topicId)
    {
        $topic = SurveyTopic::find($topicId);
        
        if ($topic) {
            try {
                // Get count of surveys using this topic before deletion
                $surveysCount = \App\Models\Survey::where('survey_topic_id', $topic->id)->count();
                
                // Audit log the topic deletion
                AuditLogService::logDelete(
                    resourceType: 'SurveyTopic',
                    resourceId: $topic->id,
                    data: [
                        'name' => $topic->name,
                        'surveys_count' => $surveysCount
                    ],
                    message: "Deleted survey topic: '{$topic->name}' (was used by {$surveysCount} survey(s))"
                );
                
                // Delete the topic
                $topic->delete();
                
                $this->dispatch('topic-deleted');
            } catch (\Exception $e) {
                \Log::error('Error deleting topic: ' . $e->getMessage());
                $this->dispatch('topic-delete-error', 'An error occurred while deleting the topic.');
            }
        }
    }
    
    public function render()
    {
        $query = SurveyTopic::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        $topics = $query->orderBy('name')->paginate(20);
        
        return view('livewire.super-admin.tags.topic-index', [
            'topics' => $topics
        ]);
    }
}
