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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Exception;
use App\Services\AuditLogService;

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
        // Get all unique years from survey created_at dates using Eloquent
        $years = Survey::whereHas('user', function($query) {
                $query->where('institution_id', $this->institution->id);
            })
            ->get()
            ->map(function($survey) {
                return $survey->created_at ? $survey->created_at->format('Y') : null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
        
        // If no data, add current year
        if (empty($years)) {
            $years[] = Carbon::now()->year;
        }
        
        return $years;
    }
    
    private function getPreferredTopics()
    {
        return Survey::whereHas('user', function($query) {
                $query->where('institution_id', $this->institution->id);
            })
            ->whereNotNull('survey_topic_id')
            ->get()
            ->groupBy('survey_topic_id')
            ->map(function($group) {
                return [
                    'survey_topic_id' => $group->first()->survey_topic_id,
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->map(function($item) {
                $topic = SurveyTopic::find($item['survey_topic_id']);
                return [
                    'name' => $topic ? $topic->name : 'Unknown',
                    'count' => $item['count']
                ];
            })
            ->values();
    }
    
    private function getRewardStats()
    {
        // Get redemption counts by reward type for this institution's users using Eloquent
        $rewardsByType = RewardRedemption::with(['reward', 'user'])
            ->whereHas('user', function($query) {
                $query->where('institution_id', $this->institution->id);
            })
            ->get()
            ->groupBy(function($redemption) {
                return $redemption->reward->type ?? 'unknown';
            })
            ->map->count()
            ->toArray();
        
        // Ensure all types are represented
        return [
            'system' => $rewardsByType['system'] ?? 0,
            'voucher' => $rewardsByType['voucher'] ?? 0,
           
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
        // Use withCount, then map to plain arrays so Livewire doesn't rehydrate models and drop computed attrs
        $users = User::where('institution_id', $this->institution->id)
            ->where('type', 'researcher')
            ->withCount('surveys')
            ->orderBy('surveys_count', 'desc')
            ->limit(5)
            ->get(['id','first_name','last_name','email']);

        return $users->map(function ($u) {
            return [
                'id' => $u->id,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'email' => $u->email,
                // fallback to direct count if surveys_count isn't present
                'surveys_count' => isset($u->surveys_count) ? (int)$u->surveys_count : (int)$u->surveys()->count(),
            ];
        })->values();
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

    public function exportToCsv()
    {
        try {
            $filename = 'institution_analytics_' . $this->institution->id . '_' . date('Y-m-d_His') . '.csv';
            
            // Generate CSV without modifying component state
            $csvContent = $this->generateAnalyticsCsvForExport();
            
            if (empty($csvContent)) {
                $this->dispatch('export-error', message: 'Error generating CSV: Empty content');
                return;
            }

            // Log the export action
            AuditLogService::logExport(
                resourceType: 'InstitutionAnalytics',
                message: 'Exported institution analytics to CSV',
                meta: [
                    'filename' => $filename,
                    'institution_id' => $this->institution->id,
                    'institution_name' => $this->institution->name,
                    'selected_year' => $this->selectedYear,
                    'survey_count' => $this->surveyCount,
                    'user_count' => $this->userCount,
                    'total_responses' => $this->totalResponses,
                    'export_timestamp' => now()->toDateTimeString()
                ]
            );

            // Dispatch browser event to download without page refresh
            $this->dispatch('download-csv', content: base64_encode($csvContent), filename: $filename);
            
        } catch (Exception $e) {
            Log::error('Analytics Export error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->dispatch('export-error', message: 'Error exporting to CSV: ' . $e->getMessage());
        }
    }

    private function generateAnalyticsCsvForExport()
    {
        // Fetch fresh data for export without modifying component properties
        $topResearchers = User::where('institution_id', $this->institution->id)
            ->where('type', 'researcher')
            ->get()
            ->map(function($user) {
                $surveyCount = Survey::where('user_id', $user->id)->count();
                $user->surveys_count = $surveyCount;
                return $user;
            })
            ->sortByDesc('surveys_count')
            ->take(5)
            ->values();
        
        $output = fopen('php://temp', 'r+');
        
        // Removed BOM write to avoid double-encoding on client
        
        // Institution Overview Section
        fputcsv($output, ['Institution Analytics Report']);
        fputcsv($output, ['Institution:', $this->institution->name]);
        fputcsv($output, ['Generated:', date('d/m/Y h:i A')]);
        fputcsv($output, []);
        
        // Key Metrics
        fputcsv($output, ['Key Metrics']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Surveys', $this->surveyCount]);
        fputcsv($output, ['Institution Members', $this->userCount]);
        fputcsv($output, ['Total Responses', $this->totalResponses]);
        fputcsv($output, ['Surveys per Researcher', $this->userCount > 0 ? number_format($this->surveyCount / max(1, $this->userCount), 1) : 0]);
        fputcsv($output, ['Response Rate (%)', $this->responseRate]);
        fputcsv($output, []);
        
        // Most Used Survey Topics
        fputcsv($output, ['Most Used Survey Topics']);
        fputcsv($output, ['Topic Name', 'Survey Count', 'Percentage']);
        foreach ($this->preferredTopics as $topic) {
            $percentage = $this->surveyCount > 0 ? round(($topic['count'] / $this->surveyCount) * 100, 1) : 0;
            fputcsv($output, [$topic['name'], $topic['count'], $percentage . '%']);
        }
        fputcsv($output, []);
        
        // Top Researchers - use the fresh data
        fputcsv($output, ['Top Researchers']);
        fputcsv($output, ['Researcher Name', 'Email', 'Survey Count']);
        foreach ($topResearchers as $researcher) {
            fputcsv($output, [
                $researcher->first_name . ' ' . $researcher->last_name,
                $researcher->email,
                $researcher->surveys_count
            ]);
        }
        fputcsv($output, []);
        
        // Reward Redemptions
        fputcsv($output, ['Reward Redemptions by Type']);
        fputcsv($output, ['Reward Type', 'Redemption Count']);
        fputcsv($output, ['System', $this->rewardStats['system']]);
        fputcsv($output, ['Voucher', $this->rewardStats['voucher']]);
        fputcsv($output, []);
        
        // Monthly Survey Trends
        fputcsv($output, ['Survey Creation Trends - ' . $this->selectedYear]);
        fputcsv($output, ['Month', 'Survey Count']);
        foreach ($this->monthlySurveys as $monthData) {
            fputcsv($output, [$monthData['name'], $monthData['count']]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    public function render()
    {
        return view('livewire.institution-admin.institution-analytics.analytics-index');
    }
}
