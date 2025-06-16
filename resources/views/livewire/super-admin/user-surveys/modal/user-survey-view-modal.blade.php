<div x-data="{ fullscreenImageSrc: null }">
    @if(session()->has('modal_message'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
            <p>{{ session('modal_message') }}</p>
        </div>
    @endif

    @if($survey)
        <!-- Survey Header - Always visible -->
        <div class="mb-6 flex flex-col">
            <!-- Survey Title and Status -->
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-2xl font-bold">{{ $survey->title }}</h3>
                    <p class="text-sm text-gray-500">Created by: {{ $survey->user->name ?? 'Unknown' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm {{ 
                    $survey->trashed() ? 'bg-gray-400 text-white' :
                    ($survey->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 
                    ($survey->status === 'published' ? 'bg-green-200 text-green-800' : 
                    ($survey->status === 'ongoing' ? 'bg-purple-200 text-purple-800' : 'bg-gray-200 text-gray-800'))) 
                }}">
                    {{ $survey->trashed() ? 'Archived' : ucfirst($survey->status) }}
                </span>
            </div>
            
            <!-- Survey Image -->
            @if($survey->image_path)
            <div class="mt-4 mb-2">
                @php $imageUrl = asset('storage/' . $survey->image_path); @endphp
                <img src="{{ $imageUrl }}" 
                     alt="{{ $survey->title }}" 
                     class="rounded-lg w-full max-h-48 object-cover cursor-pointer"
                     @click="fullscreenImageSrc = '{{ $imageUrl }}'"
                     style="cursor: pointer;">
            </div>
            @endif
            
            <!-- Brief Survey Info -->
            <div class="mt-4 flex flex-wrap justify-between">
                <div class="flex items-center mb-2">
                    <span class="font-bold mr-2">Type:</span>
                    <span class="px-2 py-1 rounded text-xs {{ 
                        $survey->type === 'basic' ? 'bg-indigo-200 text-indigo-800' : 'bg-pink-200 text-pink-800' 
                    }}">
                        {{ ucfirst($survey->type) }}
                    </span>
                </div>
                <div class="mb-2">
                    <span class="font-bold">Responses:</span> {{ $survey->responses()->count() }}
                </div>
                <div class="mb-2">
                    <span class="font-bold">Created:</span> {{ $survey->created_at->format('M d, Y h:i A') }}
                </div>
                <div class="mb-2">
                    <span class="font-bold">Days Left:</span> 
                    @if(!$survey->end_date)
                        <span>No end date</span>
                    @elseif($survey->end_date->isPast())
                        <span>Expired</span>
                    @else
                        <span>{{ (int)now()->diffInDays($survey->end_date, false) }} days</span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-4" x-data="{ tab: 'info' }">
            <nav class="flex -mb-px">
                <button 
                    @click="tab = 'info'" 
                    :class="{ 'border-blue-500 text-blue-600': tab === 'info', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'info' }" 
                    class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                >
                    Survey Information
                </button>
                <button 
                    @click="tab = 'settings'" 
                    :class="{ 'border-blue-500 text-blue-600': tab === 'settings', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'settings' }" 
                    class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                >
                    Survey Settings
                </button>
            </nav>
            
            <!-- Tab Content -->
            <div class="pt-4">
                <!-- Survey Info Tab -->
                <div x-show="tab === 'info'" x-cloak>
                    <div class="space-y-4">
                        <!-- Description -->
                        <div>
                            <h4 class="font-bold mb-2">Description</h4>
                            <p class="text-gray-700">{{ $survey->description ?? 'No description provided' }}</p>
                        </div>
                        
                        <!-- Points and Target Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-bold mb-2">Target Respondents</h4>
                                <p>{{ $survey->target_respondents ?? 'No target set' }}</p>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">Total Points Allocated</h4>
                                <p>{{ $survey->points_allocated ?? 0 }} points</p>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">Institution Only</h4>
                                <p>{{ $survey->is_institution_only ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                        
                        <!-- Timing Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-bold mb-2">Start Date</h4>
                                <p>{{ $survey->start_date ? $survey->start_date->format('M d, Y h:i A') : 'Not set' }}</p>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">End Date</h4>
                                <p>{{ $survey->end_date ? $survey->end_date->format('M d, Y h:i A') : 'Not set' }}</p>
                            </div>
                        </div>
                        
                        <!-- Topic Info -->
                        <div>
                            <h4 class="font-bold mb-2">Survey Topic</h4>
                            <p>{{ $survey->topic?->name ?? 'No topic assigned' }}</p>
                        </div>

                        <!-- Action Buttons - Keep only the Preview button -->
                        <div class="mt-6 space-y-3">
                            <!-- Preview Button -->
                            <a 
                                href="{{ route('surveys.preview', $survey) }}" 
                                target="_blank"
                                class="block w-full py-2 rounded bg-blue-500 hover:bg-blue-600 text-white text-center"
                            >
                                Preview Survey
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Survey Settings Tab -->
                <div x-show="tab === 'settings'" x-cloak class="space-y-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
                        <p class="font-medium text-yellow-800">
                            Survey settings can only be modified by the survey creator or using the form builder interface.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic settings values listed as read-only -->
                        <div class="space-y-2">
                            <h4 class="font-semibold">Form Settings</h4>
                            <p><span class="text-gray-600">Survey ID:</span> {{ $survey->id }}</p>
                            <p><span class="text-gray-600">Survey Type:</span> {{ ucfirst($survey->type) }}</p>
                            <p><span class="text-gray-600">Institution Only:</span> {{ $survey->is_institution_only ? 'Yes' : 'No' }}</p>
                            <p><span class="text-gray-600">Last Updated:</span> {{ $survey->updated_at->format('M d, Y') }}</p>
                        </div>
                        
                        <!-- Replace Status Timeline with Survey Tags -->
                        <div class="space-y-2">
                            <h4 class="font-semibold">Survey Tags</h4>
                            
                            <!-- Regular Tags -->
                            <div class="mb-3">
                                <p class="text-gray-600 mb-1">General Tags:</p>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($survey->tags as $tag)
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                            {{ $tag->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-sm">No general tags</span>
                                    @endforelse
                                </div>
                            </div>
                            
                            <!-- Institution Tags -->
                            <div>
                                <p class="text-gray-600 mb-1">Institution Tags:</p>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($survey->institutionTags as $tag)
                                        <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">
                                            {{ $tag->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-sm">No institution tags</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons Section - Move from Info tab to here -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h4 class="font-semibold mb-4">Survey Actions</h4>
                        <!-- Move the Lock/Unlock and Archive buttons here with the same side-by-side layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if(!$survey->trashed())
                                <!-- Lock/Unlock Button -->
                                <div class="{{ !$survey->is_locked ? 'md:col-span-2' : '' }}">
                                    @if(!$survey->is_locked)
                                        <!-- Show input field for lock reason when survey is unlocked -->
                                        <div class="mb-3">
                                            <label for="lockReason" class="block text-sm font-medium text-gray-700 mb-1">
                                                Reason for locking:
                                            </label>
                                            <input 
                                                type="text" 
                                                id="lockReason"
                                                wire:model="lockReason"
                                                placeholder="Enter reason for locking the survey"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            >
                                        </div>
                                    @endif
                                    
                                    <button 
                                        type="button"
                                        wire:click.prevent="toggleLockStatus"
                                        wire:loading.attr="disabled"
                                        class="w-full py-2 rounded {{ 
                                            $survey->is_locked 
                                                ? 'bg-green-500 hover:bg-green-600 text-white' 
                                                : 'bg-red-500 hover:bg-red-600 text-white'
                                        }}"
                                    >
                                        <span wire:loading.inline wire:target="toggleLockStatus" class="inline-block">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                        <span wire:loading.remove wire:target="toggleLockStatus">
                                            {{ $survey->is_locked ? 'Unlock Survey' : 'Lock Survey' }}
                                        </span>
                                    </button>
                                </div>
                                
                                <!-- Archive Button (soft delete) -->
                                <div class="{{ !$survey->is_locked ? 'md:col-span-2' : '' }}">
                                    <button 
                                        type="button"
                                        wire:click.prevent="archiveSurvey"
                                        wire:loading.attr="disabled"
                                        class="w-full py-2 rounded bg-gray-500 hover:bg-gray-600 text-white"
                                    >
                                        <span wire:loading.inline wire:target="archiveSurvey" class="inline-block">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                        <span wire:loading.remove wire:target="archiveSurvey">
                                            Archive Survey
                                        </span>
                                    </button>
                                </div>
                            @else
                                <!-- Restore Button (for archived surveys) -->
                                <div class="col-span-full">
                                    <button 
                                        type="button"
                                        wire:click.prevent="restoreSurvey"
                                        wire:loading.attr="disabled"
                                        class="w-full py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
                                    >
                                        <span wire:loading.inline wire:target="restoreSurvey" class="inline-block">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                        <span wire:loading.remove wire:target="restoreSurvey">
                                            Restore Survey
                                        </span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fullscreen Image Overlay -->
        <div x-show="fullscreenImageSrc"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="fullscreenImageSrc = null"  {{-- Click background to close --}}
             @keydown.escape.window="fullscreenImageSrc = null" {{-- Press Escape to close --}}
             class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4 cursor-pointer"
             style="display: none;"> {{-- Add display:none to prevent flash on load --}}
            
            <img :src="fullscreenImageSrc" 
                 alt="Fullscreen Survey Image" 
                 class="max-w-full max-h-full object-contain"
                 @click.stop> {{-- Prevent closing when clicking the image itself --}}
                      
            {{-- Larger, easier-to-tap close button for mobile --}}
            <button @click="fullscreenImageSrc = null" 
                    class="cursor-pointer absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-white text-4xl sm:text-3xl font-bold leading-none rounded-full hover:bg-black hover:bg-opacity-25 focus:outline-none">
                &times;
            </button>
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            <div class="flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p>Loading survey details...</p>
            </div>
        </div>
    @endif
</div>
