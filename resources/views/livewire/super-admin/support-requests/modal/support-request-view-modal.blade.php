<div>
    @if($supportRequest)
        <!-- Support Request Header - Always visible -->
        <div class="mb-6 flex flex-col">
            <!-- Request Subject and Status -->
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-2xl font-bold">{{ $supportRequest->subject }}</h3>
                    <p class="text-sm text-gray-500">Submitted by: {{ $supportRequest->user->name ?? 'Unknown User' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm {{ 
                    $supportRequest->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 
                    ($supportRequest->status === 'in_progress' ? 'bg-purple-200 text-purple-800' :
                    ($supportRequest->status === 'resolved' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'))
                }}">
                    {{ str_replace('_', ' ', ucfirst($supportRequest->status)) }}
                </span>
            </div>
            
            <!-- Request Type and Timing -->
            <div class="mt-4 flex flex-wrap justify-between">
                <div class="flex items-center mb-2">
                    <span class="font-bold mr-2">Type:</span>
                    <span class="px-2 py-1 rounded text-xs {{ 
                        $supportRequest->request_type === 'survey_lock_appeal' ? 'bg-indigo-200 text-indigo-800' : 
                        ($supportRequest->request_type === 'report_appeal' ? 'bg-pink-200 text-pink-800' :
                        ($supportRequest->request_type === 'account_issue' ? 'bg-orange-200 text-orange-800' :
                        ($supportRequest->request_type === 'survey_question' ? 'bg-teal-200 text-teal-800' : 'bg-gray-200 text-gray-800')))
                    }}">
                        {{ str_replace('_', ' ', ucfirst($supportRequest->request_type)) }}
                    </span>
                </div>
                <div class="mb-2">
                    <span class="font-bold">Submitted:</span> {{ $supportRequest->created_at->format('M d, Y h:i A') }}
                </div>
                @if($supportRequest->resolved_at)
                <div class="mb-2">
                    <span class="font-bold">Resolved:</span> {{ $supportRequest->resolved_at->format('M d, Y h:i A') }}
                </div>
                @endif
                @if($supportRequest->admin)
                <div class="mb-2">
                    <span class="font-bold">Handled by:</span> {{ $supportRequest->admin->name ?? 'Unknown' }}
                </div>
                @endif
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
                    Request Details
                </button>
                <button 
                    @click="tab = 'response'" 
                    :class="{ 'border-blue-500 text-blue-600': tab === 'response', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'response' }" 
                    class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                >
                    Admin Response
                </button>
            </nav>
            
            <!-- Tab Content -->
            <div class="pt-4">
                <!-- Request Details Tab -->
                <div x-show="tab === 'details'" x-cloak>
                    <div class="space-y-6">
                        <!-- Subject -->
                        <div class="bg-white border rounded-lg p-4">
                            <h4 class="font-bold text-lg mb-2 text-gray-800">Subject</h4>
                            <div class="text-gray-900 break-words whitespace-pre-wrap">{{ $supportRequest->subject }}</div>
                        </div>
                        
                        <!-- Description -->
                        <div class="bg-gray-50 border rounded-lg p-4">
                            <h4 class="font-bold text-lg mb-2 text-gray-800">Description</h4>
                            <div class="whitespace-pre-wrap text-gray-700 break-words">{{ $supportRequest->description }}</div>
                        </div>
                        
                        <!-- Related Information -->
                        @if($supportRequest->related_id && $supportRequest->related_model)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-bold text-lg mb-3 text-blue-800">Related Information</h4>
                            <div class="space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center">
                                    <span class="font-medium text-blue-700 w-32 mb-1 sm:mb-0">Report Type:</span>
                                    <span class="text-blue-900 break-words">{{ ucfirst(str_replace('_', ' ', $supportRequest->request_type)) }}</span>
                                </div>
                                
                                @if($supportRequest->request_type === 'survey_lock_appeal')
                                    <div class="flex flex-col sm:flex-row sm:items-center">
                                        <span class="font-medium text-blue-700 w-32 mb-1 sm:mb-0">Survey ID:</span>
                                        <span class="text-blue-900">{{ $supportRequest->related_id }}</span>
                                    </div>
                                    @if($relatedItemTitle)
                                        <div class="flex flex-col sm:flex-row sm:items-start">
                                            <span class="font-medium text-blue-700 w-32 mb-1 sm:mb-0">Survey Title:</span>
                                            <span class="text-blue-900 flex-1 break-words">{{ $relatedItemTitle }}</span>
                                        </div>
                                    @endif
                                @elseif($supportRequest->request_type === 'report_appeal')
                                    <div class="flex flex-col sm:flex-row sm:items-center">
                                        <span class="font-medium text-blue-700 w-32 mb-1 sm:mb-0">Report ID:</span>
                                        <span class="text-blue-900">{{ $supportRequest->related_id }}</span>
                                    </div>
                                    @if($relatedItemTitle)
                                        <div class="flex flex-col sm:flex-row sm:items-start">
                                            <span class="font-medium text-blue-700 w-32 mb-1 sm:mb-0">Survey Title:</span>
                                            <span class="text-blue-900 flex-1 break-words">{{ $relatedItemTitle }}</span>
                                        </div>
                                    @endif
                                    @if($relatedItem)
                                        <div class="flex flex-col sm:flex-row sm:items-start">
                                            <span class="font-medium text-blue-700 w-32 mb-1 sm:mb-0">Report Reason:</span>
                                            <span class="text-blue-900 flex-1 break-words">{{ ucfirst(str_replace('_', ' ', $relatedItem->reason ?? 'Unknown')) }}</span>
                                        </div>
                                    @endif
                                @endif
                                
                                @if($relatedItem)
                                    <div class="mt-4 pt-3 border-t border-blue-200">
                                        @if($supportRequest->request_type === 'survey_lock_appeal')
                                            <a href="{{ route('surveys.responses', $supportRequest->related_id) }}" 
                                               target="_blank"
                                               class="inline-flex items-center text-blue-600 hover:text-blue-800 underline text-sm">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                View Survey Responses
                                            </a>
                                        @elseif($supportRequest->request_type === 'report_appeal')
                                            <a href="{{ route('surveys.responses.view', ['survey' => $relatedItem->survey_id, 'response' => $relatedItem->response_id]) }}" 
                                               target="_blank"
                                               class="inline-flex items-center text-blue-600 hover:text-blue-800 underline text-sm">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                View Reported Response
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-red-600 text-sm italic bg-red-50 p-2 rounded border border-red-200">
                                        Related item no longer exists or could not be found.
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Admin Response Tab -->
                <div x-show="tab === 'response'" x-cloak class="space-y-6">
                    <!-- Current Admin Notes -->
                    <div>
                        <h4 class="font-bold text-lg mb-3 text-gray-800">Current Admin Notes</h4>
                        <textarea 
                            rows="4" 
                            disabled
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-gray-50 text-gray-700 resize-none"
                            placeholder="No admin notes have been added yet."
                        >{{ $supportRequest->admin_notes }}</textarea>
                    </div>

                    <!-- Admin Response Form -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="font-bold text-lg mb-4 text-gray-800">Update Support Request</h4>
                        <form wire:submit.prevent="updateRequest" class="space-y-6">
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Admin Notes
                                </label>
                                <textarea 
                                    id="admin_notes" 
                                    wire:model="adminNotes" 
                                    rows="6" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200 resize-y min-h-[120px]"
                                    placeholder="Add your response or internal notes here..."
                                ></textarea>
                                @error('adminNotes') 
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select 
                                    id="status" 
                                    wire:model="status" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                @error('status') 
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>

                            <div class="flex justify-end pt-4">
                                <button 
                                    type="submit"
                                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            <div class="flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p>Loading request details...</p>
            </div>
        </div>
    @endif
</div>
