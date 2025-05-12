<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Institution Demographics</h2>

    @if(!$institution)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
            <p>Your institution is not configured yet. Please contact system administration.</p>
        </div>
    @else
        {{-- Create New Demographic Category --}}
        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Add New Demographic Category</h3>
            
            <div class="mb-4">
                <label for="newCategoryName" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input 
                    type="text" 
                    id="newCategoryName" 
                    wire:model="newCategoryName" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g., Department, Age Group, Education Level"
                >
                @error('newCategoryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Demographic Options</label>
                @error('newTagNames') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                
                @foreach($newTagNames as $index => $tagName)
                    <div class="flex items-center mb-2" wire:key="new-tag-{{ $index }}">
                        <input 
                            type="text" 
                            wire:model="newTagNames.{{ $index }}" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Option {{ $index + 1 }}"
                        >
                        <button 
                            type="button"
                            wire:click="removeTagField({{ $index }})"
                            class="ml-2 p-1 text-red-600 hover:text-red-800 {{ count($newTagNames) <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ count($newTagNames) <= 1 ? 'disabled' : '' }}
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @error("newTagNames.{$index}") <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endforeach
                
                <div class="mt-2">
                    <button 
                        type="button" 
                        wire:click="addTagField" 
                        class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Option
                    </button>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button 
                    type="button" 
                    wire:click="addCategory" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Create Demographic Category
                </button>
            </div>
        </div>

        {{-- Existing Categories --}}
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Existing Demographics</h3>
        
        @if(empty($categories))
            <p class="text-gray-500 italic">No demographic categories have been created yet.</p>
        @else
            <div class="space-y-6">
                @foreach($categories as $category)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm" wire:key="category-{{ $category['id'] }}">
                        <div class="flex justify-between items-center mb-4">
                            @if($editingCategoryId === $category['id'])
                                <div class="flex-grow mr-4">
                                    <input 
                                        type="text" 
                                        wire:model="editingCategoryName" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="flex space-x-2">
                                    <button 
                                        type="button" 
                                        wire:click="updateCategory" 
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                                    >
                                        Save
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="cancelEditing" 
                                        class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <h4 class="text-md font-semibold text-gray-800">{{ $category['name'] }}</h4>
                                <div class="flex space-x-2">
                                    <button 
                                        type="button" 
                                        wire:click="startEditingCategory({{ $category['id'] }})" 
                                        class="text-blue-600 hover:text-blue-800"
                                        title="Edit category"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="deleteCategory({{ $category['id'] }})" 
                                        wire:confirm="Are you sure you want to delete this category and all its options? This cannot be undone."
                                        class="text-red-600 hover:text-red-800"
                                        title="Delete category"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        <div class="pl-2 border-l-2 border-blue-200 space-y-2">
                            @forelse($category['tags'] as $tag)
                                <div class="flex items-center group" wire:key="tag-{{ $tag['id'] }}">
                                    <input 
                                        type="text" 
                                        value="{{ $tag['name'] }}" 
                                        wire:change="updateTag({{ $tag['id'] }}, $event.target.value)"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <button 
                                        type="button"
                                        wire:click="deleteTag({{ $tag['id'] }})"
                                        class="ml-2 p-1 text-red-600 hover:text-red-800 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 italic">No options in this category.</p>
                            @endforelse
                            
                            <button 
                                type="button" 
                                wire:click="addTagToCategory({{ $category['id'] }})" 
                                class="mt-2 inline-flex items-center px-2 py-1 text-sm text-blue-600 hover:text-blue-800"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Option
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
