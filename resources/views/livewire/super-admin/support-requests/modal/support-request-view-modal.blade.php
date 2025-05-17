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
                    <div class="space-y-4">
                        <!-- Description -->
                        <div>
                            <h4 class="font-bold mb-2">Description</h4>
                            <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap">{{ $supportRequest->description }}</div>
                        </div>
                        
                        <!-- Related Entity Info -->
                        @if($supportRequest->related_id && $supportRequest->related_model)
                        <div>
                            <h4 class="font-bold mb-2">Related Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-gray-600">Model Type:</span> {{ class_basename($supportRequest->related_model) }}
                                </div>
                                <div>
                                    <span class="text-gray-600">Item ID:</span> {{ $supportRequest->related_id }}
                                </div>
                                @if($relatedItem)
                                <div class="col-span-full">
                                    <a href="#" 
                                       class="text-blue-500 hover:underline"
                                       onclick="event.preventDefault(); /* Add code to view related item */">
                                        View Related Item
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Admin Response Tab -->
                <div x-show="tab === 'response'" x-cloak class="space-y-4">
                    <!-- Current Admin Notes -->
                    <div>
                        <h4 class="font-bold mb-2">Admin Notes</h4>
                        @if($supportRequest->admin_notes)
                            <div class="bg-gray-50 p-4 rounded-lg mb-4 whitespace-pre-wrap">
                                {{ $supportRequest->admin_notes }}
                            </div>
                        @else
                            <div class="bg-gray-50 p-4 rounded-lg mb-4 text-gray-500 italic">
                                No admin notes have been added yet.
                            </div>
                        @endif
                    </div>

                    <!-- Admin Response Form -->
                    <form wire:submit.prevent="updateRequest" class="mt-4">
                        <div class="mb-4">
                            <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Update Admin Notes
                            </label>
                            <textarea id="admin_notes" wire:model="adminNotes" rows="4" 
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                      placeholder="Add your response or internal notes here..."></textarea>
                            @error('adminNotes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Update Status
                            </label>
                            <select id="status" wire:model="status" 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
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
