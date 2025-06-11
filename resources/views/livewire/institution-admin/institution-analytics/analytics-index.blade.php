<div class="container mx-auto px-4 py-8">
    @if(!$institution)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
            <p>No institution associated with your account. Please contact a system administrator.</p>
        </div>
    @else
        <h1 class="text-2xl font-bold mb-6">{{ $institution->name }} - Analytics Dashboard</h1>
        
        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-2">Total Surveys</h2>
                <p class="text-3xl">{{ $surveyCount }}</p>
                <p class="text-gray-500 text-sm mt-2">Created by institution members</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-2">Institution Members</h2>
                <p class="text-3xl">{{ $userCount }}</p>
                <p class="text-gray-500 text-sm mt-2">Active users</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-2">Total Responses</h2>
                <p class="text-3xl">{{ $totalResponses }}</p>
                <p class="text-gray-500 text-sm mt-2">Collected across all surveys</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-2">Surveys per Researcher</h2>
                <p class="text-3xl">{{ $userCount > 0 ? number_format($surveyCount / max(1, $userCount), 1) : 0 }}</p>
                <p class="text-gray-500 text-sm mt-2">Average productivity</p>
            </div>
        </div>
        
        <!-- Two Column Layout for Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Preferred Survey Topics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Most Used Survey Topics</h2>
                @if(count($preferredTopics) > 0)
                    <div class="space-y-4">
                        @foreach($preferredTopics as $topic)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span>{{ $topic['name'] }}</span>
                                    <span>{{ $topic['count'] }} surveys</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(100, ($topic['count'] / max(1, $surveyCount)) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No survey topics data available</p>
                @endif
            </div>
            
            <!-- Top Researchers -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Top Researchers</h2>
                @if(count($topResearchers) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Researcher</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Surveys</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topResearchers as $researcher)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $researcher->first_name }} {{ $researcher->last_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $researcher->survey_count }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No researcher data available</p>
                @endif
            </div>
        </div>
        
        <!-- Reward Distribution Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Reward Redemptions by Type</h2>
            <div class="h-64">
                <canvas id="rewardChart"></canvas>
            </div>
        </div>
        
        <!-- Survey Trends Chart (This Year) -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Survey Creation Trends</h2>
                <div class="flex items-center space-x-2">
                    <label for="year-select" class="text-sm font-medium text-gray-700">Year:</label>
                    <select id="year-select" wire:model.live="selectedYear" wire:change="updateYear($event.target.value)" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="h-64">
                <canvas id="surveyTrendsChart"></canvas>
            </div>
        </div>
    @endif

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Chart Initialization Scripts -->
    @if($institution)
    <script>
        // Global variables to store chart instances
        let rewardChart, trendsChart;

        // Function to initialize or update charts
        function initializeCharts() {
            // Set up the reward chart
            const rewardCtx = document.getElementById('rewardChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (rewardChart) {
                rewardChart.destroy();
            }
            
            rewardChart = new Chart(rewardCtx, {
                type: 'bar',
                data: {
                    labels: ['System', 'Voucher'],
                    datasets: [{
                        label: 'Redemptions',
                        data: [
                            {{ $rewardStats['system'] }}, 
                            {{ $rewardStats['voucher'] }}, 
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }
                }
            });
            
            // Set up the survey trends chart
            const trendsCtx = document.getElementById('surveyTrendsChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (trendsChart) {
                trendsChart.destroy();
            }
            
            // Get the data for the chart
            const monthNames = @json(collect($monthlySurveys)->pluck('name'));
            const monthlyCounts = @json(collect($monthlySurveys)->pluck('count'));
            
            // Debug - log the data to console
            console.log("Month names:", monthNames);
            console.log("Monthly counts:", monthlyCounts);
            
            trendsChart = new Chart(trendsCtx, {
                type: 'bar',
                data: {
                    labels: monthNames,
                    datasets: [{
                        label: 'Surveys Created in {{ $selectedYear }}',
                        data: monthlyCounts,
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgb(79, 70, 229)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            },
                            title: {
                                display: true,
                                text: 'Number of Surveys'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                    return monthNames[tooltipItems[0].dataIndex] + ' ' + {{ $selectedYear }};
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Initialize charts when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
        
      
    </script>
    @endif
</div>
