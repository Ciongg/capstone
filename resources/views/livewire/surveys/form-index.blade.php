<div class="max-w-7xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">My Surveys</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($surveys as $survey)
            <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col">
                {{-- Survey Image --}}
                <a href="{{ route('surveys.create', $survey->uuid) }}" class="block w-full h-36">
                    @if($survey->image_path)
                        <img src="{{ asset('storage/' . $survey->image_path) }}"
                             alt="Survey image for {{ $survey->title }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500 text-sm">no image</span>
                        </div>
                    @endif
                </a>

                {{-- Survey Content --}}
                <div class="p-3 flex-grow">
                    <h3 class="text-base font-semibold mb-1 text-gray-800 hover:text-blue-600 transition duration-150 ease-in-out">
                        <a href="{{ route('surveys.create', $survey->uuid) }}">{{ $survey->title }}</a>
                    </h3>
                    <p class="text-gray-600 text-sm mb-2 truncate" title="{{ $survey->description }}">
                        {{ $survey->description ?? 'No description available.' }}
                    </p>

                    {{-- Details Section --}}
                    <div class="flex justify-between items-center text-xs mt-2 mb-2">
                        {{-- Survey Status (Left) --}}
                        <div>
                            <span @class([
                                'inline-flex items-center h-6 px-2 py-0.5 font-semibold rounded-full',
                                'bg-gray-100 text-gray-700' => $survey->status === 'pending',
                                'bg-blue-100 text-blue-700' => $survey->status === 'published',
                                'bg-amber-100 text-amber-700' => $survey->status === 'ongoing',
                                'bg-green-100 text-green-700' => $survey->status === 'finished',
                                'bg-red-100 text-red-800' => $survey->status === 'closed',
                                'bg-gray-100 text-gray-800' => !in_array($survey->status, ['pending', 'published', 'ongoing', 'finished', 'closed']),
                            ])>
                                {{ ucfirst($survey->status) }}
                            </span>
                        </div>

                        {{-- Respondent Count (Center) --}}
                        <div class="flex items-center text-gray-600" title="Number of Responses / Target Respondents">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>{{ $survey->responses_count ?? 0 }}/{{ $survey->target_respondents > 0 ? $survey->target_respondents : '∞' }}</span>
                        </div>

                        {{-- Survey Type (Right) --}}
                        <div>
                            <span @class([
                                'inline-flex items-center h-6 px-2 py-0.5 font-semibold rounded-full',
                                'bg-blue-100 text-blue-700' => $survey->type === 'basic',
                                'bg-purple-100 text-purple-700' => $survey->type !== 'basic',
                            ])>
                                {{ ucfirst($survey->type) }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Actions --}}
                    <div class="mt-3 flex justify-between items-center">
                        {{-- Open Button --}}
                        <a href="{{ route('surveys.create', $survey->uuid) }}"
                           class="px-2.5 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">
                            Open
                        </a>
                        
                        {{-- Lock Warning (shown only if survey is locked) --}}
                        @if($survey->is_locked)
                            <div class="tooltip" x-data="{ isOpen: false }">
                                <button 
                                    @mouseenter="isOpen = true" 
                                    @mouseleave="isOpen = false"
                                    class="text-red-500 hover:text-red-600"
                                    title="Survey Locked"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </button>
                                <div x-show="isOpen" class="tooltip-text bg-gray-800 text-white text-xs rounded py-1 px-2 absolute z-10 bottom-full left-1/2 transform -translate-x-1/2 mb-1 w-48" style="display:none">
                                    <strong>Survey Locked:</strong> {{ $survey->lock_reason ?? 'This survey has been locked by an administrator' }}
                                </div>
                            </div>
                        @else
                            <div class="w-5"></div> {{-- Empty spacer when not locked --}}
                        @endif


                        

                        {{-- Delete Button --}}
                        <button
                            wire:click="deleteSurvey({{ $survey->id }})"
                            wire:confirm="Are you sure you want to delete this survey and all its data?"
                            class="p-1 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            title="Delete Survey"
                        >
                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 mt-8">
                @if(auth()->user()->institution_id || auth()->user()->isSuperAdmin())
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-4 max-w-xl mx-auto">
                        <div class="font-semibold text-blue-700 mb-2">You haven't created any surveys yet.</div>
                        <div class="text-sm text-blue-800">
                            Start by clicking the "Create Survey" button found in the navigation bar.
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-4 max-w-xl mx-auto">
                        <div class="font-semibold text-yellow-700 mb-2">You are currently unable to create surveys.</div>
                        <div class="text-sm text-yellow-800">
                            Your account must be registered under an academic institution's official domain (e.g., <span class="font-mono">@adamson.edu.ph</span>), and that institution must be supported within our system.
                        </div>
                    </div>
                    <div class="mt-2 text-gray-500 text-sm">
                        If you believe this is an error, please contact support or your institution administrator.
                    </div>
                @endif
            </div>
        @endforelse
    </div>
</div>
