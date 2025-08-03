<div>
    @if(session()->has('modal_message'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
            <p>{{ session('modal_message') }}</p>
        </div>
    @endif

    @if($user)
        <!-- User Header - Always visible (Redesigned) -->
        <div class="mb-6 flex flex-col items-center">
            <!-- Profile Photo (Smaller) -->
            <img src="{{ $user->profile_photo_url }}" 
                 alt="{{ $user->name }}" 
                 class="h-24 w-24 object-cover rounded-full mb-3 border-2 border-gray-200">
                 
            <!-- User Information -->
            <h3 class="text-lg font-semibold text-center">{{ $user->name }}</h3>
            <p class="text-sm text-gray-500 text-center">{{ $user->email }}</p>
            <p class="text-xs text-gray-400 text-center mb-2">UUID: {{ $user->uuid }}</p>
            
            <!-- Role and Status side by side -->
            <div class="flex space-x-3 mt-1">
                <span class="px-3 py-1 rounded-full text-xs {{ 
                    $user->type === 'super_admin' ? 'bg-pink-200 text-pink-800' : 
                    ($user->type === 'institution_admin' ? 'bg-indigo-200 text-indigo-800' : 
                    ($user->type === 'researcher' ? 'bg-yellow-200 text-yellow-800' : 'bg-purple-200 text-purple-800')) }}">
                    {{ ucfirst(str_replace('_', ' ', $user->type)) }}
                </span>
                
                <span class="px-3 py-1 rounded-full text-xs {{ 
                    $user->trashed() ? 'bg-gray-400 text-white' : 
                     ($user->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') }}">
                    {{ $user->trashed() ? 'Archived' : ($user->is_active ? 'Active' : 'Inactive') }}
                </span>
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
                    User Information
                </button>
                <button 
                    @click="tab = 'activity'; $wire.loadUserActivities()" 
                    :class="{ 'border-blue-500 text-blue-600': tab === 'activity', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'activity' }" 
                    class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                >
                    Activity History
                </button>
            </nav>
            
            <!-- Tab Content -->
            <div class="pt-4">
                <!-- User Info Tab -->
                <div x-show="tab === 'info'" x-cloak>
                    <div class="space-y-3">
                        <div>
                            <span class="font-bold">UUID:</span> 
                            {{ $user->uuid ?? 'Not available' }}
                        </div>
                        <div>
                            <span class="font-bold">Phone Number:</span> 
                            {{ $user->phone_number ?? 'Not provided' }}
                        </div>
                        <div>
                            <span class="font-bold">Account Level & XP:</span> 
                            Level {{ $user->account_level ?? 0 }} ({{ $user->experience_points ?? 0 }} XP)
                        </div>
                        
                        <!-- Trust Score - Now editable -->
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <label for="trustScore" class="block text-sm font-medium text-gray-700 mb-1">
                                Trust Score
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    id="trustScore"
                                    wire:model="trustScore"
                                    min="0"
                                    max="100"
                                    class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                                <span class="text-gray-500">/100</span>
                                
                                @error('trustScore') 
                                    <span class="text-red-500 text-xs ml-2">{{ $message }}</span> 
                                @enderror 
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Trust score lower than 60 will disable user's ability to participate in certain surveys.</p>
                        </div>
                        
                        <div>
                            <span class="font-bold">Email Verified:</span> 
                            {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'Not verified' }}
                        </div>
                        <div>
                            <span class="font-bold">Joined:</span> {{ $user->created_at->format('M d, Y h:i A') }}
                        </div>
                        <div>
                            <span class="font-bold">Last Active At:</span> {{ $user->last_active_at->format('M d, Y h:i A') }}
                        </div>
                        
                        @if($user->trashed())
                        <div>
                            <span class="font-bold">Archived At:</span> {{ $user->deleted_at->format('M d, Y h:i A') }}
                        </div>
                        @endif
                        
                        @if($user->institution)
                        <div>
                            <span class="font-bold">Institution:</span> {{ $user->institution->name ?? 'None' }}
                        </div>
                        @endif
                        
                        <div>
                            <span class="font-bold">Points Balance:</span> {{ $user->points ?? 0 }}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <!-- Save Trust Score Button - Only shown for active users -->
                        @if($user->is_active)
                        <button 
                            type="button"
                            x-data
                            x-on:click="Swal.fire({
                                title: 'Are you sure?',
                                text: 'Do you want to save the changes made?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#aaa',
                                confirmButtonText: 'Yes, save it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $wire.saveTrustScore();
                                }
                            })"
                            wire:loading.attr="disabled"
                            class="w-full py-2 rounded bg-blue-500 hover:bg-blue-600 text-white"
                        >
                            <span wire:loading.inline wire:target="saveTrustScore" class="inline-block">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                            <span wire:loading.remove wire:target="saveTrustScore">
                                Save Changes
                            </span>
                        </button>
                        @endif
                        
                        @if(!$user->trashed())
                            <!-- Active/Inactive Toggle Button (only for non-archived users AND not own profile) -->
                            @if($user->id !== auth()->id())
                                <button 
                                    type="button"
                                    x-data
                                    x-on:click="Swal.fire({
                                        title: 'Are you sure?',
                                        text: '{{ $user->is_active ? 'Do you want to deactivate this user?' : 'Do you want to activate this user?' }}',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '{{ $user->is_active ? '#d33' : '#3085d6' }}',
                                        cancelButtonColor: '#aaa',
                                        confirmButtonText: '{{ $user->is_active ? 'Yes, deactivate!' : 'Yes, activate!' }}'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $wire.toggleActiveStatus();
                                        }
                                    })"
                                    wire:loading.attr="disabled"
                                    class="w-full py-2 rounded {{ 
                                        $user->is_active 
                                            ? 'bg-red-500 hover:bg-red-600 text-white' 
                                            : 'bg-green-500 hover:bg-green-600 text-white'
                                    }}"
                                >
                                    <span wire:loading.inline wire:target="toggleActiveStatus" class="inline-block">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                    <span wire:loading.remove wire:target="toggleActiveStatus">
                                        {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                                    </span>
                                </button>
                            @endif
                            
                            <!-- Archive Button (soft delete) - Only visible for non-institution admins AND not own profile -->
                            @if(auth()->user()->type !== 'institution_admin' && $user->id !== auth()->id())
                            <button 
                                type="button"
                                x-data
                                x-on:click="Swal.fire({
                                    title: 'Are you sure?',
                                    text: 'Do you want to archive this user? This action can be reversed later.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Yes, archive it!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.archiveUser();
                                    }
                                })"
                                wire:loading.attr="disabled"
                                class="w-full py-2 rounded bg-gray-500 hover:bg-gray-600 text-white"
                            >
                                <span wire:loading.inline wire:target="archiveUser" class="inline-block">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                                <span wire:loading.remove wire:target="archiveUser">
                                    Archive User
                                </span>
                            </button>
                            @endif
                        @else
                            <!-- Restore Button (for archived users) -->
                            <button 
                                type="button"
                                x-data
                                x-on:click="Swal.fire({
                                    title: 'Are you sure?',
                                    text: 'Do you want to restore this archived user?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#aaa',
                                    confirmButtonText: 'Yes, restore it!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.restoreUser();
                                    }
                                })"
                                wire:loading.attr="disabled"
                                class="w-full py-2 rounded bg-green-500 hover:bg-green-600 text-white"
                            >
                                <span wire:loading.inline wire:target="restoreUser" class="inline-block">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                                <span wire:loading.remove wire:target="restoreUser">
                                    Restore User
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
                
                <!-- Activity History Tab -->
                <div x-show="tab === 'activity'" class="max-h-80 overflow-y-auto">
                    <div class="space-y-2">
                        <!-- Update loading indicator to be centered -->
                        <div wire:loading class="flex justify-center items-center py-4 w-full">
                            <div class="text-center">
                                <div class="inline-block animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                                <p class="mt-2 text-gray-500">Loading activity history...</p>
                            </div>
                        </div>
                        
                        <div wire:loading.remove>
                            @forelse($activities as $activity)
                                <div class="border-l-2 
                                    {{ $activity['type'] === 'survey_response' ? 'border-blue-500' : 
                                       ($activity['type'] === 'reward_redemption' ? 'border-green-500' : 
                                       ($activity['type'] === 'survey_created' ? 'border-purple-500' : 
                                       ($activity['type'] === 'demographic_update' ? 'border-teal-500' : 
                                       ($activity['type'] === 'report_received' ? 'border-red-500' : 
                                       ($activity['type'] === 'report_made' ? 'border-orange-500' : 
                                       ($activity['type'] === 'voucher_activity' ? 'border-indigo-500' : 'border-gray-500')))))) }} 
                                    pl-3 py-2 mb-2">
                                    <p class="text-sm">
                                        <span class="font-medium">
                                            {{ $activity['action'] }}
                                            @if(isset($activity['status']) && in_array($activity['type'], ['reward_redemption', 'report_received', 'report_made']))
                                                <span class="px-2 py-0.5 rounded-full text-xs 
                                                    {{ $activity['status'] === 'completed' || $activity['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                       ($activity['status'] === 'pending' || $activity['status'] === 'under_appeal' ? 'bg-yellow-100 text-yellow-800' : 
                                                       ($activity['status'] === 'rejected' || $activity['status'] === 'dismissed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $activity['status'])) }}
                                                </span>
                                            @endif
                                        </span> - 
                                        {{ is_string($activity['created_at']) ? $activity['created_at'] : $activity['created_at']->format('M d, Y h:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $activity['details'] }}</p>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center italic py-4">No activity records available for this user.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            <div class="flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p>Loading user details...</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
