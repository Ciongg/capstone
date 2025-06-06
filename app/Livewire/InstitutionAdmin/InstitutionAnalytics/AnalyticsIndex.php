<?php

namespace App\Livewire\InstitutionAdmin\InstitutionAnalytics;

use App\Models\Institution;
use App\Models\Reward;
use App\Models\Survey;
use App\Models\SurveyTopic;
use App\Models\User;
use App\Models\RewardRedemption;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnalyticsIndex extends Component
{
    public $institution;
    public $surveyCount;
    public $userCount;
    public $preferredTopics;
    public $rewardStats;
    public $monthlySurveys;
    public $topResearchers;
    public $responseRate;
    public $selectedYear;
    public $availableYears = [];
    public $totalResponses; // Add this line

    public function mount()
    {
        $this->institution = Auth::user()->institution;
        
        if (!$this->institution) {
            return;
        }
        
        // Set default selected year to current year
        $this->selectedYear = Carbon::now()->year;
        
        // Check if we have any survey data for debugging
        $hasData = Survey::whereHas('user', function($query) {
            $query->where('institution_id', $this->institution->id);
        })->exists();
        
        // If no data, create a dummy survey for testing
        if (!$hasData && app()->environment('local')) {
            // This is just for testing - you can remove this in production
            $researcherUser = User::where('institution_id', $this->institution->id)
                ->where('type', 'researcher')
                ->first();
                
            if ($researcherUser) {
                Survey::create([
                    'user_id' => $researcherUser->id,
                    'title' => 'Test Survey',
                    'description' => 'This is a test survey created for debugging',
                    'status' => 'published',
                    'type' => 'basic',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        // Get available years for the dropdown
        $this->availableYears = $this->getAvailableYears();
        
        $this->loadData();
    }
    
    public function loadData()
    {
        // Get surveys count from users in this institution
        $this->surveyCount = Survey::whereHas('user', function($query) {
            $query->where('institution_id', $this->institution->id);
        })->count();
        
        // Count users in this institution
        $this->userCount = User::where('institution_id', $this->institution->id)->count();
        
        // Get the most used survey topics
        $this->preferredTopics = $this->getPreferredTopics();
        
        // Get rewards distribution by type
        $this->rewardStats = $this->getRewardStats();
        
        // Get monthly survey counts for selected year
        $this->monthlySurveys = $this->getMonthlySurveyData();
        
        // Get top researchers by number of surveys
        $this->topResearchers = $this->getTopResearchers();
        
        // Calculate total responses across all surveys
        $this->totalResponses = $this->getTotalResponses();
        
        // Calculate average response rate
        $this->responseRate = $this->calculateResponseRate();
    }
    
    public function updateYear($year)
    {
        $this->selectedYear = (int)$year;
        $this->monthlySurveys = $this->getMonthlySurveyData();
    }
    
    private function getAvailableYears()
    {
        // Get all unique years from survey created_at dates
        $years = Survey::selectRaw("strftime('%Y', created_at) as year")
            ->whereHas('user', function($query) {
                $query->where('institution_id', $this->institution->id);
            })
            ->groupBy(DB::raw("strftime('%Y', created_at)"))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        // If no data, add current year
        if (empty($years)) {
            $years[] = Carbon::now()->year;
        }
        
        return $years;
    }
    
    private function getPreferredTopics()
    {
        return Survey::select('survey_topic_id', DB::raw('count(*) as count'))
            ->whereHas('user', function($query) {
                $query->where('institution_id', $this->institution->id);
            })
            ->whereNotNull('survey_topic_id')
            ->groupBy('survey_topic_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $topic = SurveyTopic::find($item->survey_topic_id);
                return [
                    'name' => $topic ? $topic->name : 'Unknown',
                    'count' => $item->count
                ];
            });
    }
    
    private function getRewardStats()
    {
        // Get redemption counts by reward type for this institution's users
        $rewardsByType = RewardRedemption::select('rewards.type', DB::raw('count(*) as count'))
            ->join('rewards', 'reward_redemptions.reward_id', '=', 'rewards.id')
            ->join('users', 'reward_redemptions.user_id', '=', 'users.id')
            ->where('users.institution_id', $this->institution->id)
            ->groupBy('rewards.type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();
        
        // Ensure all types are represented
        return [
            'system' => $rewardsByType['system'] ?? 0,
            'voucher' => $rewardsByType['voucher'] ?? 0,
            // 'monetary' => $rewardsByType['monetary'] ?? 0
        ];
    }
    
    private function getMonthlySurveyData()
    {
        $months = [];
        
        // Initialize all months with zero
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = [
                'name' => Carbon::create()->month($i)->format('M'),
                'count' => 0
            ];
        }
        
        // Fix for date comparison in SQLite - make sure we handle year properly
        try {
            // For SQLite, first create a query to get all institution surveys
            $institutionSurveys = Survey::whereHas('user', function($query) {
                    $query->where('institution_id', $this->institution->id);
                })
                ->whereYear('created_at', $this->selectedYear)
                ->get();
            
            // Now manually group by month - this is more reliable than using raw SQLite functions
            $surveyCounts = [];
            foreach ($institutionSurveys as $survey) {
                $month = (int)$survey->created_at->format('m');
                if (!isset($surveyCounts[$month])) {
                    $surveyCounts[$month] = 0;
                }
                $surveyCounts[$month]++;
            }
            
            // Update the months array with our counts
            foreach ($surveyCounts as $month => $count) {
                $months[$month]['count'] = $count;
            }
            
        } catch (\Exception $e) {
            // In case of error, add a dummy month to see something on chart
            $months[1]['count'] = 0; // Force at least one data point
        }
        
        return array_values($months);
    }
    
    private function getTopResearchers()
    {
        return User::select('users.id', 'users.first_name', 'users.last_name', DB::raw('COUNT(surveys.id) as survey_count'))
            ->join('surveys', 'users.id', '=', 'surveys.user_id')
            ->where('users.institution_id', $this->institution->id)
            ->where('users.type', 'researcher')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('survey_count')
            ->limit(5)
            ->get();
    }
    
    private function calculateResponseRate()
    {
        $institutionSurveys = Survey::whereHas('user', function($query) {
            $query->where('institution_id', $this->institution->id);
        })->get();
        
        if ($institutionSurveys->isEmpty()) {
            return 0;
        }
        
        $totalSurveys = $institutionSurveys->count();
        $totalResponses = 0;
        $totalTargetRespondents = 0;
        
        foreach ($institutionSurveys as $survey) {
            $responseCount = $survey->responses()->count();
            $targetCount = $survey->target_respondents ?? 30; // Default to 30 if not set
            
            $totalResponses += $responseCount;
            $totalTargetRespondents += $targetCount;
        }
        
        return $totalTargetRespondents > 0 
            ? round(($totalResponses / $totalTargetRespondents) * 100, 1) 
            : 0;
    }

    // Add this new method to calculate total responses
    private function getTotalResponses()
    {
        $institutionSurveys = Survey::whereHas('user', function($query) {
            $query->where('institution_id', $this->institution->id);
        })->get();
        
        $responseCount = 0;
        foreach ($institutionSurveys as $survey) {
            $responseCount += $survey->responses()->count();
        }
        
        return $responseCount;
    }

    public function render()
    {
        return view('livewire.institution-admin.institution-analytics.analytics-index');
    }
}
