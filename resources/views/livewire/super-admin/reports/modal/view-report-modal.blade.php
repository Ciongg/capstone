<div>
    @if($report)
        <!-- Report Header - Always visible -->
        <div class="mb-6 flex flex-col">
            <!-- Report Status and ID -->
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-2xl font-bold">Report #{{ $report->id }}</h3>
                    <p class="text-sm text-gray-500">Survey: {{ $report->survey->title ?? 'Unknown Survey' }}</p>
                </div>
            </div>
            
            <!-- Report Details Grid -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center mb-2">
                    <span class="font-bold mr-2">Reason:</span>
                    <span class="px-2 py-1 rounded text-xs {{ 
                        $report->reason === 'inappropriate_content' ? 'bg-red-200 text-red-800' : 
                        ($report->reason === 'spam' ? 'bg-orange-200 text-orange-800' :
                        ($report->reason === 'offensive' ? 'bg-purple-200 text-purple-800' :
                        ($report->reason === 'suspicious' ? 'bg-indigo-200 text-indigo-800' :
                        ($report->reason === 'duplicate' ? 'bg-pink-200 text-pink-800' : 'bg-gray-200 text-gray-800'))))
                    }}">
                        {{ str_replace('_', ' ', ucwords($report->reason)) }}
                    </span>
                </div>
                <div class="flex items-center mb-2">
                    <span class="font-bold mr-2">Status:</span>
                    <span class="px-2 py-1 rounded text-xs {{ 
                        $report->status === 'unappealed' ? 'bg-yellow-200 text-yellow-800' : 
                        ($report->status === 'under_appeal' ? 'bg-blue-200 text-blue-800' : 'bg-green-200 text-green-800')
                    }}">
                        {{ str_replace('_', ' ', ucwords($report->status)) }}
                    </span>
                </div>
                <div class="mb-2">
                    <span class="font-bold">Reported:</span> {{ $report->created_at->format('M d, Y h:i A') }}
                </div>
                <div class="mb-2">
                    <span class="font-bold">Reporter:</span> {{ $report->reporter->name ?? 'Unknown User' }}
                </div>
                <div class="mb-2">
                    <span class="font-bold">Respondent:</span> {{ $report->respondent->name ?? 'Unknown User' }}
                </div>
               
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-4" x-data="{ tab: 'details' }">
            <nav class="flex -mb-px">
                <button 
                    @click="tab = 'details'" 
                    :class="{ 'border-blue-500 text-blue-600': tab === 'details', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'details' }" 
                    class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                >
                    Report Details
                </button>
                <button 
                    @click="tab = 'response'" 
                    :class="{ 'border-blue-500 text-blue-600': tab === 'response', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'response' }" 
                    class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                >
                    Response Data
                </button>
            </nav>
            
            <!-- Tab Content -->
            <div class="pt-4">
                <!-- Report Details Tab -->
                <div x-show="tab === 'details'" x-cloak>
                    <div class="space-y-4">
                        <!-- Report Description -->
                        <div>
                            <h4 class="font-bold mb-2">Report Details</h4>
                            <div class="bg-gray-50 p-4 rounded-lg max-h-40 overflow-y-auto whitespace-pre-wrap">{{ $report->details }}</div>
                        </div>
                        
                        <!-- Users Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h5 class="font-semibold text-blue-800 mb-2">Reporter Information</h5>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">Name:</span> {{ $report->reporter->name ?? 'Unknown' }}</div>
                                    <div><span class="font-medium">Email:</span> {{ $report->reporter->email ?? 'Unknown' }}</div>
                                    <div><span class="font-medium">ID:</span> {{ $report->reporter_id }}</div>
                                </div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <h5 class="font-semibold text-red-800 mb-2">Respondent Information</h5>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">Name:</span> {{ $report->respondent->name ?? 'Unknown' }}</div>
                                    <div><span class="font-medium">Email:</span> {{ $report->respondent->email ?? 'Unknown' }}</div>
                                    <div><span class="font-medium">ID:</span> {{ $report->respondent_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Response Data Tab -->
                <div x-show="tab === 'response'" x-cloak class="space-y-4">
                    <div>
                        <h4 class="font-bold mb-2">Survey Information</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div><span class="font-medium">Survey:</span> {{ $report->survey->title ?? 'Unknown' }}</div>
                                <div><span class="font-medium">Survey ID:</span> {{ $report->survey_id }}</div>
                                <div><span class="font-medium">Response ID:</span> {{ $report->response_id }}</div>
                                <div><span class="font-medium">Report Status:</span> 
                                    <span class="px-2 py-1 rounded text-xs {{ 
                                        $report->status === 'unappealed' ? 'bg-yellow-200 text-yellow-800' : 
                                        ($report->status === 'under_appeal' ? 'bg-blue-200 text-blue-800' : 'bg-green-200 text-green-800')
                                    }}">
                                        {{ str_replace('_', ' ', ucwords($report->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($processedAnswer)
                    <!-- Answer Given Section -->
                    <div>
                        <h4 class="font-bold mb-2">Answer Given</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-3">
                                <div>
                                    <span class="font-medium text-gray-700">Question:</span>
                                    <div class="mt-1 text-gray-900 max-h-20 overflow-y-auto">{{ $processedAnswer['question_text'] }}</div>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700">Answer:</span>
                                    <div class="mt-1 p-3 bg-white border rounded-md max-h-48 overflow-y-auto">
                                        @if($processedAnswer['question_type'] === 'rating')
                                            <div class="flex items-center">
                                                @php
                                                    $rating = (int) ($processedAnswer['raw_answer'] ?? 0);
                                                    $maxStars = $report->question->stars ?? 5;
                                                @endphp
                                                @for ($i = 1; $i <= $maxStars; $i++)
                                                    <svg class="w-5 h-5 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <polygon points="10,1 12.59,7.36 19.51,7.64 14,12.26 15.82,19.02 10,15.27 4.18,19.02 6,12.26 0.49,7.64 7.41,7.36" />
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-gray-600">({{ $processedAnswer['display_answer'] }})</span>
                                            </div>
                                        @elseif(isset($processedAnswer['is_likert']) && $processedAnswer['is_likert'])
                                            {{-- Special formatting for Likert scale answers --}}
                                            <div class="space-y-2">
                                                @foreach(explode("\n", $processedAnswer['display_answer']) as $response)
                                                    @if(trim($response))
                                                        <div class="flex items-start">
                                                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
                                                            <div class="text-gray-900 break-words flex-1">
                                                                @php
                                                                    $parts = explode(': ', $response, 2);
                                                                @endphp
                                                                @if(count($parts) === 2)
                                                                    <span class="font-medium text-gray-800">{{ $parts[0] }}:</span>
                                                                    <span class="text-gray-700 ml-2">{{ $parts[1] }}</span>
                                                                @else
                                                                    <span class="text-gray-700">{{ $response }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif(strlen($processedAnswer['display_answer']) > 100)
                                            <div class="whitespace-pre-wrap break-words">{{ $processedAnswer['display_answer'] }}</div>
                                        @else
                                            <span class="text-gray-900 break-words">{{ $processedAnswer['display_answer'] }}</span>
                                        @endif
                                        
                                        @if(isset($processedAnswer['other_text']))
                                            <div class="mt-2 pt-2 border-t border-gray-200">
                                                <span class="font-medium text-gray-700">Other Response:</span>
                                                <div class="text-gray-900 break-words">{{ $processedAnswer['other_text'] }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-xs text-gray-500">
                                    <span class="font-medium">Question Type:</span> {{ ucfirst(str_replace('_', ' ', $processedAnswer['question_type'])) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($report->question_id)
                    <!-- No Answer Found -->
                    <div>
                        <h4 class="font-bold mb-2">Answer Given</h4>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="text-yellow-800">No answer found for the reported question</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div>
                        <div class="flex space-x-2">
                            <a href="{{ route('surveys.responses.view', ['survey' => $report->survey_id, 'response' => $report->response_id]) }}" 
                               target="_blank"
                               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                                View Full Response
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            <div class="flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p>Loading report details...</p>
            </div>
        </div>
    @endif
</div>
