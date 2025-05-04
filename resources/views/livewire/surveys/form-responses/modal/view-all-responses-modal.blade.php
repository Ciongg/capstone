<div>
    <div class="flex items-center justify-between mb-4">
        <span class="text-lg font-semibold">All Responses</span>
        <span class="text-blue-600 font-semibold text-lg">
            {{ $question->answers->unique('response_id')->count() }} responses
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded">
            
            {{-- Simple Case: Essay, Short Text, Date, Rating --}}
            @if(in_array($question->question_type, ['essay', 'short_text', 'date', 'rating']))
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-b text-left">Respondent ID</th>
                        <th class="px-4 py-2 border-b text-left">Response</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($question->answers as $answer)
                        <tr>
                            <td class="px-4 py-2 border-b">
                                {{ $answer->response?->user_id ?? '-' }} 
                            </td>
                            <td class="px-4 py-2 border-b">
                                {{ $answer->answer }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-center text-gray-400">No responses yet.</td>
                        </tr>
                    @endforelse
                </tbody>

            {{-- Multiple Choice / Radio Case --}}
            @elseif(in_array($question->question_type, ['multiple_choice', 'radio']))
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-b text-left">Respondent ID</th>
                        <th class="px-4 py-2 border-b text-left">Response(s)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Group answers by respondent ID --}}
                    @php $groupedAnswers = $question->answers->groupBy('response_id'); @endphp
                    @forelse($groupedAnswers as $responseId => $answers)
                        <tr>
                            <td class="px-4 py-2 border-b">
                                {{-- Find the user ID from the first answer in the group --}}
                                {{ $answers->first()?->response?->user_id ?? '-' }}
                            </td>
                            <td class="px-4 py-2 border-b">
                                {{-- Join all answers for this respondent for this question --}}
                                {{ $answers->pluck('answer')->implode(', ') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-center text-gray-400">No responses yet.</td>
                        </tr>
                    @endforelse
                </tbody>

            {{-- Likert Case --}}
            @elseif($question->question_type === 'likert')
                @php
                    $likertRows = array_values(is_array($question->likert_rows) ? $question->likert_rows : (json_decode($question->likert_rows, true) ?: []));
                    $likertColumns = array_values(is_array($question->likert_columns) ? $question->likert_columns : (json_decode($question->likert_columns, true) ?: []));
                    $groupedAnswers = $question->answers->groupBy('response_id');
                @endphp
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-b text-left">Respondent ID</th>
                        {{-- Add a column header for each Likert statement (row) --}}
                        @foreach($likertRows as $rowText)
                            <th class="px-4 py-2 border-b text-left">{{ $rowText }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedAnswers as $responseId => $answers)
                        <tr>
                            <td class="px-4 py-2 border-b">
                                {{ $answers->first()?->response?->user_id ?? '-' }}
                            </td>
                            @php
                                // Decode the JSON answer for this respondent (should only be one answer record per respondent for a likert question)
                                $likertAnswerData = json_decode($answers->first()?->answer, true);
                            @endphp
                            {{-- Loop through the statements (rows) again to display the selected option --}}
                            @foreach($likertRows as $rowIdx => $rowText)
                                <td class="px-4 py-2 border-b">
                                    @php
                                        // Get the index of the selected column/option for this row
                                        $selectedColIdx = $likertAnswerData[$rowIdx] ?? null;
                                    @endphp
                                    {{-- Display the text of the selected column/option --}}
                                    {{ ($selectedColIdx !== null && isset($likertColumns[$selectedColIdx])) ? $likertColumns[$selectedColIdx] : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            {{-- Adjust colspan based on number of statements + 1 for Respondent ID --}}
                            <td colspan="{{ count($likertRows) + 1 }}" class="px-4 py-2 text-center text-gray-400">No responses yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            @endif
            
        </table>
    </div>
</div>
