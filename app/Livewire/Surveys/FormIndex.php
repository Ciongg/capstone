<?php

namespace App\Livewire\Surveys;
use Illuminate\Support\Facades\Auth; // Ensure Auth facade is imported
use Livewire\Component;
use App\Models\Survey;

class FormIndex extends Component
{
    public function deleteSurvey($surveyId)
    {
        $survey = Survey::where('user_id', Auth::id())->findOrFail($surveyId); // Use Auth::id()
        // Add authorization checks if needed

        // Consider deleting related data (pages, questions, choices, responses) if not handled by model events or cascade deletes
        $survey->pages()->delete(); // Example: Delete pages (adjust based on your relationships and needs)
        $survey->responses()->delete(); // Example: Delete responses

        $survey->delete();

        // Optionally: Add a success message
        session()->flash('message', 'Survey deleted successfully.');

        // The component will re-render automatically, removing the deleted survey
    }

    public function render()
    {
        $surveys = Survey::where('user_id', Auth::id()) // Use Auth::id()
                         ->withCount('responses') // Add this line
                         ->latest()
                         ->get();

        return view('livewire.surveys.form-index', compact('surveys'));
    }
}
