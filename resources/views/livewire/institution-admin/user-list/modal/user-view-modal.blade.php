<div class="p-6">
    @if (!$user)
        <div class="text-center py-8">
            <h3 class="text-lg font-medium text-gray-900">User not found</h3>
            <p class="mt-2 text-sm text-gray-500">This user may have been removed or doesn't belong to your institution.</p>
            <button wire:click="$dispatch('closeModal')" class="mt-4 bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Close</button>
        </div>
    @else
        <h2 class="text-lg font-medium text-gray-900 flex items-center">
            <img class="h-10 w-10 rounded-full mr-4" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
            {{ $user->first_name }} {{ $user->last_name }}
            @if ($user->trashed())
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Archived</span>
            @elseif ($user->is_active)
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
            @else
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Inactive</span>
            @endif
        </h2>
        
        @if (session()->has('modal_message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4" role="alert">
                <p>{{ session('modal_message') }}</p>
            </div>
        @endif
        
        <!-- User Details -->
        <div class="mt-6 border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->first_name }} {{ $user->last_name }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">User Type</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst(str_replace('_', ' ', $user->type)) }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Points</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->points ?? 0 }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Experience Points</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->experience_points ?? 0 }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Joined</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->created_at->format('F j, Y') }}</dd>
                </div>
                @if ($user->trashed())
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Archived Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->deleted_at->format('F j, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
        
        <!-- User Activity -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900">User Activity</h3>
            @if (!$activitiesLoaded)
                <button wire:click="loadUserActivities" class="mt-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded shadow">
                    <div wire:loading.remove wire:target="loadUserActivities">Load Activities</div>
                    <div wire:loading wire:target="loadUserActivities">Loading...</div>
                </button>
            @else
                @if (empty($activities))
                    <p class="text-sm text-gray-500 mt-2">No recent activities found.</p>
                @else
                    <div class="mt-2 flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach ($activities as $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if (!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @if ($activity['type'] === 'survey_response')
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @elseif ($activity['type'] === 'reward_redemption')
                                                    <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @elseif ($activity['type'] === 'survey_created')
                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $activity['action'] }} <span class="font-medium text-gray-900">{{ $activity['details'] }}</span>
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>
        
        <!-- Actions -->
        <div class="mt-6 flex justify-between">
            <div>
                <button wire:click="$dispatch('closeModal')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Close
                </button>
            </div>
            <div class="space-x-2">
                @if (!$user->trashed())
                    <button wire:click="toggleActiveStatus" wire:confirm="{{ $user->is_active ? 'Deactivate this user?' : 'Activate this user?' }}" class="{{ $user->is_active ? 'bg-yellow-600' : 'bg-green-600' }} py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:{{ $user->is_active ? 'bg-yellow-700' : 'bg-green-700' }}">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button wire:click="archiveUser" wire:confirm="Are you sure you want to archive this user?" class="bg-red-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700">
                        Archive
                    </button>
                @else
                    <button wire:click="restoreUser" wire:confirm="Are you sure you want to restore this user?" class="bg-green-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700">
                        Restore
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
