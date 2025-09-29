<?php

namespace App\Livewire\Surveys\FormBuilder;

use Livewire\Component;
use App\Models\Survey;
use App\Models\SurveyGenerationJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SurveyGenerationStatus extends Component
{
    public $survey;
    public $job;
    public $previousJobStatus = null;
    public $refreshInterval = 3000; // 3 seconds
    
    protected $listeners = ['surveyGenerationStarted' => 'handleJobStarted'];
    
    public function mount($surveyId)
    {
        $this->survey = Survey::findOrFail($surveyId);
        $this->refreshJob();
    }
    
    public function handleJobStarted($data)
    {
        // Clear any dismissal flags when a new job starts
        Session::forget('dismissed_job_' . $this->survey->id);
        
        // Immediately refresh the job status when a new job is started
        $this->refreshJob();
    }
    
    public function refreshJob()
    {
        try {
            // Store current job status before refreshing
            $previousStatus = $this->job ? $this->job->status : null;
            $previousJobId = $this->job ? $this->job->id : null;
            
            // Check if user has dismissed the job status
            if (Session::has('dismissed_job_' . $this->survey->id)) {
                $this->job = null;
                return;
            }
            
            // Always get the latest job for this survey
            $latestJob = SurveyGenerationJob::where('survey_id', $this->survey->id)
                ->latest()
                ->first();
            
            // If no job found, nothing to display
            if (!$latestJob) {
                $this->job = null;
                return;
            }
            
            // Set the job regardless of status - we want to show it until dismissed
            $this->job = $latestJob;
            
            // If this is a newly completed job (status just changed), update the survey structure
            if ($previousStatus === 'processing' && $latestJob->status === 'completed') {
                $this->dispatch('surveyStructureUpdated');
            }
            
            // Only log failed jobs once per job (not on every refresh)
            if ($latestJob->status === 'failed' && $latestJob->id !== $previousJobId) {
                $result = json_decode($latestJob->result, true);
                Log::warning('Displaying failed survey generation job', [
                    'job_id' => $latestJob->id,
                    'survey_id' => $this->survey->id,
                    'message' => $result['message'] ?? 'Unknown error',
                    'result' => $latestJob->result
                ]);
            }
            
            // Update previous job status for next comparison
            $this->previousJobStatus = $this->job ? $this->job->status : null;
            
        } catch (\Exception $e) {
            // Log any errors that occur during job status checking (but only once)
            if (!Session::has('logged_job_error_' . $this->survey->id)) {
                Log::error('Error retrieving survey generation job status: ' . $e->getMessage(), [
                    'survey_id' => $this->survey->id,
                    'trace' => $e->getTraceAsString()
                ]);
                Session::put('logged_job_error_' . $this->survey->id, true);
            }
            
            // If there's any database error, just set job to null
            $this->job = null;
        }
    }
    
    public function dismissStatus()
    {
        // Mark the job as dismissed so it won't appear again
        Session::put('dismissed_job_' . $this->survey->id, true);
        // Clear any error logging flags
        Session::forget('logged_job_error_' . $this->survey->id);
        $this->job = null;
    }
    
    public function applyChanges()
    {
        // Mark the job as dismissed and reload the page
        Session::put('dismissed_job_' . $this->survey->id, true);
        $this->dispatch('refreshPage');
        return redirect(request()->header('Referer'));
    }
    
    public function retryWithShorterAbstract()
    {
        if ($this->job && $this->job->status === 'failed') {
            // Mark current job as dismissed
            Session::put('dismissed_job_' . $this->survey->id, true);
            
            // Redirect to the modal with a suggestion to use a shorter description
            $this->dispatch('openSurveyGeneratorModal', ['survey' => $this->survey])
                 ->to('surveys.form-builder.form-builder');
            
            // Clear the job display
            $this->job = null;
        }
    }
    
    /**
     * Display the raw AI response for debugging
     */
    public function viewDebugData()
    {
        if ($this->job && $this->job->status === 'failed') {
            try {
                $debugData = json_decode($this->job->debug_data ?? '{}', true);
                
                // Create a condensed view of the debug data
                $excerpt = $debugData['response_excerpt'] ?? 'No debug data available';
                $length = $debugData['response_length'] ?? 0;
                
                // Log the debug info accessed
                Log::info('Survey generation debug data accessed', [
                    'job_id' => $this->job->id,
                    'survey_id' => $this->survey->id,
                    'user_id' => auth()->id()
                ]);
                
                // Show the debug data in an alert
                $this->dispatch('showDebugModal', [
                    'title' => "AI Response Debug (Job #{$this->job->id})",
                    'content' => $excerpt,
                    'fullLength' => $length
                ]);
            } catch (\Exception $e) {
                Log::error('Error displaying debug data', [
                    'job_id' => $this->job->id,
                    'error' => $e->getMessage()
                ]);
                
                $this->dispatch('showErrorAlert', [
                    'message' => 'Error displaying debug data: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    public function getStatusColorClass()
    {
        if (!$this->job) {
            return 'bg-gray-100';
        }
        
        switch ($this->job->status) {
            case 'pending':
                return 'bg-blue-100 text-blue-800';
            case 'processing':
                return 'bg-yellow-100 text-yellow-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'failed':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100';
        }
    }
    
    /**
     * Cancel an active survey generation job
     */
    public function cancelJob()
    {
        if (!$this->job) {
            return;
        }
        
        try {
            // Only cancel jobs that are in pending or processing state
            if (in_array($this->job->status, ['pending', 'processing'])) {
                // Update job status to cancelled
                $this->job->status = 'failed';
                $this->job->result = json_encode([
                    'success' => false,
                    'message' => 'Job cancelled by user'
                ]);
                $this->job->save();
                
                // Log the cancellation
                Log::info('Survey generation job cancelled by user', [
                    'job_id' => $this->job->id,
                    'survey_id' => $this->survey->id,
                    'user_id' => auth()->id()
                ]);
                
                // If using Laravel Horizon or queue-specific cancellation:
                // Uncomment if you're using Laravel Horizon or need to actually cancel the queue job
                // \Illuminate\Support\Facades\Bus::findBatch($this->job->job_batch_id)->cancel();
                
                // Show success message to user
                $this->dispatch('showSuccessAlert', [
                    'message' => 'Survey generation cancelled successfully'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cancel survey generation job', [
                'job_id' => $this->job->id,
                'survey_id' => $this->survey->id,
                'error' => $e->getMessage()
            ]);
            
            // Show error to user
            $this->dispatch('showErrorAlert', [
                'message' => 'Failed to cancel job: ' . $e->getMessage()
            ]);
        }
        
        // Refresh the job status
        $this->refreshJob();
    }
    
    public function render()
    {
        return view('livewire.surveys.form-builder.survey-generation-status');
    }
}



