<?php

namespace App\Livewire\Feed;

use Livewire\Component;
use App\Models\Survey;
class Index extends Component
{
    public function render()
    {
        $surveys = Survey::whereIn('status', ['ongoing', 'published'])->latest()->get();
        return view('livewire.feed.index', compact('surveys'));
    }
}
