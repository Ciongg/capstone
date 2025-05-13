<?php

namespace App\Livewire\Profile\Institution;

use Livewire\Component;
use App\Models\Institution;
use App\Models\InstitutionTagCategory;
use App\Models\InstitutionTag;
use Illuminate\Support\Facades\Auth;

class SetInstitutionDemographic extends Component
{
    public $institution;
    public $categories = [];
    public $newCategoryName = '';
    public $newTagNames = [];
    public $editingCategoryId = null;
    public $editingCategoryName = '';

    public function mount()
    {
        // Get the institution associated with the current admin user
        $this->institution = Auth::user()->institution;
        $this->loadCategories();
        $this->newTagNames = ['']; // Initialize with one empty tag field
    }

    public function loadCategories()
    {
        if ($this->institution) {
            $this->categories = InstitutionTagCategory::where('institution_id', $this->institution->id)
                ->with('tags')
                ->get()
                ->toArray();
        } else {
            $this->categories = [];
        }
    }

    public function addCategory()
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255',
            'newTagNames' => 'required|array|min:1',
            'newTagNames.*' => 'required|string|max:255',
        ]);

        // Create the category
        $category = InstitutionTagCategory::create([
            'institution_id' => $this->institution->id,
            'name' => $this->newCategoryName,
        ]);

        // Create all the tags for this category
        foreach ($this->newTagNames as $tagName) {
            if (!empty($tagName)) {
                InstitutionTag::create([
                    'institution_tag_category_id' => $category->id,
                    'name' => $tagName,
                ]);
            }
        }

        $this->newCategoryName = ''; // Clear the category name input
        $this->newTagNames = [''];   // Reset tag names to one empty field
        $this->loadCategories();

        $this->dispatch('notify', [
            'type' => 'success', 
            'message' => 'Demographic category added successfully!'
        ]);
    }

    public function addTagField()
    {
        $this->newTagNames[] = '';
    }

    public function removeTagField($index)
    {
        if (count($this->newTagNames) > 1) {
            unset($this->newTagNames[$index]);
            $this->newTagNames = array_values($this->newTagNames);
        }
    }

    public function startEditingCategory($categoryId)
    {
        $this->editingCategoryId = $categoryId;
        $category = InstitutionTagCategory::find($categoryId);
        if ($category) {
            $this->editingCategoryName = $category->name;
        }
    }

    public function updateCategory()
    {
        $this->validate([
            'editingCategoryName' => 'required|string|max:255',
        ]);

        $category = InstitutionTagCategory::find($this->editingCategoryId);
        if ($category && $category->institution_id == $this->institution->id) {
            $category->name = $this->editingCategoryName;
            $category->save();
            
            $this->loadCategories();
            $this->cancelEditing();

            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => 'Category updated successfully!'
            ]);
        }
    }

    public function cancelEditing()
    {
        $this->editingCategoryId = null;
        $this->editingCategoryName = '';
    }

    public function deleteCategory($categoryId)
    {
        $category = InstitutionTagCategory::find($categoryId);
        if ($category && $category->institution_id == $this->institution->id) {
            // Delete all associated tags
            $category->tags()->delete();
            // Delete the category
            $category->delete();
            
            $this->loadCategories();
            
            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => 'Category and all associated tags deleted successfully!'
            ]);
        }
    }

    public function addTagToCategory($categoryId)
    {
        $category = InstitutionTagCategory::find($categoryId);
        if ($category && $category->institution_id == $this->institution->id) {
            InstitutionTag::create([
                'institution_tag_category_id' => $categoryId,
                'name' => 'New Tag',
            ]);
            
            $this->loadCategories();
        }
    }

    public function updateTag($tagId, $name)
    {
        $tag = InstitutionTag::find($tagId);
        if ($tag && $tag->category->institution_id == $this->institution->id) {
            $tag->name = $name;
            $tag->save();
            
            $this->loadCategories();
        }
    }

    public function deleteTag($tagId)
    {
        $tag = InstitutionTag::find($tagId);
        if ($tag && $tag->category->institution_id == $this->institution->id) {
            $tag->delete();
            
            $this->loadCategories();
        }
    }

    public function render()
    {
        return view('livewire.profile.institution.set-institution-demographic');
    }
}
