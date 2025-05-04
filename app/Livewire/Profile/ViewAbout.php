<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use App\Models\TagCategory;

class ViewAbout extends Component
{
    public $user;
    public $tagCategories;
    public $selectedTags = [];

    public function mount($user)
    {
        // Ensure $user is a User model instance
        if (is_numeric($user)) {
            $this->user = User::find($user);
        } elseif ($user instanceof User) {
            $this->user = $user;
        } else {
            $this->user = null;
        }

        $this->tagCategories = TagCategory::with('tags')->get();

        if ($this->user) {
            foreach ($this->tagCategories as $category) {
                $tag = $this->user->tags()->where('tag_category_id', $category->id)->first();
                $this->selectedTags[$category->id] = $tag ? $tag->id : '';
            }
        }
    }

    public function saveTags()
    {
        $tagIds = array_filter($this->selectedTags);

        if ($this->user) {
            // Build sync array with tag_name
            $syncData = [];
            foreach ($tagIds as $categoryId => $tagId) {
                $tag = \App\Models\Tag::find($tagId);
                if ($tag) {
                    $syncData[$tagId] = ['tag_name' => $tag->name];
                }
            }
            $this->user->tags()->sync($syncData);
            session()->flash('tags_saved', 'Demographic tags updated!');
        }
    }

    public function render()
    {
        return view('livewire.profile.view-about');
    }
}
