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
        return User::where('type', 'researcher')
            ->withCount('surveys')
            ->orderBy('surveys_count', 'desc')
            ->limit(5)
            ->get();
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

    public function render()
    {
        return view('livewire.super-admin.analytics.analytics-index');
    }
}