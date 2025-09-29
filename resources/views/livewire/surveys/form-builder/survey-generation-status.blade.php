<div wire:poll.{{ $refreshInterval }}ms="refreshJob">
    @if($job)
        <div class="p-4 mb-4 rounded {{ $this->getStatusColorClass() }}">
            <div class="flex items-center">
                <div class="mr-2">
                    @if($job->status === 'pending' || $job->status === 'processing')
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    @elseif($job->status === 'completed')
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @elseif($job->status === 'failed')
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    @if($job->status === 'pending')
                        <p class="font-semibold">Survey Generation Queued</p>
                        <p class="text-sm">Your survey is queued for generation and will start soon.</p>
                    @elseif($job->status === 'processing')
                        <p class="font-semibold">Generating Survey</p>
                        <p class="text-sm">Your survey is being generated. This may take a few minutes.</p>
                    @elseif($job->status === 'completed')
                        <p class="font-semibold">Survey Generation Complete</p>
                        <p class="text-sm">
                            @if(isset(json_decode($job->result, true)['message']))
                                {{ json_decode($job->result, true)['message'] }}
                            @else
                                Your survey has been successfully generated.
                            @endif
                        </p>
                    @elseif($job->status === 'failed')
                        <p class="font-semibold">Survey Generation Failed</p>
                        <p class="text-sm">
                            @php 
                                $errorMessage = json_decode($job->result, true)['message'] ?? 'There was an error generating your survey.';
                                $isJsonError = strpos($errorMessage, 'Invalid survey structure') !== false ||
                                              strpos($errorMessage, 'Failed to generate survey structure') !== false;
                            @endphp
                            
                            {{ $errorMessage }}
                            
                            @if($isJsonError)
                                <br><span class="mt-1 text-xs italic">Tip: Try using a shorter, clearer description focused on the key survey topics.</span>
                            @endif
                        </p>
                    @endif
                </div>
                
                @if($job->status === 'completed')
                    <button 
                        wire:click="applyChanges"
                        class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        Apply Changes
                    </button>
                @endif

                @if($job->status === 'failed')
                    <button 
                        wire:click="retryWithShorterAbstract"
                        class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Retry
                    </button>
                    
                    @if(auth()->user() && auth()->user()->is_admin)
                        <button 
                            wire:click="viewDebugData"
                            class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-purple-700 bg-purple-50 hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                        >
                            Debug
                        </button>
                    @endif
                @endif
                
                @if($job->status === 'pending' || $job->status === 'processing')
                    <button 
                        wire:click="cancelJob"
                        class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Cancel
                    </button>
                @endif
                
                <button 
                    wire:click="dismissStatus"
                    class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                >
                    Dismiss
                </button>
            </div>
        </div>
    @endif

    <!-- Debug Modal (will be controlled by JS) -->
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('showDebugModal', (data) => {
                // Create a modal to show debug info - this assumes Alpine.js and your site's modal system
                // You might need to adjust this to match your actual modal implementation
                if (typeof showModal === 'function') { // If you have a global modal function
                    showModal(data.title, '<pre class="whitespace-pre-wrap text-sm overflow-auto max-h-96">' + data.content + '</pre>');
                } else {
                    // Fallback: use alert with limited content
                    alert('Debug data (first 1000 chars of ' + data.fullLength + ' total):\n\n' + data.content.substring(0, 1000) + '...');
                }
            });
        });
    </script>
</div>

