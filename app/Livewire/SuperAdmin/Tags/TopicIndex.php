<?php

namespace App\Livewire\SuperAdmin\Tags;

use Livewire\Component;
use App\Models\SurveyTopic;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

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
            $topic->update([
                'name' => $this->topicName
            ]);
            $message = 'Topic updated successfully!';
        } else {
            // Create new topic
            SurveyTopic::create([
                'name' => $this->topicName
            ]);
            $message = 'Topic created successfully!';
        }
        
        $this->dispatch('topic-saved', $message);
        $this->reset(['topicName', 'topicId']);
    }
    
    public function deleteTopic($topicId)
    {
        $topic = SurveyTopic::find($topicId);
        
        if ($topic) {
            // Remove the check that prevents deletion when topic is in use
            // Instead, simply delete the topic and let the database cascade the deletion
            try {
                // Delete the topic
                $topic->delete();
                
                // Notify user of successful deletion
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
