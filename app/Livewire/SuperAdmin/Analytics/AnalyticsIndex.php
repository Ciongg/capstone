<?php

namespace App\Livewire\SuperAdmin\Analytics;

use App\Models\Reward;
use App\Models\Survey;
use App\Models\SurveyTopic;
use App\Models\User;
use App\Models\RewardRedemption;
use App\Models\Response;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Exception;

class AnalyticsIndex extends Component
{
    public $surveyCount;
    public $userCount;
    public $preferredTopics;
    public $rewardStats;
    public $monthlySurveys;
    public $topResearchers;
    public $responseRate;
    public $selectedYear;
    public $availableYears = [];
    public $totalResponses;

    public function mount()
    {
        // Set default selected year to current year
        $this->selectedYear = Carbon::now()->year;
        $this->availableYears = $this->getAvailableYears();
        $this->loadData();
    }

    public function loadData()
    {
        $this->surveyCount = Survey::count();
        $this->userCount = User::count();
        $this->preferredTopics = $this->getPreferredTopics();
        $this->rewardStats = $this->getRewardStats();
        $this->monthlySurveys = $this->getMonthlySurveyData();
        $this->topResearchers = $this->getTopResearchers();
        $this->totalResponses = $this->getTotalResponses();
        $this->responseRate = $this->calculateResponseRate();
    }

    public function updatedSelectedYear()
    {
        $this->loadData();
    }

    private function getAvailableYears()
    {
        $years = Survey::all()->map(function($survey) {
            return $survey->created_at ? $survey->created_at->format('Y') : null;
        })->filter()->unique()->sortDesc()->values()->toArray();
        if (empty($years)) {
            $years[] = now()->year;
        }
        return $years;
    }

    private function getMonthlySurveyData()
    {
        $surveys = Survey::whereYear('created_at', $this->selectedYear)->get();
        $monthlyCounts = array_fill(1, 12, 0);
        foreach ($surveys as $survey) {
            $month = (int)$survey->created_at->format('n');
            $monthlyCounts[$month]++;
        }
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = [
                'month' => Carbon::create()->month($month)->format('M'),
                'count' => $monthlyCounts[$month]
            ];
        }
        return $monthlyData;
    }

    private function getPreferredTopics()
    {
        $topicCounts = Survey::whereNotNull('survey_topic_id')
            ->get()
            ->groupBy('survey_topic_id')
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5);
        return $topicCounts->map(function($count, $topicId) {
            $topic = SurveyTopic::find($topicId);
            return [
                'name' => $topic ? $topic->name : 'Unknown',
                'count' => $count
            ];
        })->values();
    }

    private function getRewardStats()
    {
        $rewards = RewardRedemption::with('reward')->get();
        $rewardsByType = $rewards->groupBy(function($redemption) {
            return $redemption->reward->type ?? 'unknown';
        })->map->count();
        return [
            'system' => $rewardsByType['system'] ?? 0,
            'voucher' => $rewardsByType['voucher'] ?? 0,
        ];
    }

    private function getTopResearchers()
    {
        $users = User::where('type', 'researcher')
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
                'surveys_count' => isset($u->surveys_count) ? (int)$u->surveys_count : (int)$u->surveys()->count(),
            ];
        })->values();
    }

    private function calculateResponseRate()
    {
        if ($this->surveyCount === 0) {
            return 0;
        }
        $averageResponses = $this->totalResponses / $this->surveyCount;
        return min(100, ($averageResponses / 10) * 100);
    }

    private function getTotalResponses()
    {
        return Response::count();
    }

    public function exportToCsv()
    {
        try {
            $filename = 'system_analytics_' . date('Y-m-d_His') . '.csv';
            
            // Generate CSV without modifying component state
            $csvContent = $this->generateAnalyticsCsvForExport();
            
            if (empty($csvContent)) {
                $this->dispatch('export-error', message: 'Error generating CSV: Empty content');
                return;
            }

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
        $topResearchers = User::where('type', 'researcher')
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
        
        // System Overview Section
        fputcsv($output, ['System Analytics Report']);
        fputcsv($output, ['Generated:', date('d/m/Y h:i A')]);
        fputcsv($output, []);
        
        // Key Metrics
        fputcsv($output, ['Key Metrics']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Surveys', $this->surveyCount]);
        fputcsv($output, ['Total Users', $this->userCount]);
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
            fputcsv($output, [$monthData['month'], $monthData['count']]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    public function render()
    {
        return view('livewire.super-admin.analytics.analytics-index');
    }
}