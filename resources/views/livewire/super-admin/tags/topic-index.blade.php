<div>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
        <p><strong>Note:</strong> Manage survey topics that researchers can select when creating surveys.</p>
    </div>

    <!-- Search and Create Button Row -->
    <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-2">
        <div class="flex-1 w-full">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search topics..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button 
            wire:click="openTopicModal"
            x-data
            x-on:click="$dispatch('open-modal', { name: 'topic-modal' })"
            class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-lg shadow-md transition-all duration-200 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Topic
        </button>
    </div>

    <!-- Topics List -->
    <div class="mt-6 bg-white shadow overflow-hidden rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse ($topics as $topic)
                <li class="px-6 py-4 flex items-center justify-between">
                    <div class="text-sm font-medium text-gray-900">{{ $topic->name }}</div>
                    <div class="flex items-center space-x-3">
                        <button 
                            wire:click="openTopicModal({{ $topic->id }})"
                            x-data
                            x-on:click="$dispatch('open-modal', { name: 'topic-modal' })"
                            class="text-blue-600 hover:text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                        <button 
                            x-data
                            x-on:click="Swal.fire({
                                title: 'Are you sure?',
                                text: 'You won\'t be able to revert this! This may affect associated surveys.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#708090',
                                confirmButtonText: 'Yes, delete it!',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $wire.deleteTopic({{ $topic->id }})
                                }
                            })"
                            class="text-red-600 hover:text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-gray-500 italic">
                    No survey topics found. Add your first topic to get started.
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $topics->links() }}
    </div>

    <!-- Topic Modal -->
    <x-modal name="topic-modal" title="{{ $topicId ? 'Edit Topic' : 'Create New Topic' }}" focusable>
        <div class="p-6 relative">
            <div class="mb-4">
                <label for="topicName" class="block font-medium text-sm text-gray-700">Topic Name</label>
                <input id="topicName" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                       type="text" wire:model="topicName">
                @error('topicName') 
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('close-modal', { name: 'topic-modal' })"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                    Cancel
                </button>

                <button
                    type="button"
                    wire:click="saveTopic"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-[#03b8ff] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0299d5] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                    {{ $topicId ? 'Update' : 'Create' }}
                </button>
            </div>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('topic-saved', (message) => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 1800,
                showConfirmButton: false,
            });
            // Close the modal after successful save
            window.dispatchEvent(
                new CustomEvent('close-modal', { detail: { name: 'topic-modal' } })
            );
        });
        
        @this.on('topic-deleted', () => {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Topic deleted successfully.',
                timer: 1800,
                showConfirmButton: false,
            });
        });
        
        @this.on('topic-in-use', () => {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Delete',
                text: 'This topic is currently in use by surveys and cannot be deleted.',
                confirmButtonColor: '#3085d6',
            });
        });
    });
</script>
@endpush
