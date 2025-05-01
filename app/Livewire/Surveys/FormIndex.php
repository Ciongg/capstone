<?php

namespace App\Livewire\Surveys;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Survey;
class FormIndex extends Component
{



    public function render()
    {
        $surveys = Survey::where('user_id', Auth::id())->latest()->get();
        return view('livewire.surveys.form-index', compact('surveys'));
    }
}
