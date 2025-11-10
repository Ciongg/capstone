<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\TagCategory;
use App\Models\Tag;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use App\Livewire\SuperAdmin\Tags\TagsIndex;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create([
        'name' => 'Test University',
        'domain' => 'test.edu'
    ]);
    
    $this->superAdmin = User::create([
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'superadmin@system.com',
        'password' => bcrypt('password'),
        'type' => 'super_admin',
        'institution_id' => null,
        'is_active' => true,
    ]);
    
    Auth::login($this->superAdmin);
});

// Category CRUD Tests
it('can create edit and delete category', function () {
    // Create
    $component = Livewire::test(TagsIndex::class)
        ->set('categoryName', 'Age Group')
        ->call('saveCategory');
    
    $category = TagCategory::where('name', 'Age Group')->first();
    expect($category)->not->toBeNull();
    
    // Edit
    $component->call('openCategoryModal', $category->id)
        ->set('categoryName', 'Age Range')
        ->call('saveCategory');
    
    $category->refresh();
    expect($category->name)->toBe('Age Range');
    
    // Delete empty category
    $component->call('deleteCategory', $category->id);
    expect(TagCategory::find($category->id))->toBeNull();
});

it('validates category fields and prevents deletion with tags', function () {
    // Test required and unique validation
    TagCategory::create(['name' => 'Education']);
    
    Livewire::test(TagsIndex::class)
        ->set('categoryName', '')
        ->call('saveCategory')
        ->assertHasErrors(['categoryName']);
    
    Livewire::test(TagsIndex::class)
        ->set('categoryName', 'Education')
        ->call('saveCategory')
        ->assertHasErrors(['categoryName']);
    
    // Test cannot delete category with tags
    $category = TagCategory::create(['name' => 'Gender']);
    Tag::create(['name' => 'Male', 'tag_category_id' => $category->id]);
    
    Livewire::test(TagsIndex::class)
        ->call('deleteCategory', $category->id)
        ->assertDispatched('category-has-tags');
    
    expect(TagCategory::find($category->id))->not->toBeNull();
});

// Tag CRUD Tests
it('can create edit and delete tags', function () {
    $category = TagCategory::create(['name' => 'Age Group']);
    
    // Create tag
    Livewire::test(TagsIndex::class)
        ->call('openTagModal', $category->id)
        ->set('tagName', '18-24')
        ->call('saveTag');
    
    $tag = Tag::where('name', '18-24')->first();
    expect($tag)->not->toBeNull();
    expect($tag->tag_category_id)->toBe($category->id);
    
    // Edit tag
    Livewire::test(TagsIndex::class)
        ->call('openTagModal', $category->id, $tag->id)
        ->set('tagName', '25-34')
        ->call('saveTag');
    
    $tag->refresh();
    expect($tag->name)->toBe('25-34');
    
    // Delete tag and verify user detachment
    $user = User::factory()->create(['institution_id' => $this->institution->id]);
    $user->tags()->attach($tag->id, ['tag_name' => $tag->name]);
    
    Livewire::test(TagsIndex::class)
        ->call('deleteTag', $tag->id);
    
    expect(Tag::find($tag->id))->toBeNull();
    expect($user->fresh()->tags()->count())->toBe(0);
});

it('validates tag uniqueness within category', function () {
    $category = TagCategory::create(['name' => 'Age Group']);
    Tag::create(['name' => '18-24', 'tag_category_id' => $category->id]);
    
    // Cannot create duplicate in same category
    Livewire::test(TagsIndex::class)
        ->call('openTagModal', $category->id)
        ->set('tagName', '18-24')
        ->call('saveTag')
        ->assertHasErrors(['tagName']);
});

it('searches categories and tags', function () {
    $category = TagCategory::create(['name' => 'Age Group']);
    Tag::create(['name' => '18-24', 'tag_category_id' => $category->id]);
    Tag::create(['name' => '25-34', 'tag_category_id' => $category->id]);
    
    // Search categories
    $component = Livewire::test(TagsIndex::class)
        ->set('search', 'Age');
    
    expect($component->viewData('categories')->count())->toBe(1);
    
    // Search tags
    $component = Livewire::test(TagsIndex::class)
        ->set('search', '25-34');
    
    $categories = $component->viewData('categories');
    $matchingTags = $categories->first()->tags->filter(fn($tag) => str_contains($tag->name, '25'));
    
    expect($matchingTags->count())->toBe(1);
    expect($matchingTags->first()->name)->toBe('25-34');
});
