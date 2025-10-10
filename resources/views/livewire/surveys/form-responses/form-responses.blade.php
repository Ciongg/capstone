<div class="bg-gray-100 min-h-screen py-4 sm:py-8">
    <div class="max-w-7xl mx-auto space-y-6 sm:space-y-10 px-2 sm:px-4">

        <!-- Display any session flash messages -->
        @if(session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
            <a href="{{ route('surveys.create', $survey->uuid) }}"
               class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 flex items-center justify-center text-sm sm:text-base"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Editor
            </a>
            <div class="flex-grow"></div>
        
            {{-- Export to CSV Button - Only show if there are responses --}}
            @if($survey->responses()->count() > 0)
                <button
                    wire:click="exportToCsv"
                    wire:loading.attr="disabled"
                    wire:target="exportToCsv"
                    class="w-40 px-3 sm:px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 inline-flex items-center justify-center text-sm sm:text-base mr-2 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="exportToCsv" class="inline-flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 flex-shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        <span>Export to CSV</span>
                    </span>
                    <span wire:loading wire:target="exportToCsv" class="inline-flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        
                    </span>
                </button>
            @endif
            
            {{-- View Individual Responses Button - Only show if there are responses --}}
            @if($survey->responses()->count() > 0)
                <a href="{{ route('surveys.responses.individual', $survey->uuid) }}"
                   class="px-3 sm:px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center justify-center text-sm sm:text-base"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"></path>
                    </svg>
                    View Individual Responses
                </a>
            @endif
        
        </div>

        {{-- Top summary containers --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 sm:mb-8">
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex flex-col items-center">
                <span class="text-base sm:text-lg font-semibold">Responses</span>
                <span class="text-xl sm:text-2xl text-blue-600 font-bold mt-2">
                    {{ $survey->responses()->count() }}
                </span>
            </div>
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex flex-col items-center">
                <span class="text-base sm:text-lg font-semibold">Average Time</span>
                <span class="text-xl sm:text-2xl text-blue-600 font-bold mt-2">
                    @if(isset($averageTime) && $averageTime !== null)
                        @php
                            $minutes = floor($averageTime / 60);
                            $seconds = $averageTime % 60;
                        @endphp
                        {{ sprintf('%d:%02d', $minutes, $seconds) }}
                    @else
                        --
                    @endif
                </span>
            </div>
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex flex-col items-center">
                <span class="text-base sm:text-lg font-semibold">Points</span>
                <span class="text-xl sm:text-2xl text-blue-600 font-bold mt-2">
                    {{ $survey->points_allocated ?? '--' }}
                </span>
            </div>
        </div>

        {{-- Check if survey has any responses --}}
        @if($survey->responses()->count() === 0)
            {{-- No responses message --}}
            <div class="bg-white shadow rounded-lg sm:rounded-2xl p-8 sm:p-12 text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Responses Yet</h3>
                <p class="text-gray-500 mb-6">This survey hasn't received any responses yet. Share the survey link to start collecting responses.</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('surveys.create', $survey->uuid) }}" 
                       class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Back to Editor
                    </a>
                </div>
            </div>
        @else
            {{-- <div class="flex justify-end mb-4">
                <button
                    wire:click="clearAllAISummaries"
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 font-semibold"
                    onclick="return confirm('Are you sure you want to clear all AI summaries for this survey?')"
                >
                    Clear All AI Summaries
                </button>
            </div> --}}

            @php
                $colors = ['#60a5fa', '#fbbf24', '#34d399', '#f87171', '#a78bfa', '#f472b6', '#facc15', '#38bdf8'];
                $questionCounter = 1; // Initialize question counter
            @endphp

            @if(isset($survey))
            @foreach($survey->pages as $page)
                {{-- Ensure questions are sorted by their order within the page --}}
                @foreach($page->questions->sortBy('order') as $question) 
                    {{-- FOR MULTIPLE CHOICE AND RADIO --}}
                    @if(in_array($question->question_type, ['multiple_choice', 'radio']))

                        {{-- container of question for multiple choice and radio aka single option--}}
                        <div class="bg-white shadow rounded-lg sm:rounded-2xl p-4 sm:p-8 mb-6 sm:mb-8" wire:key="question-{{ $question->id }}">
                            {{-- Include the question title and "More Details" button --}}
                            @include('livewire.surveys.form-responses.partials.question-details-button-modal', ['question' => $question, 'questionCounter' => $questionCounter])
                            
                            {{-- Store choice colors in a JSON object for consistent reference --}}
                            @php
                                $choiceColors = [];
                                foreach($question->choices->sortBy('order') as $i => $choice) {
                                    $choiceColors[$choice->id] = $colors[$i % count($colors)];
                                }
                            @endphp
                            
                            {{-- Pie Chart and Legend Container --}}
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                                {{-- Legend --}}
                                <div class="md:w-1/2">
                                    <ul>
                                        @foreach($question->choices->sortBy('order') as $choice)
                                            <li class="flex items-center mb-2">
                                                <span class="inline-block w-4 h-4 rounded-full mr-2 flex-shrink-0" style="background: {{ $choiceColors[$choice->id] }}"></span>
                                                <span class="break-words text-justify">{{ $choice->choice_text }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                {{-- Pie Chart --}}
                                <div class="md:w-1/2 flex justify-center" wire:ignore>
                                    <canvas id="chart-question-{{ $question->id }}" class="max-w-full h-auto" width="200" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        @push('scripts')
                        <script>
                            let chartInstance{{ $question->id }};

                            function renderChart{{ $question->id }}() {
                                const canvas = document.getElementById('chart-question-{{ $question->id }}');
                                if (!canvas) return;
                                
                                const ctx = canvas.getContext('2d');
                                
                                if (chartInstance{{ $question->id }}) {
                                    chartInstance{{ $question->id }}.destroy();
                                }

                                const choices = @json($question->choices->sortBy('order')->values());
                                const answers = @json($question->answers);
                                const choiceColors = @json($choiceColors);
                                
                                let data = Array(choices.length).fill(0);
                                
                               answers.forEach(answer => {
                                try {
                                    const choiceIds = JSON.parse(answer.answer);
                                    const choiceIdArray = Array.isArray(choiceIds) ? choiceIds : [choiceIds];
                                    
                                    choiceIdArray.forEach(choiceId => {
                                        if (choiceId !== null && !isNaN(parseInt(choiceId))) {
                                            const choiceIndex = choices.findIndex(c => c.id === parseInt(choiceId));
                                            if (choiceIndex !== -1) {
                                                    data[choiceIndex]++;
                                                }
                                            }
                                        });
                                    } catch (e) {
                                        console.error('Error parsing answer:', answer.answer, e);
                                    }
                                });
                                
                                let labels = choices.map(choice => choice.choice_text);
                                let backgroundColor = choices.map(choice => choiceColors[choice.id]);
                                
                                chartInstance{{ $question->id }} = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            data: data,
                                            backgroundColor: backgroundColor,
                                            borderWidth: 0
                                        }]
                                    },
                                    options: {
                                        responsive: false,
                                        plugins: {
                                            legend: { display: false }
                                        }
                                    }
                                });
                            }

                            document.addEventListener('DOMContentLoaded', function () {
                                renderChart{{ $question->id }}();
                            });
                            
                            // Re-render on Livewire updates but only if canvas exists
                            document.addEventListener('livewire:update', function () {
                                if (document.getElementById('chart-question-{{ $question->id }}')) {
                                    renderChart{{ $question->id }}();
                                }
                            });
                        </script>
                        @endpush



                    {{-- FOR LIKERTS --}}
                    @elseif($question->question_type === 'likert')
                    @php
                        // Reindex likert columns: use as-is if already an array, otherwise decode the JSON string.
                        // If decoding fails or returns null/false, fallback to an empty array using the ?: operator.
                        $likertColumns = array_values(is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []));
                        $likertRows = array_values(is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []));
                        
                        // Initialize counts correctly
                        $likertCounts = [];
                        foreach ($likertRows as $rowIdx => $row) {
                            $likertCounts[$rowIdx] = array_fill(0, count($likertColumns), 0);
                        }

                        // Process answers more carefully
                        foreach ($question->answers as $answer) {
                            $decoded = json_decode($answer->answer, true);
                            // Ensure decoded data is an array before proceeding
                            if (is_array($decoded)) {
                                foreach ($decoded as $rowIdx => $colIdx) {
                                    // Check if row index exists and column index is not null and exists
                                    if (isset($likertCounts[$rowIdx]) && $colIdx !== null) {
                                        $colIdx = intval($colIdx); // Ensure it's an integer
                                        if (isset($likertCounts[$rowIdx][$colIdx])) {
                                            $likertCounts[$rowIdx][$colIdx]++;
                                        }
                                    }
                                }
                            }
                        }
                    @endphp

                        <div class="bg-white shadow rounded-lg p-4 sm:p-6 mb-6" wire:key="likert-{{ $question->id }}">
                            {{-- Include the question title and "More Details" button --}}
                            @include('livewire.surveys.form-responses.partials.question-details-button-modal', ['question' => $question, 'questionCounter' => $questionCounter])

                            <div class="overflow-x-auto">
                                <table class="table-auto w-full border-collapse border border-gray-300 mb-4 min-w-max">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2"></th>
                                            @foreach($likertColumns as $column)
                                                <th class="border border-gray-300 px-4 py-2 text-gray-600">{{ $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($likertRows as $rowIdx => $row)
                                            <tr>
                                                <td class="border border-gray-300 px-4 py-2 font-semibold">{{ $row }}</td>
                                                @foreach($likertColumns as $colIdx => $column)
                                                    {{-- Use the calculated counts --}}
                                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ $likertCounts[$rowIdx][$colIdx] ?? 0 }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-center mt-6 overflow-x-auto" wire:ignore>
                                <canvas id="likert-chart-{{ $question->id }}" class="max-w-full" height="{{ 150 * count($likertRows) }}"></canvas>
                            </div>
                        </div>
                        @push('scripts')
                        <script>
                            (function() {
                                let chartInstance = null;
                                
                                function renderLikertChart{{ $question->id }}() {
                                    const canvas = document.getElementById('likert-chart-{{ $question->id }}');
                                    if (!canvas) return;
                                    
                                    const ctx = canvas.getContext('2d');

                                    if (chartInstance) {
                                        chartInstance.destroy();
                                    }

                                    const rows = @json($likertRows);
                                    const columns = @json($likertColumns);
                                    const counts = @json($likertCounts);
                                    const colors = [
                                        @foreach($likertColumns as $i => $column)
                                            "{{ $colors[$i % count($colors)] }}",
                                        @endforeach
                                    ];

                                    const datasets = columns.map((col, colIdx) => ({
                                        label: col,
                                        data: rows.map((row, rowIdx) => counts[rowIdx]?.[colIdx] ?? 0),
                                        backgroundColor: colors[colIdx],
                                        borderWidth: 1
                                    }));

                                    chartInstance = new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: rows,
                                            datasets: datasets
                                        },
                                        options: {
                                            indexAxis: 'y',
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: {
                                                legend: { display: true, position: 'top' }
                                            },
                                            scales: {
                                                x: {
                                                    beginAtZero: true,
                                                    precision: 0,
                                                    ticks: {
                                                        stepSize: 1
                                                    }
                                                },
                                                y: {
                                                    stacked: false
                                                }
                                            }
                                        }
                                    });
                                }
                                
                                document.addEventListener('DOMContentLoaded', renderLikertChart{{ $question->id }});
                                document.addEventListener('livewire:update', function() {
                                    if (document.getElementById('likert-chart-{{ $question->id }}')) {
                                        renderLikertChart{{ $question->id }}();
                                    }
                                });
                            })();
                        </script>
                        @endpush


                    {{-- Handle other types like essay, short_text, date, rating --}}
                    @else
                        <div class="bg-white shadow rounded-lg p-4 sm:p-6 mb-6" wire:key="rating-{{ $question->id }}">
                            {{-- Include the question title and "More Details" button --}}
                            @include('livewire.surveys.form-responses.partials.question-details-button-modal', ['question' => $question, 'questionCounter' => $questionCounter])

                            {{-- FOR RATING--}}
                            @if($question->question_type === 'rating')

                                @php
                                    $starCount = $question->stars ?? 5;
                                    $totalAnswers = $question->answers->count();
                                    $averageRating = $totalAnswers > 0 ? $question->answers->avg('answer') : 0;
                                    $ratingCountsRaw = [];
                                    for ($i = 1; $i <= $starCount; $i++) {
                                        $ratingCountsRaw[] = $question->answers->where('answer', $i)->count();
                                    }
                                    $ratingCounts = array_values($ratingCountsRaw);
                                @endphp

                                <div class="flex flex-col lg:flex-row gap-4 sm:gap-8">

                                    {{-- Left Side: Average Rating --}}
                                    <div class="lg:w-1/3 flex flex-col items-center justify-center lg:border-r border-gray-200 lg:pr-8 pb-4 lg:pb-0">
                                        <div class="text-3xl sm:text-4xl font-bold mb-2">{{ number_format($averageRating, 1) }}</div>
                                        <div class="flex items-center space-x-1 mb-2">
                                            @php $roundedAverage = round($averageRating); @endphp
                                            @for($i = 1; $i <= $starCount; $i++)
                                                <svg class="w-6 h-6 {{ $i <= $roundedAverage ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <div class="text-gray-500 text-xs sm:text-sm text-center">Average Rating ({{ $totalAnswers }} responses)</div>
                                    </div>

                                    {{-- Right Side: Rating Distribution Chart --}}
                                    <div class="lg:w-2/3 relative overflow-x-auto" style="min-height: 150px;" wire:ignore> 
                                        <canvas id="rating-chart-{{ $question->id }}" class="max-w-full"></canvas>
                                    </div>

                                </div>

                                @push('scripts')

                                <script>
                                    (function() {
                                        let chartInstance = null;
                                        
                                        function renderRatingChart{{ $question->id }}() {
                                            const canvas = document.getElementById('rating-chart-{{ $question->id }}');
                                            if (!canvas) return;

                                            if (chartInstance) {
                                                chartInstance.destroy();
                                            }

                                            const labels = [
                                                @for($i = 1; $i <= $starCount; $i++)
                                                    "{{ $i }} Star{{ $i > 1 ? 's' : '' }}",
                                                @endfor
                                            ];
                                            const data = @json($ratingCounts);

                                            chartInstance = new Chart(canvas.getContext('2d'), {
                                                type: 'bar',
                                                data: {
                                                    labels: labels,
                                                    datasets: [{
                                                        label: 'Number of Responses',
                                                        data: data,
                                                        backgroundColor: '#60a5fa',
                                                        borderColor: '#3b82f6',
                                                        borderWidth: 1
                                                    }]
                                                },
                                                options: {
                                                    indexAxis: 'y',
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    plugins: {
                                                        legend: { display: false }
                                                    },
                                                    scales: {
                                                        x: {
                                                            beginAtZero: true,
                                                            precision: 0
                                                        },
                                                        y: {
                                                            ticks: {
                                                                autoSkip: false
                                                            }
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                        
                                        document.addEventListener('DOMContentLoaded', renderRatingChart{{ $question->id }});
                                        document.addEventListener('livewire:update', function() {
                                            if (document.getElementById('rating-chart-{{ $question->id }}')) {
                                                renderRatingChart{{ $question->id }}();
                                            }
                                        });
                                    })();
                                </script>
                                @endpush

                            @else {{-- Display for other non-chart types (essay, short_text, date) --}}
                                <div class="space-y-2">
                                    @php
                                        $answers = $question->answers->sortBy('created_at')->values();

                                        //display limmit for answers shown
                                        $displayLimit = 5;
                                        $answerCount = $answers->count();
                                    @endphp
                                    @forelse($answers->take($displayLimit) as $i => $answer)
                                        <div class="p-3 bg-gray-50 rounded border border-gray-200 break-words overflow-wrap-anywhere">
                                            {{ $answer->answer }}
                                        </div>
                                        @if($i === $displayLimit - 1 && $answerCount > $displayLimit)
                                            <div class="text-center text-gray-400 text-xl font-bold">...</div>
                                        @endif
                                    @empty
                                        <div class="text-gray-400 italic">No responses yet.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif

                    @php $questionCounter++; @endphp {{-- Increment counter after each question block --}}
                @endforeach
            @endforeach
            @else
                <div class="text-gray-500">Survey not found.</div>
            @endif
        @endif
    </div>

    @push('scripts')
    <script>
        // Remove all the Livewire event listeners since we're using direct download now
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Form responses page loaded');
        });
    </script>
    @endpush
</div>
