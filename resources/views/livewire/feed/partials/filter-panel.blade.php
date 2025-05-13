{{-- Filter Panel - Controlled by Livewire --}}
@if($showFilterPanel)
<div 
    class="mb-6 p-4 bg-white rounded-lg shadow-md border border-gray-100"
    wire:transition.origin.top.left
>
    <div class="mb-3 flex items-center justify-between">
        <h3 class="font-medium text-gray-700">Filter Surveys by Tag</h3>
        <button wire:click="$set('showFilterPanel', false)" class="text-gray-400 hover:text-gray-600" title="Close panel">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    @if($activePanelTagId && ($tag = \App\Models\Tag::find($activePanelTagId)))
        <div class="mb-4 p-2 bg-blue-50 border border-blue-100 rounded-md">
            <div class="flex items-center justify-between">
                <span class="text-sm text-blue-700">Selected: {{ $tag->name }}</span>
                <button wire:click="clearPanelTagFilter" class="text-xs text-blue-600 hover:text-blue-800 underline">
                    Clear this tag
                </button>
            </div>
        </div>
    @endif
    
    <div class="space-y-4 max-h-96 overflow-y-auto">
        @forelse($tagCategories as $category)
            <div wire:key="filter-category-{{ $category->id }}">
                <h4 class="font-semibold text-gray-600 mb-2">{{ $category->name }}</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    @foreach($category->tags as $tag)
                        <button 
                            wire:click="togglePanelTagFilter({{ $tag->id }})"
                            wire:key="filter-tag-{{ $tag->id }}"
                            type="button"
                            class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors duration-150
                                   {{ $activePanelTagId == $tag->id 
                                       ? 'bg-blue-500 text-white font-semibold shadow-md' 
                                       : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                        >
                            {{ $tag->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-gray-500">No tags available for filtering.</p>
        @endforelse
    </div>
    
    <div class="mt-6 flex justify-end space-x-2">
        <button 
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
            wire:click="$set('showFilterPanel', false)"
        >
            Cancel
        </button>
        <button 
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
            wire:click="$set('showFilterPanel', false)"
        >
            Done
        </button>
    </div>
</div>
@endif