<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mb-6" x-data="{ tab: 'users' }">
                    <nav class="flex -mb-px">
                        <button 
                            x-on:click="tab = 'users'" 
                            :class="{ 'border-blue-500 text-blue-600': tab === 'users', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'users' }" 
                            class="{{ !$isInstitutionAdmin ? 'w-1/3' : 'w-full' }} py-3 px-1 text-center border-b-2 font-medium text-sm"
                        >
                            User List
                        </button>
                        @if(!$isInstitutionAdmin)
                            <button 
                                x-on:click="tab = 'merchants'" 
                                :class="{ 'border-blue-500 text-blue-600': tab === 'merchants', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'merchants' }" 
                                class="w-1/3 py-3 px-1 text-center border-b-2 font-medium text-sm"
                            >
                                Merchant List
                            </button>
                            <button 
                                x-on:click="tab = 'institutions'" 
                                :class="{ 'border-blue-500 text-blue-600': tab === 'institutions', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'institutions' }" 
                                class="w-1/3 py-3 px-1 text-center border-b-2 font-medium text-sm"
                            >
                                Institution List
                            </button>
                        @endif
                    </nav>
                    <div class="pt-4">
                        <!-- User List Tab -->
                        <div x-show="tab === 'users'" x-cloak>
                            <!-- Show different title based on admin type -->
                            @if($isInstitutionAdmin)
                                <h2 class="text-2xl font-bold mb-4">{{ $institutionName }} - Users Management</h2>
                            @else
                                <h2 class="text-2xl font-bold mb-4">User Management</h2>
                            @endif
                            @if(session()->has('message'))
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                                    <p>{{ session('message') }}</p>
                                </div>
                            @endif
                            @if(session()->has('error'))
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                                    <p>{{ session('error') }}</p>
                                </div>
                            @endif
                            <!-- Search and Filters -->
                            <div class="mb-6">
                                <!-- Search Box -->
                                <div class="mb-4">
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="searchTerm" 
                                           placeholder="Search users by name or email..." 
                                           class="w-full px-4 py-2 border rounded-lg">
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    <!-- Status Filter Buttons -->
                                    <div class="flex space-x-2">
                                        <button wire:click="filterByStatus('all')" 
                                            class="px-4 py-2 text-sm rounded {{ $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                            All Users
                                        </button>
                                        <button wire:click="filterByStatus('active')" 
                                            class="px-4 py-2 text-sm rounded {{ $statusFilter === 'active' ? 'bg-green-600 text-white' : 'bg-gray-200' }}">
                                            Active ({{ $activeCount }})
                                        </button>
                                        <button wire:click="filterByStatus('inactive')" 
                                            class="px-4 py-2 text-sm rounded {{ $statusFilter === 'inactive' ? 'bg-red-600 text-white' : 'bg-gray-200' }}">
                                            Inactive ({{ $inactiveCount }})
                                        </button>
                                        <button wire:click="filterByStatus('archived')" 
                                            class="px-4 py-2 text-sm rounded {{ $statusFilter === 'archived' ? 'bg-gray-600 text-white' : 'bg-gray-200' }}">
                                            Archived ({{ $archivedCount }})
                                        </button>
                                    </div>
                                    <!-- Type Filter Buttons - Hide super_admin button for institution admins -->
                                    <div class="flex space-x-2">
                                        <button wire:click="filterByType('all')" 
                                            class="px-4 py-2 text-sm rounded {{ $typeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                                            All Roles
                                        </button>
                                        <button wire:click="filterByType('respondent')" 
                                            class="px-4 py-2 text-sm rounded {{ $typeFilter === 'respondent' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}">
                                            Respondents
                                        </button>
                                        <button wire:click="filterByType('researcher')" 
                                            class="px-4 py-2 text-sm rounded {{ $typeFilter === 'researcher' ? 'bg-yellow-600 text-white' : 'bg-gray-200' }}">
                                            Researchers
                                        </button>
                                        <button wire:click="filterByType('institution_admin')" 
                                            class="px-4 py-2 text-sm rounded {{ $typeFilter === 'institution_admin' ? 'bg-indigo-600 text-white' : 'bg-gray-200' }}">
                                            Institution Admins
                                        </button>
                                        @if(!$isInstitutionAdmin)
                                            <button wire:click="filterByType('super_admin')" 
                                                class="px-4 py-2 text-sm rounded {{ $typeFilter === 'super_admin' ? 'bg-pink-600 text-white' : 'bg-gray-200' }}">
                                                Super Admins
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                            <th class="py-3 px-6 text-left">ID</th>
                                            <th class="py-3 px-6 text-left">Name</th>
                                            <th class="py-3 px-6 text-left">Email</th>
                                            <th class="py-3 px-6 text-left">Role</th>
                                            <th class="py-3 px-6 text-left">Status</th>
                                            <th class="py-3 px-6 text-left">Joined Date</th>
                                            <th class="py-3 px-6 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 text-sm">
                                        @forelse($users as $user)
                                            <tr class="border-b border-gray-200 hover:bg-gray-50 {{ $user->trashed() ? 'bg-gray-100' : '' }}">
                                                <td class="py-3 px-6">{{ $user->id }}</td>
                                                <td class="py-3 px-6 flex items-center">
                                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-8 w-8 rounded-full mr-2">
                                                    {{ $user->name }}
                                                </td>
                                                <td class="py-3 px-6">{{ $user->email }}</td>
                                                <td class="py-3 px-6">
                                                    <span class="px-2 py-1 rounded text-xs 
                                                        {{ $user->type === 'super_admin' ? 'bg-pink-200 text-pink-800' : 
                                                        ($user->type === 'institution_admin' ? 'bg-indigo-200 text-indigo-800' : 
                                                        ($user->type === 'researcher' ? 'bg-yellow-200 text-yellow-800' : 'bg-purple-200 text-purple-800')) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $user->type)) }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-6">
                                                    <span class="px-2 py-1 rounded text-xs 
                                                        {{ $user->trashed() ? 'bg-gray-400 text-white' : 
                                                           ($user->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') }}">
                                                        {{ $user->trashed() ? 'Archived' : ($user->is_active ? 'Active' : 'Inactive') }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-6">{{ $user->created_at->format('M d, Y') }}</td>
                                                <td class="py-3 px-6">
                                                    <button 
                                                        x-data
                                                        @click="
                                                            $wire.set('selectedUserId', null).then(() => {
                                                                $wire.set('selectedUserId', {{ $user->id }});
                                                                $nextTick(() => $dispatch('open-modal', { name: 'user-view-modal' }));
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
                                                <td colspan="7" class="py-3 px-6 text-center">No users found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $users->links() }}
                            </div>
                        </div>
                        <!-- Merchant List Tab - Only for Super Admins -->
                        @if(!$isInstitutionAdmin)
                            <div x-show="tab === 'merchants'" x-cloak>
                                <h2 class="text-2xl font-bold mb-4">Merchant Management</h2>
                                @livewire('super-admin.merchants.merchant-index')
                            </div>
                            <div x-show="tab === 'institutions'" x-cloak>
                                <h2 class="text-2xl font-bold mb-4">Institution Management</h2>
                                @livewire('super-admin.institutions.institution-index')
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Modal for viewing user details -->
                <x-modal name="user-view-modal" title="User Details" focusable>
                    <div class="p-6 relative min-h-[400px] flex flex-col">            
                        <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                            <div class="flex flex-col items-center justify-center h-full">
                                <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                                <p class="text-sm text-gray-600">Loading details...</p>
                            </div>
                        </div>
                        <div wire:loading.remove class="flex-1">
                            @if($selectedUserId)
                                @livewire('super-admin.user-list.modal.user-view-modal', ['userId' => $selectedUserId], key('user-modal-' . $selectedUserId))
                            @else
                                <p class="text-gray-500">No user selected.</p>
                            @endif
                        </div>
                    </div>
                </x-modal>
            </div>
        </div>
    </div>
</div>
