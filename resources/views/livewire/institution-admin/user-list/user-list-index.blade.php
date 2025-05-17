<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Institution Users</h1>
        
        <!-- Search & Filter Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex flex-col md:flex-row justify-between space-y-4 md:space-y-0 md:space-x-4">
                    <!-- Search Box -->
                    <div class="flex-1 max-w-md">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live="searchTerm" type="search" id="search" class="form-input block w-full pl-10 sm:text-sm sm:leading-5 rounded-md" placeholder="Search users...">
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex flex-wrap items-center space-x-1">
                        <span class="text-gray-700 mr-2">Status:</span>
                        <button wire:click="filterByStatus('all')" class="{{ $statusFilter === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded-md text-sm font-medium">
                            All <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full {{ $statusFilter === 'all' ? 'bg-white text-gray-800' : 'bg-gray-600 text-white' }}">{{ $activeCount + $inactiveCount + $archivedCount }}</span>
                        </button>
                        <button wire:click="filterByStatus('active')" class="{{ $statusFilter === 'active' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded-md text-sm font-medium">
                            Active <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full {{ $statusFilter === 'active' ? 'bg-white text-green-600' : 'bg-green-600 text-white' }}">{{ $activeCount }}</span>
                        </button>
                        <button wire:click="filterByStatus('inactive')" class="{{ $statusFilter === 'inactive' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded-md text-sm font-medium">
                            Inactive <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full {{ $statusFilter === 'inactive' ? 'bg-white text-yellow-600' : 'bg-yellow-600 text-white' }}">{{ $inactiveCount }}</span>
                        </button>
                        <button wire:click="filterByStatus('archived')" class="{{ $statusFilter === 'archived' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded-md text-sm font-medium">
                            Archived <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full {{ $statusFilter === 'archived' ? 'bg-white text-red-600' : 'bg-red-600 text-white' }}">{{ $archivedCount }}</span>
                        </button>
                    </div>
                    
                    <!-- User Type Filter -->
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">User Type:</span>
                        <select wire:model.live="typeFilter" class="form-select rounded-md shadow-sm mt-1 block w-full">
                            <option value="all">All Types</option>
                            <option value="respondent">Respondent</option>
                            <option value="researcher">Researcher</option>
                            <option value="institution_admin">Institution Admin</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User List Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                @if ($users->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter to find what you're looking for.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->type)) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($user->trashed())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Archived
                                                </span>
                                            @elseif ($user->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="$dispatch('openModal', { component: 'institution-admin.user-list.modal.user-view-modal', arguments: { userId: {{ $user->id }} }})" class="text-indigo-600 hover:text-indigo-900">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Links -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal for displaying user details - will be handled by the UserViewModal component -->
</div>
