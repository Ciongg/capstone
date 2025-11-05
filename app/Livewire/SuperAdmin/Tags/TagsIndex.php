<?php

namespace App\Livewire\SuperAdmin\Tags;

use Livewire\Component;
use App\Models\Tag;
use App\Models\TagCategory;
use App\Models\InstitutionTagCategory;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogService;

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
    
    // User role properties
    public $isInstitutionAdmin = false;
    public $institutionId = null;
    
    // Add this new property to track if we're working with institution data
    public $isForInstitution = false;
    
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
    
    public function mount()
    {
        $user = Auth::user();
        $this->isInstitutionAdmin = $user->isInstitutionAdmin();
        $this->institutionId = $user->institution_id;
    }
    
    // Category methods
    public function openCategoryModal($categoryId = null, $isInstitution = false)
    {
        $this->resetErrorBag();
        $this->categoryId = $categoryId;
        $this->isForInstitution = $isInstitution;
        
        if ($categoryId) {
            if ($isInstitution) {
                $category = InstitutionTagCategory::find($categoryId);
            } else {
                $category = TagCategory::find($categoryId);
            }
            
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
            $oldName = $category->name;
            $category->update([
                'name' => $this->categoryName
            ]);
            
            // Audit log the category update
            AuditLogService::logUpdate(
                resourceType: 'TagCategory',
                resourceId: $category->id,
                before: ['name' => $oldName],
                after: ['name' => $this->categoryName],
                message: "Updated tag category from '{$oldName}' to '{$this->categoryName}'"
            );
            
            $message = 'Category updated successfully!';
        } else {
            // Create new category
            $category = TagCategory::create([
                'name' => $this->categoryName
            ]);
            
            // Audit log the category creation
            AuditLogService::logCreate(
                resourceType: 'TagCategory',
                resourceId: $category->id,
                data: ['name' => $category->name],
                message: "Created new tag category: '{$category->name}'"
            );
            
            $message = 'Category created successfully!';
        }
        
        $this->showCategoryModal = false;
        $this->dispatch('category-saved', $message);
    }
    
    /**
     * Save a category for an institution
     */
    public function saveInstitutionCategory()
    {
        $this->validate([
            'categoryName' => ['required', 'string', 'max:50', 
                Rule::unique('institution_tag_categories', 'name')
                    ->where('institution_id', $this->institutionId)
                    ->ignore($this->categoryId)],
        ]);
        
        if ($this->categoryId) {
            // Update existing category
            $category = InstitutionTagCategory::find($this->categoryId);
            $oldName = $category->name;
            $category->update([
                'name' => $this->categoryName,
                'institution_id' => $this->institutionId
            ]);
            
            // Audit log the institution category update
            AuditLogService::logUpdate(
                resourceType: 'InstitutionTagCategory',
                resourceId: $category->id,
                before: ['name' => $oldName, 'institution_id' => $this->institutionId],
                after: ['name' => $this->categoryName, 'institution_id' => $this->institutionId],
                message: "Updated institution tag category from '{$oldName}' to '{$this->categoryName}'"
            );
            
            $message = 'Category updated successfully!';
        } else {
            // Create new category
            $category = InstitutionTagCategory::create([
                'name' => $this->categoryName,
                'institution_id' => $this->institutionId
            ]);
            
            // Audit log the institution category creation
            AuditLogService::logCreate(
                resourceType: 'InstitutionTagCategory',
                resourceId: $category->id,
                data: ['name' => $category->name, 'institution_id' => $this->institutionId],
                message: "Created new institution tag category: '{$category->name}'"
            );
            
            $message = 'Category created successfully!';
        }
        
        $this->showCategoryModal = false;
        $this->dispatch('institution-category-saved', $message);
    }
    
    // Remove the confirmation methods as they're no longer needed
    // We're handling the confirmation directly in the view
    
    public function deleteCategory($categoryId)
    {
        if ($this->isInstitutionAdmin) {
            $category = InstitutionTagCategory::find($categoryId);
            
            if ($category) {
                if ($category->tags()->count() > 0) {
                    $this->dispatch('category-has-tags');
                    return;
                }
                
                // Audit log the institution category deletion
                AuditLogService::logDelete(
                    resourceType: 'InstitutionTagCategory',
                    resourceId: $category->id,
                    data: ['name' => $category->name, 'institution_id' => $category->institution_id],
                    message: "Deleted institution tag category: '{$category->name}'"
                );
                
                $category->delete();
                $this->dispatch('category-deleted');
            }
        } else {
            $category = TagCategory::find($categoryId);
            
            if ($category) {
                if ($category->tags()->count() > 0) {
                    $this->dispatch('category-has-tags');
                    return;
                }
                
                // Audit log the category deletion
                AuditLogService::logDelete(
                    resourceType: 'TagCategory',
                    resourceId: $category->id,
                    data: ['name' => $category->name],
                    message: "Deleted tag category: '{$category->name}'"
                );
                
                $category->delete();
                $this->dispatch('category-deleted');
            }
        }
    }
    
    // Tag methods
    public function openTagModal($categoryId, $tagId = null, $isInstitution = false)
    {
        $this->resetErrorBag();
        $this->tagId = $tagId;
        $this->tagCategoryId = $categoryId;
        $this->isForInstitution = $isInstitution;
        
        if ($tagId) {
            if ($isInstitution) {
                // Load institution tag
                $tag = \App\Models\InstitutionTag::find($tagId);
                if ($tag) {
                    $this->tagName = $tag->name;
                    $this->tagCategoryId = $tag->institution_tag_category_id;
                }
            } else {
                // Load global tag
                $tag = Tag::find($tagId);
                if ($tag) {
                    $this->tagName = $tag->name;
                    $this->tagCategoryId = $tag->tag_category_id;
                }
            }
        } else {
            $this->tagName = '';
        }
        
        $this->showTagModal = true;
    }
    
    public function saveTag()
    {
        $this->validate([
            'tagName' => [
                'required', 
                'string', 
                'max:50',
                function ($attribute, $value, $fail) {
                    $exists = Tag::where('tag_category_id', $this->tagCategoryId)
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->when($this->tagId, function ($query) {
                            return $query->where('id', '!=', $this->tagId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('A tag with this name already exists in this category.');
                    }
                }
            ],
            'tagCategoryId' => ['required', 'exists:tag_categories,id'],
        ]);
        
        if ($this->tagId) {
            // Update existing tag
            $tag = Tag::find($this->tagId);
            $oldData = ['name' => $tag->name, 'tag_category_id' => $tag->tag_category_id];
            $tag->update([
                'name' => $this->tagName,
                'tag_category_id' => $this->tagCategoryId
            ]);
            
            // Audit log the tag update
            AuditLogService::logUpdate(
                resourceType: 'Tag',
                resourceId: $tag->id,
                before: $oldData,
                after: ['name' => $this->tagName, 'tag_category_id' => $this->tagCategoryId],
                message: "Updated tag: '{$this->tagName}'"
            );
            
            $message = 'Tag updated successfully!';
        } else {
            // Create new tag
            $tag = Tag::create([
                'name' => $this->tagName,
                'tag_category_id' => $this->tagCategoryId
            ]);
            
            // Audit log the tag creation
            AuditLogService::logCreate(
                resourceType: 'Tag',
                resourceId: $tag->id,
                data: ['name' => $tag->name, 'tag_category_id' => $tag->tag_category_id],
                message: "Created new tag: '{$tag->name}'"
            );
            
            $message = 'Tag created successfully!';
        }
        
        $this->showTagModal = false;
        $this->dispatch('tag-saved', $message);
    }
    
    /**
     * Save a tag for an institution
     */
    public function saveInstitutionTag()
    {
        $this->validate([
            'tagName' => [
                'required', 
                'string', 
                'max:50',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\InstitutionTag::where('institution_tag_category_id', $this->tagCategoryId)
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->when($this->tagId, function ($query) {
                            return $query->where('id', '!=', $this->tagId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('A tag with this name already exists in this category.');
                    }
                }
            ],
            'tagCategoryId' => ['required', 'exists:institution_tag_categories,id'],
        ]);
        
        $category = InstitutionTagCategory::where('id', $this->tagCategoryId)
            ->where('institution_id', $this->institutionId)
            ->first();
        
        if (!$category) {
            $this->addError('tagCategoryId', 'The selected category is invalid.');
            return;
        }
        
        if ($this->tagId) {
            // Update existing institution tag
            $tag = \App\Models\InstitutionTag::find($this->tagId);
            
            if ($tag) {
                $oldData = ['name' => $tag->name, 'institution_tag_category_id' => $tag->institution_tag_category_id];
                $tag->update([
                    'name' => $this->tagName,
                    'institution_tag_category_id' => $this->tagCategoryId
                ]);
                
                // Audit log the institution tag update
                AuditLogService::logUpdate(
                    resourceType: 'InstitutionTag',
                    resourceId: $tag->id,
                    before: $oldData,
                    after: ['name' => $this->tagName, 'institution_tag_category_id' => $this->tagCategoryId],
                    message: "Updated institution tag: '{$this->tagName}'"
                );
                
                $message = 'Tag updated successfully!';
            } else {
                $this->addError('tagId', 'The selected tag could not be found.');
                return;
            }
        } else {
            // Create new institution tag
            $tag = \App\Models\InstitutionTag::create([
                'name' => $this->tagName,
                'institution_tag_category_id' => $this->tagCategoryId
            ]);
            
            // Audit log the institution tag creation
            AuditLogService::logCreate(
                resourceType: 'InstitutionTag',
                resourceId: $tag->id,
                data: ['name' => $tag->name, 'institution_tag_category_id' => $tag->institution_tag_category_id],
                message: "Created new institution tag: '{$tag->name}'"
            );
            
            $message = 'Tag created successfully!';
        }
        
        $this->showTagModal = false;
        $this->dispatch('institution-tag-saved', $message);
    }
    
    // Keep the existing tag deletion method
    public function deleteTag($tagId)
    {
        $tag = Tag::find($tagId);
        
        if ($tag) {
            $usersCount = $tag->users()->count();
            $surveysCount = $tag->surveys()->count();
            
            // Detach tag from users and surveys before deletion
            $tag->users()->detach();
            $tag->surveys()->detach();
            
            // Audit log the tag deletion
            AuditLogService::logDelete(
                resourceType: 'Tag',
                resourceId: $tag->id,
                data: [
                    'name' => $tag->name,
                    'tag_category_id' => $tag->tag_category_id,
                    'users_count' => $usersCount,
                    'surveys_count' => $surveysCount
                ],
                message: "Deleted tag: '{$tag->name}' (was used by {$usersCount} user(s) and {$surveysCount} survey(s))"
            );
            
            $tag->delete();
            $this->dispatch('tag-deleted');
        }
    }
    
    // Add method to delete institution tags
    public function deleteInstitutionTag($tagId)
    {
        $tag = \App\Models\InstitutionTag::find($tagId);
        
        if ($tag) {
            $usersCount = $tag->users()->count();
            $surveysCount = $tag->surveys()->count();
            
            // Detach tag from users and surveys before deletion
            $tag->users()->detach();
            $tag->surveys()->detach();
            
            // Audit log the institution tag deletion
            AuditLogService::logDelete(
                resourceType: 'InstitutionTag',
                resourceId: $tag->id,
                data: [
                    'name' => $tag->name,
                    'institution_tag_category_id' => $tag->institution_tag_category_id,
                    'users_count' => $usersCount,
                    'surveys_count' => $surveysCount
                ],
                message: "Deleted institution tag: '{$tag->name}' (was used by {$usersCount} user(s) and {$surveysCount} survey(s))"
            );
            
            $tag->delete();
            $this->dispatch('tag-deleted');
        }
    }
    
    public function render()
    {
        if ($this->isInstitutionAdmin) {
            // For institution admins, show only their institution's tag categories
            $query = InstitutionTagCategory::query()->with('tags')
                ->where('institution_id', $this->institutionId);
            
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
                'allCategories' => InstitutionTagCategory::where('institution_id', $this->institutionId)
                    ->orderBy('name')->get(),
                'isInstitutionAdmin' => true,
            ]);
        } else {
            // Original functionality for super admins
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
                'isInstitutionAdmin' => false,
            ]);
        }
    }
}
