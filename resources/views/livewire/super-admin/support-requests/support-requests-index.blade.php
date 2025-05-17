<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Support Request Management</h2>
                
                @if(session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif
                
                <!-- Search and Filters -->
                <div class="mb-6">
                    <!-- Search Box -->
                    <div class="mb-4">
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchTerm" 
                               placeholder="Search requests by subject or description..." 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="flex flex-wrap gap-4">
                        <!-- Status Filter Buttons -->
                        <div class="flex space-x-2 flex-wrap">
                            <button wire:click="filterByStatus('all')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                All Requests
                            </button>
                            <button wire:click="filterByStatus('pending')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200' }}">
                                Pending ({{ $pendingCount }})
                            </button>
                            <button wire:click="filterByStatus('in_progress')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'in_progress' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}">
                                In Progress ({{ $inProgressCount }})
                            </button>
                            <button wire:click="filterByStatus('resolved')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'resolved' ? 'bg-green-600 text-white' : 'bg-gray-200' }}">
                                Resolved ({{ $resolvedCount }})
                            </button>
                            <button wire:click="filterByStatus('rejected')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                                Rejected ({{ $rejectedCount }})
                            </button>
                        </div>
                        
                        <!-- Type Filter Buttons -->
                        <div class="flex space-x-2 flex-wrap">
                            <button wire:click="filterByType('all')" 
                                class="px-4 py-2 text-sm rounded {{ $requestTypeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                All Types
                            </button>
                            <button wire:click="filterByType('survey_lock_appeal')" 
                                class="px-4 py-2 text-sm rounded {{ $requestTypeFilter === 'survey_lock_appeal' ? 'bg-indigo-600 text-white' : 'bg-gray-200' }}">
                                Lock Appeals
                            </button>
                            <button wire:click="filterByType('report_appeal')" 
                                class="px-4 py-2 text-sm rounded {{ $requestTypeFilter === 'report_appeal' ? 'bg-pink-600 text-white' : 'bg-gray-200' }}">
                                Report Appeals
                            </button>
                            <button wire:click="filterByType('account_issue')" 
                                class="px-4 py-2 text-sm rounded {{ $requestTypeFilter === 'account_issue' ? 'bg-orange-600 text-white' : 'bg-gray-200' }}">
                                Account Issues
                            </button>
                            <button wire:click="filterByType('survey_question')" 
                                class="px-4 py-2 text-sm rounded {{ $requestTypeFilter === 'survey_question' ? 'bg-teal-600 text-white' : 'bg-gray-200' }}">
                                Survey Questions
                            </button>
                            <button wire:click="filterByType('other')" 
                                class="px-4 py-2 text-sm rounded {{ $requestTypeFilter === 'other' ? 'bg-gray-600 text-white' : 'bg-gray-200' }}">
                                Other
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">ID</th>
                                <th class="py-3 px-6 text-left">Subject</th>
                                <th class="py-3 px-6 text-left">User</th>
                                <th class="py-3 px-6 text-left">Type</th>
                                <th class="py-3 px-6 text-left">Status</th>
                                <th class="py-3 px-6 text-left">Submitted</th>
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse($supportRequests as $request)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6">{{ $request->id }}</td>
                                    <td class="py-3 px-6">
                                        <div class="truncate max-w-[250px]">{{ $request->subject }}</div>
                                    </td>
                                    <td class="py-3 px-6">{{ $request->user->name ?? 'Unknown' }}</td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 rounded text-xs {{ 
                                            $request->request_type === 'survey_lock_appeal' ? 'bg-indigo-200 text-indigo-800' : 
                                            ($request->request_type === 'report_appeal' ? 'bg-pink-200 text-pink-800' :
                                            ($request->request_type === 'account_issue' ? 'bg-orange-200 text-orange-800' :
                                            ($request->request_type === 'survey_question' ? 'bg-teal-200 text-teal-800' : 'bg-gray-200 text-gray-800')))
                                        }}">
                                            {{ str_replace('_', ' ', ucfirst($request->request_type)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 rounded text-xs {{ 
                                            $request->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 
                                            ($request->status === 'in_progress' ? 'bg-purple-200 text-purple-800' :
                                            ($request->status === 'resolved' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'))
                                        }}">
                                            {{ str_replace('_', ' ', ucfirst($request->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">{{ $request->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-6">
                                        <button 
                                            x-data
                                            @click="
                                                $wire.set('selectedRequestId', null).then(() => {
                                                    $wire.set('selectedRequestId', {{ $request->id }});
                                                    $nextTick(() => $dispatch('open-modal', { name: 'support-request-view-modal' }));
                                                })
                                            "
                                            class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                                        >
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-3 px-6 text-center">No support requests found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $supportRequests->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing support request details -->
    <x-modal name="support-request-view-modal" title="Support Request Details">
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading details...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @if($selectedRequestId)
                    @livewire('super-admin.support-requests.modal.support-request-view-modal', ['requestId' => $selectedRequestId], key('request-modal-' . $selectedRequestId))
                @else
                    <p class="text-gray-500">No support request selected.</p>
                @endif
            </div>
        </div>
    </x-modal>
</div>
