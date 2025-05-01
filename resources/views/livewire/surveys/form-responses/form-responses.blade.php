{{-- filepath: resources/views/livewire/surveys/form-responses/form-responses.blade.php --}}
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto space-y-10 px-4">

        <div class="flex flex-col md:flex-row md:justify-end md:items-center gap-4 mb-6">
            <a href="{{ route('surveys.responses.individual', $survey->id) }}"
                wire:navigate
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                View Individual Responses
            </a>
            <button
                wire:click="deleteAllResponses"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                onclick="return confirm('Are you sure you want to delete all responses for this survey?')"
            >
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
        @endphp

        @if(isset($survey))
            @foreach($survey->pages as $page)
                @foreach($page->questions as $question)
                    @if(in_array($question->question_type, ['multiple_choice', 'radio']))
                        <div class="bg-white shadow rounded-2xl p-8 mb-8 flex flex-col md:flex-row md:items-center md:justify-between relative">
                            {{-- Legend --}}
                            <div class="md:w-1/2 mb-6 md:mb-0">
                                <div class="font-semibold mb-2 text-lg">{{ $question->question_text }}</div>
                                <ul>
                                    @foreach($question->choices as $i => $choice)
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
                        </div>
                        @push('scripts')
                        <script>
                            let chartInstance{{ $question->id }};

                            function renderChart{{ $question->id }}() {
                                const ctx = document.getElementById('chart-question-{{ $question->id }}').getContext('2d');
                                if (chartInstance{{ $question->id }}) {
                                    chartInstance{{ $question->id }}.destroy();
                                }

                                let data = [
                                    @foreach($question->choices as $choice)
                                        {{ $question->answers->where('answer', $choice->choice_text)->count() }},
                                    @endforeach
                                ];
                                let labels = [
                                    @foreach($question->choices as $choice)
                                        "{{ $choice->choice_text }}",
                                    @endforeach
                                ];
                                let backgroundColor = [
                                    @foreach($question->choices as $i => $choice)
                                        "{{ $colors[$i % count($colors)] }}",
                                    @endforeach
                                ];

                                // If only one non-zero value, add a transparent dummy slice
                                let nonZero = data.filter(v => v > 0).length;
                                if (nonZero === 1) {
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
                    @else
                        <div class="bg-white shadow rounded-lg p-6 mb-6">
                            <div class="font-semibold mb-2">{{ $question->question_text }}</div>
                            <div class="space-y-2">
                                @forelse($question->answers as $answer)
                                    <div class="p-3 bg-gray-50 rounded border border-gray-200">
                                        {{ $answer->answer }}
                                    </div>
                                @empty
                                    <div class="text-gray-400 italic">No responses yet.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                @endforeach
            @endforeach
        @else
            <div class="text-gray-500">Survey not found.</div>
        @endif
    </div>
</div>
