{{-- filepath: resources/views/livewire/surveys/form-responses/form-responses.blade.php --}}
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto space-y-10 px-4">

        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
            <a href="{{ route('surveys.create', $survey->id) }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Editor
            </a>
            <div class="flex-grow"></div>
            
            {{-- Preview Survey Button --}}
            <a href="{{ route('surveys.preview', $survey->id) }}"
               class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 flex items-center"
               target="_blank"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                </svg>
                Preview
            </a>
            
            {{-- View Individual Responses Button --}}
            <a href="{{ route('surveys.responses.individual', $survey->id) }}"
               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"></path>
                </svg>
                View Individual Responses
            </a>
            
            {{-- Delete All Responses Button --}}
            <button
                wire:click="deleteAllResponses"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 flex items-center"
                onclick="return confirm('Are you sure you want to delete all responses for this survey?')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                </svg>
                Delete All Responses
            </button>
        </div>

        {{-- Top summary containers --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
                <span class="text-lg font-semibold">Responses</span>
                <span class="text-2xl text-blue-600 font-bold mt-2">
                    {{ $survey->responses()->count() }}
                </span>
            </div>
            <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
                <span class="text-lg font-semibold">Average Time</span>
                <span class="text-2xl text-blue-600 font-bold mt-2">--</span>
            </div>
            <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
                <span class="text-lg font-semibold">Points</span>
                <span class="text-2xl text-blue-600 font-bold mt-2">--</span>
            </div>
        </div>

        @php
            $colors = ['#60a5fa', '#fbbf24', '#34d399', '#f87171', '#a78bfa', '#f472b6', '#facc15', '#38bdf8'];
            $questionCounter = 1; // Initialize question counter
        @endphp

        @if(isset($survey))
            @foreach($survey->pages as $page)
                {{-- Ensure questions are sorted by their order within the page --}}
                @foreach($page->questions->sortBy('order') as $question) 
                    @php $modalName = 'view-all-responses-modal' . $question->id; @endphp

                    @if(in_array($question->question_type, ['multiple_choice', 'radio']))
                        <div class="bg-white shadow rounded-2xl p-8 mb-8 flex flex-col md:flex-row md:items-center md:justify-between relative">
                            {{-- "More Details" button on top right --}}
                            <div class="absolute top-4 right-4">
                                <button
                                    x-data
                                    x-on:click="$dispatch('open-modal', {name : '{{ $modalName }}'})"
                                    class="text-blue-600 underline font-semibold hover:text-blue-800 text-sm"
                                    type="button"
                                >
                                    More Details
                                </button>
                            </div>

                            {{-- Legend --}}
                            <div class="md:w-1/2 mb-6 md:mb-0">
                                {{-- Display question number and text --}}
                                <div class="font-semibold mb-2 text-lg">{{ $questionCounter }}. {{ $question->question_text }}</div>
                                <ul>
                                    @foreach($question->choices->sortBy('order') as $i => $choice) {{-- Also sort choices if needed --}}
                                        <li class="flex items-center mb-2">
                                            <span class="inline-block w-4 h-4 rounded-full mr-2" style="background: {{ $colors[$i % count($colors)] }}"></span>
                                            <span>{{ $choice->choice_text }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            {{-- Pie Chart --}}
                            <div class="md:w-1/2 flex justify-center">
                                <canvas id="chart-question-{{ $question->id }}" width="200" height="200"></canvas>
                            </div>

                            {{-- Modal for all responses to this question --}}
                            <x-modal name="{{ $modalName }}" title="All Responses">
                                <livewire:surveys.form-responses.modal.view-all-responses-modal :question="$question" />
                            </x-modal>
                        </div>
                        @push('scripts')
                        <script>
                            let chartInstance{{ $question->id }};

                            function renderChart{{ $question->id }}() {
                                const ctx = document.getElementById('chart-question-{{ $question->id }}').getContext('2d');
                                if (chartInstance{{ $question->id }}) {
                                    chartInstance{{ $question->id }}.destroy();
                                }

                                // --- Corrected Data Calculation ---
                                const choices = @json($question->choices->sortBy('order')->values());
                                const answers = @json($question->answers);
                                
                                // Initialize counts for each choice
                                let data = Array(choices.length).fill(0);
                                
                                // Process each answer
                                answers.forEach(answer => {
                                    try {
                                        // Parse the JSON string to get choice IDs array
                                        const choiceIds = JSON.parse(answer.answer);
                                        
                                        // For multiple choice, answer is an array of choice IDs
                                        if (Array.isArray(choiceIds)) {
                                            choiceIds.forEach(choiceId => {
                                                // Find the index of this choice ID in our choices array
                                                const choiceIndex = choices.findIndex(c => c.id === parseInt(choiceId));
                                                if (choiceIndex !== -1) {
                                                    data[choiceIndex]++;
                                                }
                                            });
                                        } 
                                        // For radio buttons, answer is a single choice ID
                                        else if (!isNaN(parseInt(choiceIds))) {
                                            const choiceIndex = choices.findIndex(c => c.id === parseInt(choiceIds));
                                            if (choiceIndex !== -1) {
                                                data[choiceIndex]++;
                                            }
                                        }
                                    } catch (e) {
                                        console.error('Error parsing answer:', answer.answer, e);
                                    }
                                });

                                let labels = choices.map(choice => choice.choice_text);
                                let backgroundColor = [
                                    @foreach($question->choices->sortBy('order') as $i => $choice)
                                        "{{ $colors[$i % count($colors)] }}",
                                    @endforeach
                                ];

                                // If only one non-zero value, add a transparent dummy slice
                                let nonZero = data.filter(v => v > 0).length;
                                if (nonZero === 1 && data.length > 1) {
                                    const zeroIndex = data.findIndex(v => v === 0);
                                    if (zeroIndex !== -1) {
                                        data[zeroIndex] = 0.00001;
                                    } else {
                                        data.push(0.00001);
                                        labels.push('dummy');
                                        backgroundColor.push('rgba(0,0,0,0)');
                                    }
                                } else if (data.length === 1 && data[0] > 0) {
                                    data.push(0.00001);
                                    labels.push('dummy');
                                    backgroundColor.push('rgba(0,0,0,0)');
                                }

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
                        </script>
                        @endpush
                    @elseif($question->question_type === 'likert')
                    @php
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

                        <div class="bg-white shadow rounded-lg p-6 mb-6 relative">
                            {{-- "More Details" button on top right --}}
                            <div class="absolute top-4 right-4">
                                <button
                                    x-data
                                    x-on:click="$dispatch('open-modal', {name : '{{ $modalName }}'})"
                                    class="text-blue-600 underline font-semibold hover:text-blue-800 text-sm"
                                    type="button"
                                >
                                    More Details
                                </button>
                            </div>

                            {{-- Display question number and text --}}
                            <div class="font-semibold mb-2 text-lg">{{ $questionCounter }}. {{ $question->question_text }}</div>
                            <div class="overflow-x-auto">
                                <table class="table-auto w-full border-collapse border border-gray-300 mb-4">
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
                            <div class="flex justify-center mt-6">
                                <canvas id="likert-chart-{{ $question->id }}" height="{{ 150 * count($likertRows) }}"></canvas>
                            </div>

                            {{-- Modal for all responses to this question --}}
                            <x-modal name="{{ $modalName }}" title="All Responses">
                                <livewire:surveys.form-responses.modal.view-all-responses-modal :question="$question" />
                            </x-modal>
                        </div>
                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const ctx = document.getElementById('likert-chart-{{ $question->id }}').getContext('2d');
                                const rows = @json($likertRows);
                                const columns = @json($likertColumns);
                                const counts = @json($likertCounts); // Pass the correctly calculated counts
                                const colors = [
                                    @foreach($likertColumns as $i => $column)
                                        "{{ $colors[$i % count($colors)] }}",
                                    @endforeach
                                ];

                                // Each dataset is a Likert option (column)
                                const datasets = columns.map((col, colIdx) => ({
                                    label: col,
                                    // Ensure data mapping uses the correct counts array structure
                                    data: rows.map((row, rowIdx) => counts[rowIdx]?.[colIdx] ?? 0),
                                    backgroundColor: colors[colIdx],
                                    borderWidth: 1
                                }));

                                new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: rows,
                                        datasets: datasets
                                    },
                                    options: {
                                        indexAxis: 'y', // horizontal bars
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: true, position: 'top' }
                                        },
                                        scales: {
                                            x: {
                                                beginAtZero: true,
                                                precision: 0, // Ensure whole numbers on axis
                                                ticks: {
                                                    stepSize: 1 // Force step size of 1
                                                }
                                            },
                                            y: {
                                                stacked: false
                                            }
                                        }
                                    }
                                });
                            });
                        </script>
                        @endpush
                    @else {{-- Handle other types like essay, short_text, date, rating --}}
                        <div class="bg-white shadow rounded-lg p-6 mb-6 relative">
                            {{-- "More Details" button on top right --}}
                            <div class="absolute top-4 right-4">
                                <button
                                    x-data
                                    x-on:click="$dispatch('open-modal', {name : '{{ $modalName }}'})"
                                    class="text-blue-600 underline font-semibold hover:text-blue-800 text-sm"
                                    type="button"
                                >
                                    More Details
                                </button>
                            </div>

                            {{-- Display question number and text --}}
                            <div class="font-semibold mb-4 text-lg">{{ $questionCounter }}. {{ $question->question_text }}</div>

                            @if($question->question_type === 'rating')
                                @php
                                    $starCount = $question->stars ?? 5;
                                    $totalAnswers = $question->answers->count();
                                    $averageRating = $totalAnswers > 0 ? $question->answers->avg('answer') : 0;
                                    // Use array_values to ensure 0-based indexing for Chart.js (like likert fix)
                                    $ratingCountsRaw = [];
                                    for ($i = 1; $i <= $starCount; $i++) {
                                        $ratingCountsRaw[] = $question->answers->where('answer', $i)->count();
                                    }
                                    $ratingCounts = array_values($ratingCountsRaw);
                                @endphp
                                <div class="flex flex-col md:flex-row gap-8">
                                    {{-- Left Side: Average Rating --}}
                                    <div class="md:w-1/3 flex flex-col items-center justify-center border-r border-gray-200 pr-8">
                                        <div class="text-4xl font-bold mb-2">{{ number_format($averageRating, 1) }}</div>
                                        <div class="flex items-center space-x-1 mb-2">
                                            @php $roundedAverage = round($averageRating); @endphp
                                            @for($i = 1; $i <= $starCount; $i++)
                                                <svg class="w-6 h-6 {{ $i <= $roundedAverage ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <div class="text-gray-500 text-sm">Average Rating ({{ $totalAnswers }} responses)</div>
                                    </div>

                                    {{-- Right Side: Rating Distribution Chart --}}
                                    <div class="md:w-2/3 relative" style="min-height: 150px;"> 
                                        <canvas id="rating-chart-{{ $question->id }}"></canvas>
                                    </div>
                                </div>
                                @push('scripts')
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const ctx = document.getElementById('rating-chart-{{ $question->id }}');
                                        if (!ctx) return;

                                        // Use array_values to ensure 0-based indexing (like likert)
                                        const labels = [
                                            @for($i = 1; $i <= $starCount; $i++)
                                                "{{ $i }} Star{{ $i > 1 ? 's' : '' }}",
                                            @endfor
                                        ];
                                        const data = @json($ratingCounts);

                                        new Chart(ctx.getContext('2d'), {
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
                                    });
                                </script>
                                @endpush

                            @else {{-- Display for other non-chart types (essay, short_text, date) --}}
                                <div class="space-y-2">
                                    @php
                                        $answers = $question->answers->sortBy('created_at')->values();
                                        $displayLimit = 5;
                                        $answerCount = $answers->count();
                                    @endphp
                                    @forelse($answers->take($displayLimit) as $i => $answer)
                                        <div class="p-3 bg-gray-50 rounded border border-gray-200 overflow-hidden text-ellipsis whitespace-nowrap">
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

                            {{-- Modal for all responses to this question --}}
                            <x-modal name="{{ $modalName }}" title="All Responses">
                                <livewire:surveys.form-responses.modal.view-all-responses-modal :question="$question" />
                            </x-modal>
                        </div>
                    @endif
                    @php $questionCounter++; @endphp {{-- Increment counter after each question block --}}
                @endforeach
            @endforeach
        @else
            <div class="text-gray-500">Survey not found.</div>
        @endif
    </div>
</div>
