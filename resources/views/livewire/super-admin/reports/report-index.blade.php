<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Report Management</h2>
                
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
                               placeholder="Search reports by details, survey title, or reporter name..." 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="flex flex-wrap gap-4">
                        <!-- Reason Filter Buttons -->
                        <div class="flex space-x-2 flex-wrap">
                            <button wire:click="filterByReason('all')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                All Reasons
                            </button>
                            <button wire:click="filterByReason('inappropriate_content')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'inappropriate_content' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                                Inappropriate
                            </button>
                            <button wire:click="filterByReason('spam')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'spam' ? 'bg-orange-600 text-white' : 'bg-gray-200' }}">
                                Spam
                            </button>
                            <button wire:click="filterByReason('offensive')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'offensive' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}">
                                Offensive
                            </button>
                            <button wire:click="filterByReason('suspicious')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'suspicious' ? 'bg-indigo-600 text-white' : 'bg-gray-200' }}">
                                Suspicious
                            </button>
                            <button wire:click="filterByReason('duplicate')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'duplicate' ? 'bg-pink-600 text-white' : 'bg-gray-200' }}">
                                Duplicate
                            </button>
                            <button wire:click="filterByReason('other')" 
                                class="px-4 py-2 text-sm rounded {{ $reasonFilter === 'other' ? 'bg-gray-600 text-white' : 'bg-gray-200' }}">
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
                                <th class="py-3 px-6 text-left">Survey</th>
                                <th class="py-3 px-6 text-left">Reporter</th>
                                <th class="py-3 px-6 text-left">Respondent</th>
                                <th class="py-3 px-6 text-left">Reason</th>
                                <th class="py-3 px-6 text-left">Reported</th>
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse($reports as $report)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6">{{ $report->id }}</td>
                                    <td class="py-3 px-6">
                                        <div class="truncate max-w-[200px]">
                                            {{ $report->survey->title ?? 'Unknown Survey' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">{{ $report->reporter->name ?? 'Unknown' }}</td>
                                    <td class="py-3 px-6">{{ $report->respondent->name ?? 'Unknown' }}</td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 rounded text-xs {{ 
                                            $report->reason === 'inappropriate_content' ? 'bg-red-200 text-red-800' : 
                                            ($report->reason === 'spam' ? 'bg-orange-200 text-orange-800' :
                                            ($report->reason === 'offensive' ? 'bg-purple-200 text-purple-800' :
                                            ($report->reason === 'suspicious' ? 'bg-indigo-200 text-indigo-800' :
                                            ($report->reason === 'duplicate' ? 'bg-pink-200 text-pink-800' : 'bg-gray-200 text-gray-800'))))
                                        }}">
                                            {{ str_replace('_', ' ', ucwords($report->reason)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">{{ $report->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-6">
                                        <button 
                                            x-data
                                            @click="
                                                $wire.set('selectedReportId', null).then(() => {
                                                    $wire.set('selectedReportId', {{ $report->id }});
                                                    $nextTick(() => $dispatch('open-modal', { name: 'view-report-modal' }));
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
                                    <td colspan="7" class="py-3 px-6 text-center">No reports found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing report details -->
    <x-modal name="view-report-modal" title="Report Details">
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading details...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @if($selectedReportId)
                    @livewire('super-admin.reports.modal.view-report-modal', ['reportId' => $selectedReportId], key('report-modal-' . $selectedReportId))
                @else
                    <p class="text-gray-500">No report selected.</p>
                @endif
            </div>
        </div>
    </x-modal>
</div>
