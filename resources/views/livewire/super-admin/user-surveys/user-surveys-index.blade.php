<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Survey Management</h2>
                
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
                               placeholder="Search by title or UUID..." 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="flex flex-wrap gap-4">
                        <!-- Status Filter Buttons -->
                        <div class="flex space-x-2 flex-wrap">
                            <button wire:click="filterByStatus('all')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                All Surveys
                            </button>
                            <button wire:click="filterByStatus('pending')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200' }}">
                                Pending ({{ $pendingCount }})
                            </button>
                            <button wire:click="filterByStatus('published')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'published' ? 'bg-green-600 text-white' : 'bg-gray-200' }}">
                                Published ({{ $publishedCount }})
                            </button>
                            <button wire:click="filterByStatus('locked')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'locked' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                                Locked ({{ $lockedCount }})
                            </button>
                            <button wire:click="filterByStatus('archived')" 
                                class="px-4 py-2 text-sm rounded {{ $statusFilter === 'archived' ? 'bg-gray-600 text-white' : 'bg-gray-200' }}">
                                Archived ({{ $archivedCount }})
                            </button>
                        </div>
                        
                        <!-- Type Filter Buttons -->
                        <div class="flex space-x-2">
                            <button wire:click="filterByType('all')" 
                                class="px-4 py-2 text-sm rounded {{ $typeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                All Types
                            </button>
                            <button wire:click="filterByType('basic')" 
                                class="px-4 py-2 text-sm rounded {{ $typeFilter === 'basic' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                Basic ({{ $basicCount }})
                            </button>
                            <button wire:click="filterByType('advanced')" 
                                class="px-4 py-2 text-sm rounded {{ $typeFilter === 'advanced' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}">
                                Advanced ({{ $advancedCount }})
                            </button>
                        </div>

                        <!-- Institution Filter Button -->
                        <div class="flex space-x-2">
                            <button wire:click="filterByInstitution('all')" 
                                class="px-4 py-2 text-sm rounded {{ $institutionFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                All Access
                            </button>
                            <button wire:click="filterByInstitution('institution')" 
                                class="px-4 py-2 text-sm rounded {{ $institutionFilter === 'institution' ? 'bg-yellow-600 text-white' : 'bg-gray-200' }}">
                                Institution Only ({{ $institutionCount }})
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">ID</th>
                                <th class="py-3 px-6 text-left">Title</th>
                                <th class="py-3 px-6 text-left">Type</th>
                                <th class="py-3 px-6 text-left">Creator</th>
                                <th class="py-3 px-6 text-left">Status</th>
                                <th class="py-3 px-6 text-left">Responses</th>
                                <th class="py-3 px-6 text-left">Created</th>
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse($surveys as $survey)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6">{{ $survey->id }}</td>
                                    <td class="py-3 px-6">
                                        <div class="truncate max-w-[200px]">{{ $survey->title }}</div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 rounded text-xs {{ 
                                            $survey->type === 'basic' ? 'bg-indigo-200 text-indigo-800' : 'bg-purple-200 text-purple-800' }}">
                                            {{ ucfirst($survey->type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">{{ $survey->user->name ?? 'Unknown' }}</td>
                                    <td class="py-3 px-6">
                                        <div class="flex items-center space-x-1">
                                            <span class="px-2 py-1 rounded text-xs {{ 
                                                $survey->trashed() ? 'bg-gray-400 text-white' :
                                                ($survey->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 
                                                ($survey->status === 'published' ? 'bg-green-200 text-green-800' : 
                                                ($survey->status === 'ongoing' ? 'bg-purple-200 text-purple-800' : 'bg-gray-200 text-gray-800'))) 
                                            }}">
                                                {{ $survey->trashed() ? 'Archived' : ucfirst($survey->status) }}
                                            </span>
                                            
                                            @if(!$survey->trashed() && $survey->is_locked)
                                                <span class="px-2 py-1 rounded text-xs bg-red-200 text-red-800 flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Locked
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        {{ $survey->responses_count ?? $survey->responses()->count() }}
                                    </td>
                                    <td class="py-3 px-6">{{ $survey->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-6">
                                        <button 
                                            x-data
                                            @click="
                                                $wire.set('selectedSurveyId', null).then(() => {
                                                    $wire.set('selectedSurveyId', {{ $survey->id }});
                                                    $nextTick(() => $dispatch('open-modal', { name: 'survey-view-modal' }));
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
                                    <td colspan="8" class="py-3 px-6 text-center">No surveys found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $surveys->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing survey details -->
    <x-modal name="survey-view-modal" title="Survey Details">
        <div class="p-6 relative min-h-[400px] flex flex-col">
            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-600">Loading details...</p>
                </div>
            </div>
            
            <div wire:loading.remove class="flex-1">
                @if($selectedSurveyId)
                    @livewire('super-admin.user-surveys.modal.user-survey-view-modal', ['surveyId' => $selectedSurveyId], key('survey-modal-' . $selectedSurveyId))
                @else
                    <p class="text-gray-500">No survey selected.</p>
                @endif
            </div>
        </div>
    </x-modal>
</div>
