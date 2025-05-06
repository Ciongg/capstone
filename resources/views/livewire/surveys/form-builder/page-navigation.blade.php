<div>
    @if ($pages->isEmpty())
        <div class="text-center mt-6">
            <button 
                wire:click="addPage"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                + Add Page
            </button>
        </div>
    @else
        <div class="flex space-x-4 items-center">
            @foreach ($pages as $page)
                <button 
                    wire:click="setActivePage({{ $page->id }})"
                    class="px-4 py-2 rounded {{ $activePageId === $page->id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    Page {{ $page->page_number }}
                </button>
            @endforeach
            
            <button 
                wire:click="addPage"
                class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300"
            >
                + Add Page
            </button>
        </div>
    @endif
</div>
