<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Announcement Management</h2>

                <!-- Status explanation notice -->
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                    <p><strong>Note:</strong> This page shows all announcements. Use this page to manage announcement records.</p>
                </div>
                
                <!-- Search and Create Button Row -->
                <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-2">
                    <div class="flex flex-col md:flex-row gap-2 flex-1 w-full md:w-auto">
                        <input wire:model.debounce.300ms="search" type="text" placeholder="Search announcements..." 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select wire:model="audienceFilter" 
                                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Audiences</option>
                            <option value="public">Public</option>
                            <option value="institution_specific">Institution Specific</option>
                        </select>
                    </div>
                    <button 
                        x-data
                        x-on:click="$wire.openCreateModal(); $dispatch('open-modal', { name: 'create-announcement-modal' })"
                        class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-lg shadow-md transition-all duration-200 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Announcement
                    </button>
                </div>

                <!-- Announcements Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($announcements as $announcement)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col h-full">
                        @if($announcement->image_path)
                            <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                                 alt="{{ $announcement->title }}" 
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 flex items-center justify-center bg-gray-50 border-b border-gray-200">
                                <span class="italic text-gray-400">No image</span>
                            </div>
                        @endif
                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold">{{ $announcement->title }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full {{ $announcement->target_audience === 'public' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $announcement->target_audience === 'public' ? 'Public' : 'Institution' }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $announcement->description }}</p>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                <span>
                                    Created at: {{ $announcement->created_at->format('M d, Y H:i') }}
                                </span>
                                <span>{{ $announcement->active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            
                            <div class="mt-auto flex justify-end space-x-2 pt-2">
                                <button 
                                    x-data
                                    x-on:click="$wire.set('selectedAnnouncementId', null).then(() => {
                                        $wire.set('selectedAnnouncementId', {{ $announcement->id }});
                                        $nextTick(() => $dispatch('open-modal', { name: 'manage-announcement-modal' }));
                                    })"
                                    class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm flex items-center">
                                    Update
                                </button>
                                <button
                                    type="button"
                                    x-data
                                    x-on:click="Swal.fire({
                                        title: 'Are you sure?',
                                        text: 'Do you want to delete this announcement? This action cannot be undone.',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, delete it!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $wire.deleteAnnouncement({{ $announcement->id }});
                                        }
                                    })"
                                    class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded text-sm flex items-center">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12 text-center text-gray-500">
                        <p>No announcements found. Create your first announcement to get started.</p>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $announcements->links() }}
                </div>

                <!-- Modal for creating announcement -->
                <x-modal name="create-announcement-modal" title="Create Announcement" focusable>
                    <div class="p-6 relative min-h-[200px] flex flex-col">
                        <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50">
                            <div class="mt-[160px] flex flex-col items-center justify-center h-full">
                                <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                                <p class="text-sm text-gray-600">Loading...</p>
                            </div>
                        </div>
                        <div wire:loading.remove class="flex-1">
                            @livewire('super-admin.announcements.modal.create-announcement-modal', [], key('create-announcement-modal-' . now()))
                        </div>
                    </div>
                </x-modal>

                <!-- Modal for managing announcement -->
                <x-modal name="manage-announcement-modal" title="Manage Announcement" focusable>
                    <div class="p-6 relative min-h-[200px] flex flex-col">
                        <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50">
                            <div class="mt-[160px] flex flex-col items-center justify-center h-full">
                                <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                                <p class="text-sm text-gray-600">Loading...</p>
                            </div>
                        </div>
                        <div wire:loading.remove class="flex-1">
                            @if($selectedAnnouncementId)
                                @livewire('super-admin.announcements.modal.manage-announcement-modal', ['announcementId' => $selectedAnnouncementId], key('manage-announcement-modal-' . $selectedAnnouncementId . '-' . $manageModalKey))
                            @else
                                <p class="text-gray-500">No announcement selected.</p>
                            @endif
                        </div>
                    </div>
                </x-modal>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('announcement-deleted', function () {
        Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Announcement deleted successfully.',
            timer: 1800,
            showConfirmButton: false,
        });
    });
    
    window.addEventListener('announcement-updated-success', function () {
        Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: 'Announcement updated successfully.',
            timer: 1800,
            showConfirmButton: false,
        });
    });
    
 
</script>
@endpush
