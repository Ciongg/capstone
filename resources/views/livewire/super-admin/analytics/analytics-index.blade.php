{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\analytics\analytics-index.blade.php --}}
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">System Analytics Dashboard</h1>
        <button 
            wire:click="exportToCsv"
            wire:loading.attr="disabled"
            wire:target="exportToCsv"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center justify-center disabled:opacity-70 disabled:cursor-not-allowed min-w-[100px] sm:min-w-[220px]"
        >
            <span wire:loading.remove wire:target="exportToCsv" class="inline-flex items-center justify-center w-full">
                <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/>
                </svg>
                <span class="hidden sm:inline">Export Analytics to CSV</span>
                <span class="sm:hidden">Export</span>
            </span>
            <span wire:loading wire:target="exportToCsv" class="inline-flex items-center justify-center w-full">
                <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        </button>
    </div>
    
    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Total Surveys</h2>
            <p class="text-3xl">
                {{ $surveyCount > 0 ? $surveyCount : 0 }}
            </p>
            <p class="text-gray-500 text-sm mt-2">Created by all users</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Total Users</h2>
            <p class="text-3xl">{{ $userCount }}</p>
            <p class="text-gray-500 text-sm mt-2">Registered users</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Total Responses</h2>
            <p class="text-3xl">{{ $totalResponses }}</p>
            <p class="text-gray-500 text-sm mt-2">Collected across all surveys</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Surveys per Researcher</h2>
            <p class="text-3xl">
                {{ $userCount > 0 && $surveyCount > 0 ? number_format($surveyCount / max(1, $userCount), 1) : 0 }}
            </p>
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
                                        {{ $researcher['first_name'] }} {{ $researcher['last_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $researcher['surveys_count'] }}
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
                <select id="year-select" wire:model.live="selectedYear" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart Initialization Scripts -->
    <script>
        let rewardChart, trendsChart;
        function initializeCharts() {
            // Reward Chart
            const rewardCtx = document.getElementById('rewardChart').getContext('2d');
            if (rewardChart) rewardChart.destroy();
            rewardChart = new Chart(rewardCtx, {
                type: 'bar',
                data: {
                    labels: ['System', 'Voucher'],
                    datasets: [{
                        label: 'Redemptions',
                        data: [
                            {{ $rewardStats['system'] ?? 0 }},
                            {{ $rewardStats['voucher'] ?? 0 }},
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)'
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
            // Survey Trends Chart
            const trendsCtx = document.getElementById('surveyTrendsChart').getContext('2d');
            if (trendsChart) trendsChart.destroy();
            const monthNames = @json(collect($monthlySurveys)->pluck('month'));
            const monthlyCounts = @json(collect($monthlySurveys)->pluck('count'));
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
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        // Re-initialize charts after Livewire updates (ensures charts persist)
        document.addEventListener('livewire:update', function () {
            initializeCharts();
        });

        // Listen for CSV download event and trigger client-side download without refresh
        document.addEventListener('livewire:init', () => {
            Livewire.on('download-csv', (payload) => {
                // Support payload as object
                const contentB64 = payload?.content ?? '';
                const filename = payload?.filename ?? 'analytics.csv';

                // Decode base64 to binary string
                const binary = atob(contentB64);

                // Convert to bytes and prepend UTF-8 BOM as raw bytes
                const len = binary.length;
                const bytes = new Uint8Array(len + 3);
                bytes[0] = 0xEF; bytes[1] = 0xBB; bytes[2] = 0xBF;
                for (let i = 0; i < len; i++) {
                    bytes[i + 3] = binary.charCodeAt(i);
                }

                // Build Blob with correct bytes (avoids BOM becoming visible characters)
                const blob = new Blob([bytes], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                // Ensure charts still display
                initializeCharts();
            });
        });
    </script>
</div>