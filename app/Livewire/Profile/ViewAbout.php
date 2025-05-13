<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use App\Models\TagCategory;
use App\Models\Tag;
use App\Models\InstitutionTagCategory;
use App\Models\InstitutionTag;
use Illuminate\Support\Facades\Auth;

class ViewAbout extends Component
{
    public User $user;
    public $selectedTags = [];
    public $selectedInstitutionTags = [];
    public $tagCategories;
    public $institutionTagCategories = [];

    public function mount()
    {
        $this->user = Auth::user();
        
        // Load regular tag categories and tags
        $this->tagCategories = TagCategory::with('tags')->get();
        
        // Load user's selected tags
        $userTags = $this->user->tags()->pluck('tags.id')->toArray();
        
        // Organize selected tags by category
        foreach ($this->tagCategories as $category) {
            $categoryTags = $category->tags->pluck('id')->toArray();
            $userTagsForCategory = array_intersect($userTags, $categoryTags);
            
            if (!empty($userTagsForCategory)) {
                $this->selectedTags[$category->id] = reset($userTagsForCategory);
            }
        }
        
        // If user belongs to an institution, load institution-specific tag categories
        if ($this->user->institution_id) {
            $this->institutionTagCategories = InstitutionTagCategory::where('institution_id', $this->user->institution_id)
                ->with('tags')
                ->get();
            
            // Load user's selected institution tags
            $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();
            
            // Organize selected institution tags by category
            foreach ($this->institutionTagCategories as $category) {
                // Get all tags belonging to this category
                $categoryTags = $category->tags->pluck('id')->toArray();
                
                // Find which of the user's tags belong to this category
                $userTagsForCategory = array_intersect($userInstitutionTags, $categoryTags);
                
                if (!empty($userTagsForCategory)) {
                    $this->selectedInstitutionTags[$category->id] = reset($userTagsForCategory);
                }
            }
        }
    }

    public function saveTags()
    {
        // Process regular tags
        $tagsToSync = [];
        foreach ($this->selectedTags as $categoryId => $tagId) {
            if (!empty($tagId)) {
                // Get tag name for denormalization
                $tag = Tag::find($tagId);
                if ($tag) {
                    $tagsToSync[$tagId] = ['tag_name' => $tag->name];
                }
            }
        }
        
        // Sync regular tags
        $this->user->tags()->sync($tagsToSync);
        
        // Process institution tags if applicable
        if ($this->user->institution_id && !empty($this->selectedInstitutionTags)) {
            $institutionTagsToSync = [];
            foreach ($this->selectedInstitutionTags as $categoryId => $tagId) {
                if (!empty($tagId)) {
                    // Get tag name for denormalization
                    $tag = InstitutionTag::find($tagId);
                    if ($tag) {
                        $institutionTagsToSync[$tagId] = ['tag_name' => $tag->name];
                    }
                }
            }
            
            // Sync institution tags
            $this->user->institutionTags()->sync($institutionTagsToSync);
        }
        
        session()->flash('tags_saved', 'Your demographic information has been updated successfully!');
    }

    public function render()
    {
        return view('livewire.profile.view-about');
    }
}