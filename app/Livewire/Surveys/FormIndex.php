<?php

namespace App\Livewire\Surveys;

use Livewire\Component;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        
        // Get surveys owned by the user
        $surveys = Survey::where('user_id', $user->id)
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get surveys where the user is a collaborator
        $sharedSurveys = Survey::whereHas('collaborators', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('user:id,first_name,last_name') // Load owner details
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('livewire.surveys.form-index', [
            'surveys' => $surveys,
            'sharedSurveys' => $sharedSurveys
        ]);
    }
}
