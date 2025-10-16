<?php

namespace App\Livewire\SuperAdmin\Tags;

use Livewire\Component;
use App\Models\Tag;
use App\Models\TagCategory;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class TagsIndex extends Component
{
    use WithPagination;
    
    // Search and filter variables
    public $search = '';
    public $selectedCategory = 'all';
    
    // For tag category management
    public $showCategoryModal = false;
    public $categoryId = null;
    public $categoryName = '';
    
    // For tag management
    public $showTagModal = false;
    public $tagId = null;
    public $tagName = '';
    public $tagCategoryId = null;
    
    // Change listeners to remove the delete events since we're now calling the methods directly
    protected function getListeners()
    {
        return [
            'refreshComponent' => '$refresh'
        ];
    }
    
    // Validation rules
    protected function rules()
    {
        return [
            'categoryName' => ['required', 'string', 'max:50', 
                Rule::unique('tag_categories', 'name')->ignore($this->categoryId)],
            'tagName' => ['required', 'string', 'max:50'],
            'tagCategoryId' => ['required', 'exists:tag_categories,id'],
        ];
    }
    
    public function updated($propertyName)
    {
        $this->resetPage();
        $this->validateOnly($propertyName);

        // Keep modals open when validation fails
        if (in_array($propertyName, ['categoryName']) && $this->getErrorBag()->has('categoryName')) {
            $this->showCategoryModal = true;
        }

        if (in_array($propertyName, ['tagName', 'tagCategoryId']) && 
            ($this->getErrorBag()->has('tagName') || $this->getErrorBag()->has('tagCategoryId'))) {
            $this->showTagModal = true;
        }
    }
    
    // Category methods
    public function openCategoryModal($categoryId = null)
    {
        $this->resetErrorBag();
        $this->categoryId = $categoryId;
        
        if ($categoryId) {
            $category = TagCategory::find($categoryId);
            if ($category) {
                $this->categoryName = $category->name;
            }
        } else {
            $this->categoryName = '';
        }
        
        $this->showCategoryModal = true;
    }
    
    public function saveCategory()
    {
        $this->validate([
            'categoryName' => ['required', 'string', 'max:50', 
                Rule::unique('tag_categories', 'name')->ignore($this->categoryId)],
        ]);
        
        if ($this->categoryId) {
            // Update existing category
            $category = TagCategory::find($this->categoryId);
            $category->update([
                'name' => $this->categoryName
            ]);
            $message = 'Category updated successfully!';
        } else {
            // Create new category
            TagCategory::create([
                'name' => $this->categoryName
            ]);
            $message = 'Category created successfully!';
        }
        
        // Modal will be closed via the event listener in JavaScript
        $this->showCategoryModal = false;
        $this->dispatch('category-saved', $message);
    }
    
    // Remove the confirmation methods as they're no longer needed
    // We're handling the confirmation directly in the view
    
    public function deleteCategory($categoryId)
    {
        $category = TagCategory::find($categoryId);
        
        if ($category) {
            // Check if category has tags
            if ($category->tags()->count() > 0) {
                $this->dispatch('category-has-tags');
                return;
            }
            
            $category->delete();
            $this->dispatch('category-deleted');
        }
    }
    
    // Tag methods
    public function openTagModal($categoryId, $tagId = null)
    {
        $this->resetErrorBag();
        $this->tagId = $tagId;
        $this->tagCategoryId = $categoryId;
        
        if ($tagId) {
            $tag = Tag::find($tagId);
            if ($tag) {
                $this->tagName = $tag->name;
                $this->tagCategoryId = $tag->tag_category_id;
            }
        } else {
            $this->tagName = '';
        }
        
        $this->showTagModal = true;
    }
    
    public function saveTag()
    {
        $this->validate([
            'tagName' => ['required', 'string', 'max:50'],
            'tagCategoryId' => ['required', 'exists:tag_categories,id'],
        ]);
        
        if ($this->tagId) {
            // Update existing tag
            $tag = Tag::find($this->tagId);
            $tag->update([
                'name' => $this->tagName,
                'tag_category_id' => $this->tagCategoryId
            ]);
            $message = 'Tag updated successfully!';
        } else {
            // Create new tag
            Tag::create([
                'name' => $this->tagName,
                'tag_category_id' => $this->tagCategoryId
            ]);
            $message = 'Tag created successfully!';
        }
        
        // Modal will be closed via the event listener in JavaScript
        $this->showTagModal = false;
        $this->dispatch('tag-saved', $message);
    }
    
    public function deleteTag($tagId)
    {
        $tag = Tag::find($tagId);
        
        if ($tag) {
            // Check if tag is in use (depends on your application)
            $inUseCount = $tag->surveys()->count() + $tag->users()->count();
            
            if ($inUseCount > 0) {
                $this->dispatch('tag-in-use');
                return;
            }
            
            $tag->delete();
            $this->dispatch('tag-deleted');
        }
    }
    
    public function render()
    {
        $query = TagCategory::query()->with('tags');
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('tags', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
        }
        
        if ($this->selectedCategory !== 'all') {
            $query->where('id', $this->selectedCategory);
        }
        
        $categories = $query->orderBy('name')->paginate(10);
        
        return view('livewire.super-admin.tags.tags-index', [
            'categories' => $categories,
            'allCategories' => TagCategory::orderBy('name')->get(),
        ]);
    }
}
