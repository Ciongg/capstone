<div class="w-full max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
        <span class="text-lg font-semibold">All Responses</span>
        <span class="text-blue-600 font-semibold text-lg">
            {{ $question->answers->unique('response_id')->count() }} responses
        </span>
    </div>
    
    {{-- Scrollable Table Container --}}
    <div class="overflow-x-auto overflow-y-auto max-h-[50vh] sm:max-h-[200px] mb-6 border rounded-lg">
        <table class="min-w-full border border-gray-200 text-sm">
            
            {{-- Simple Case: Essay, Short Text, Date, Rating --}}
            @if(in_array($question->question_type, ['essay', 'short_text', 'date', 'rating']))
                <thead class="sticky top-0 bg-gray-100">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 border-b text-left whitespace-nowrap">Respondent ID</th>
                        <th class="px-2 sm:px-4 py-2 border-b text-left">Response</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($question->answers as $answer)
                        <tr>
                            <td class="px-2 sm:px-4 py-2 border-b whitespace-nowrap">
                                {{ $answer->response?->user_id ?? '-' }} 
                            </td>
                            <td class="px-2 sm:px-4 py-2 border-b break-words max-w-xs sm:max-w-none">
                                {{ $answer->answer }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-2 sm:px-4 py-2 text-center text-gray-400">No responses yet.</td>
                        </tr>
                    @endforelse
                </tbody>

            {{-- Multiple Choice / Radio Case --}}
            @elseif(in_array($question->question_type, ['multiple_choice', 'radio']))
                <thead class="sticky top-0 bg-gray-100">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 border-b text-left whitespace-nowrap">Respondent ID</th>
                        <th class="px-2 sm:px-4 py-2 border-b text-left">Response(s)</th>
                        <th class="px-2 sm:px-4 py-2 border-b text-left">"Other" Text</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Group answers by respondent ID --}}
                    @php $groupedAnswers = $question->answers->groupBy('response_id'); @endphp
                    @forelse($groupedAnswers as $responseId => $answers)
                        <tr>
                            <td class="px-2 sm:px-4 py-2 border-b whitespace-nowrap">
                                {{-- Find the user ID from the first answer in the group --}}
                                {{ $answers->first()?->response?->user_id ?? '-' }}
                            </td>
                            <td class="px-2 sm:px-4 py-2 border-b break-words max-w-xs sm:max-w-none">
                                @php
                                    // For multiple choice/radio answers: convert JSON array to readable text
                                    $choiceTexts = [];
                                    $hasOtherChoice = false;
                                    $otherText = null;
                                    
                                    foreach ($answers as $answer) {
                                        try {
                                            // Decode the JSON answer to get choice IDs
                                            $choiceIds = json_decode($answer->answer, true);
                                            
                                            // Handle multiple choice (array of IDs)
                                            if (is_array($choiceIds)) {
                                                foreach ($choiceIds as $choiceId) {
                                                    // Find the choice by ID and get its text
                                                    $choice = $question->choices->firstWhere('id', $choiceId);
                                                    if ($choice) {
                                                        $choiceTexts[] = $choice->choice_text;
                                                        
                                                        // Check if this is an "Other" choice
                                                        if ($choice->is_other && !empty($answer->other_text)) {
                                                            $hasOtherChoice = true;
                                                            $otherText = $answer->other_text;
                                                        }
                                                    }
                                                }
                                            } 
                                            // Handle radio (single ID)
                                            else {
                                                $choice = $question->choices->firstWhere('id', $choiceIds);
                                                if ($choice) {
                                                    $choiceTexts[] = $choice->choice_text;
                                                    
                                                    // Check if this is an "Other" choice
                                                    if ($choice->is_other && !empty($answer->other_text)) {
                                                        $hasOtherChoice = true;
                                                        $otherText = $answer->other_text;
                                                    }
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            $choiceTexts[] = 'Error: ' . $e->getMessage();
                                        }
                                    }
                                @endphp
                                {{-- Join the choice texts into a comma-separated string --}}
                                {{ implode(', ', $choiceTexts) }}
                            </td>
                            <td class="px-2 sm:px-4 py-2 border-b break-words max-w-xs sm:max-w-none">
                                {{ $hasOtherChoice ? $otherText : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-2 sm:px-4 py-2 text-center text-gray-400">No responses yet.</td>
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
                <thead class="sticky top-0 bg-gray-100">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 border-b text-left whitespace-nowrap">Respondent ID</th>
                        {{-- Add a column header for each Likert statement (row) --}}
                        @foreach($likertRows as $rowText)
                            <th class="px-2 sm:px-4 py-2 border-b text-left min-w-[120px]">{{ $rowText }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedAnswers as $responseId => $answers)
                        <tr>
                            <td class="px-2 sm:px-4 py-2 border-b whitespace-nowrap">
                                {{ $answers->first()?->response?->user_id ?? '-' }}
                            </td>
                            @php
                                // Decode the JSON answer for this respondent (should only be one answer record per respondent for a likert question)
                                $likertAnswerData = json_decode($answers->first()?->answer, true);
                            @endphp
                            {{-- Loop through the statements (rows) again to display the selected option --}}
                            @foreach($likertRows as $rowIdx => $rowText)
                                <td class="px-2 sm:px-4 py-2 border-b break-words">
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
                            <td colspan="{{ count($likertRows) + 1 }}" class="px-2 sm:px-4 py-2 text-center text-gray-400">No responses yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            @endif
            
        </table>
    </div>

    {{-- Response Summary Section (for multiple choice, radio, and likert) --}}
    @if(in_array($question->question_type, ['multiple_choice', 'radio', 'likert', 'rating', 'date']))
        <div class="mt-6 sm:mt-8">
            <div class="flex items-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span class="text-base sm:text-lg font-semibold">Response Summary</span>
            </div>
            
            <div class="relative">
                <textarea 
                    class="w-full h-32 sm:h-40 p-3 border border-gray-300 rounded-lg text-sm sm:text-base bg-gray-50" 
                    readonly
                >{{ $exactCounts }}</textarea>
                
                {{-- Copy to clipboard button --}}
                <button 
                    class="absolute top-2 right-2 p-2 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors duration-200 text-gray-700"
                    x-data="{ copied: false }"
                    x-on:click="
                        navigator.clipboard.writeText($el.previousElementSibling.value)
                            .then(() => {
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            })
                            .catch(() => {
                                $el.previousElementSibling.select();
                                document.execCommand('copy');
                                window.getSelection().removeAllRanges();
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            });
                    "
                    title="Copy to clipboard"
                    type="button"
                >
                    <template x-if="!copied">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                    </template>
                    <template x-if="copied">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </template>
                </button>
            </div>
        </div>
    @endif

    {{-- AI Summarization Section --}}
    <div class="mt-6 sm:mt-8">
        <div class="flex items-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
            <span class="text-base sm:text-lg font-semibold">Summarize With AI</span>
        </div>
        
        <div class="space-y-4" x-data="{ 
            copied: false,
            copyText() {
                const textarea = this.$refs.summaryTextarea;
                if (!textarea.value) return;
                
                navigator.clipboard.writeText(textarea.value)
                    .then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    })
                    .catch(() => {
                        // Fallback method that requires selection
                        textarea.select();
                        document.execCommand('copy');
                        // Clear selection immediately after
                        window.getSelection().removeAllRanges();
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
            }
        }">
            <div class="relative">
                <textarea 
                    class="w-full h-48 sm:h-64 p-3 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm sm:text-base bg-gray-50" 
                    placeholder="Click 'Generate' to create an AI summary of the responses..."
                    wire:model="aiSummary"
                    id="aiSummaryTextarea-{{ $question->id }}"
                    wire:key="aiSummaryTextarea-{{ $question->id }}-{{ md5($aiSummary) }}"
                    x-ref="summaryTextarea"
                    readonly
                >{{ $aiSummary }}</textarea>
                
                {{-- Copy to clipboard button with Alpine.js --}}
                <button 
                    class="absolute top-2 right-2 p-2 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors duration-200 text-gray-700"
                    @click="copyText"
                    title="Copy to clipboard"
                    type="button"
                >
                    <template x-if="!copied">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                    </template>
                    <template x-if="copied">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </template>
                </button>
            </div>
            
            <div class="flex justify-end items-center space-x-2">
                {{-- AI Model Selector as a select dropdown --}}
                <select wire:model="selectedModel" class="px-2 py-1 border rounded text-sm focus:ring focus:ring-blue-200 focus:border-blue-500">
                    <option value="deepseek">DeepSeek</option>
                    <option value="gemini">Gemini</option>
                </select>
                <button 
                    class="px-4 sm:px-5 py-2 font-medium rounded-md text-white text-sm sm:text-base flex items-center justify-center"
                    style="background-color: #03b8ff;"
                    wire:click="generateSummary"
                    wire:loading.class="opacity-75"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="generateSummary">Generate</span>
                    <span wire:loading wire:target="generateSummary">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Generating...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>