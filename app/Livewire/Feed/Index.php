<?php

namespace App\Livewire\Feed;

use Livewire\Component;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $search = '';

    public function render()
    {
        $surveys = Survey::whereIn('status', ['ongoing', 'published'])
            ->when($this->search, fn($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->latest()
            ->get();

        $userPoints = Auth::user()?->points ?? 0;

        return view('livewire.feed.index', compact('surveys', 'userPoints'));
    }
}
