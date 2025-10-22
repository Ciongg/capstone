<div x-data="{ fullscreenImageSrc: null, activeTab: 'tags' }">
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">
                    @if($isInstitutionAdmin)
                        Institution Demographics Management
                    @else
                        Tag & Topic Management
                    @endif
                </h2>

                <!-- Tab Navigation - Only visible for Super Admins -->
                @if(!$isInstitutionAdmin)
                <div class="mb-6">
                    <nav class="flex -mb-px">
                        <button 
                            @click="activeTab = 'tags'" 
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'tags', 'border-transparent text-gray-500 hover:text-gray-700': activeTab !== 'tags' }" 
                            class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                        >
                            Tag Management
                        </button>
                        <button 
                            @click="activeTab = 'topics'" 
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'topics', 'border-transparent text-gray-500 hover:text-gray-700': activeTab !== 'topics' }" 
                            class="w-1/2 py-3 px-1 text-center border-b-2 font-medium text-sm"
                        >
                            Survey Topics
                        </button>
                    </nav>
                </div>
                @endif

                <!-- Institution Admin View -->
                @if($isInstitutionAdmin)
                <div>
                    <!-- Status explanation notice -->
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                        <p><strong>Note:</strong> This page allows you to manage demographic categories and tags for your institution.
                            These tags are used for demographic targeting of surveys within your institution.</p>
                    </div>
                    
                    <!-- Empty state message for institution admins with no categories -->
                    @if($categories->isEmpty())
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">
                                    No demographics added yet. You can add one now by clicking the "Add Category" button.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Institution admin categories management content -->
                    <!-- ... Category management UI ... -->
                    
                    <!-- Add search and create buttons for institution admin -->
                    <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-2">
                        <div class="flex flex-col md:flex-row gap-2 flex-1 w-full md:w-auto">
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tags or categories..." 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select wire:model.live="selectedCategory" 
                                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Categories</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button 
                            wire:click="openCategoryModal(null, true)"
                            x-data
                            x-on:click="$dispatch('open-modal', { name: 'institution-category-modal' })"
                            class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-lg shadow-md transition-all duration-200 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Category
                        </button>
                    </div>
                    
                    <!-- Display categories and tags for institution admin -->
                    <div class="space-y-6">
                        @forelse ($categories as $category)
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                <div class="flex items-center justify-between bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $category->name }}</h3>
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            wire:click="openCategoryModal({{ $category->id }}, true)"
                                            x-data
                                            x-on:click="$dispatch('open-modal', { name: 'institution-category-modal' })"
                                            class="text-blue-600 hover:text-blue-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        <button 
                                            x-data
                                            x-on:click="Swal.fire({
                                                title: 'Are you sure?',
                                                text: 'You won\'t be able to revert this! All tags in this category will need to be deleted first.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#708090',
                                                confirmButtonText: 'Yes, delete it!',
                                                cancelButtonText: 'Cancel'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $wire.deleteCategory({{ $category->id }})
                                                }
                                            })"
                                            class="text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-sm text-gray-500">{{ $category->tags->count() }} tags in this category</span>
                                        <button 
                                            wire:click="openTagModal({{ $category->id }}, null, true)"
                                            x-data
                                            x-on:click="$dispatch('open-modal', { name: 'institution-tag-modal' })"
                                            class="px-3 py-1 bg-[#03b8ff] hover:bg-[#0299d5] text-white text-sm rounded-md shadow-sm transition-all duration-200 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Add Tag
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @forelse ($category->tags as $tag)
                                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-md border border-gray-200">
                                                <span>{{ $tag->name }}</span>
                                                <div class="flex items-center space-x-2">
                                                    <button 
                                                        wire:click="openTagModal({{ $category->id }}, {{ $tag->id }}, true)"
                                                        x-data
                                                        x-on:click="$dispatch('open-modal', { name: 'institution-tag-modal' })"
                                                        class="text-blue-600 hover:text-blue-800">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                    </button>
                                                    <button 
                                                        x-data
                                                        x-on:click="Swal.fire({
                                                            title: 'Are you sure?',
                                                            text: 'You won\'t be able to revert this! This tag will be permanently deleted.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ef4444',
                                                            cancelButtonColor: '#708090',
                                                            confirmButtonText: 'Yes, delete it!',
                                                            cancelButtonText: 'Cancel'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.deleteInstitutionTag({{ $tag->id }})
                                                            }
                                                        })"
                                                        class="text-red-600 hover:text-red-800">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full text-center py-4 text-gray-500 italic">
                                                No tags found for this category.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <p>No tag categories found. Create your first category to get started.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @else
                <!-- Super Admin View -->
                <div x-show="activeTab === 'tags'">
                    <!-- Status explanation notice -->
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                        <p><strong>Note:</strong> This page allows you to manage tag categories and their associated tags. These tags are used for user demographics and survey targeting.</p>
                    </div>
                    
                    <!-- Search and Create Button Row -->
                    <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-2">
                        <div class="flex flex-col md:flex-row gap-2 flex-1 w-full md:w-auto">
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tags or categories..." 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select wire:model.live="selectedCategory" 
                                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Categories</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button 
                            wire:click="openCategoryModal"
                            x-data
                            x-on:click="$dispatch('open-modal', { name: 'category-modal' })"
                            class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-lg shadow-md transition-all duration-200 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Category
                        </button>
                    </div>

                    <!-- Categories and Tags Display -->
                    <div class="space-y-6">
                        @forelse ($categories as $category)
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                <div class="flex items-center justify-between bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $category->name }}</h3>
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            wire:click="openCategoryModal({{ $category->id }})"
                                            x-data
                                            x-on:click="$dispatch('open-modal', { name: 'category-modal' })"
                                            class="text-blue-600 hover:text-blue-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        <button 
                                            x-data
                                            x-on:click="Swal.fire({
                                                title: 'Are you sure?',
                                                text: 'You won\'t be able to revert this! All tags in this category will need to be deleted first.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#708090',
                                                confirmButtonText: 'Yes, delete it!',
                                                cancelButtonText: 'Cancel'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $wire.deleteCategory({{ $category->id }})
                                                }
                                            })"
                                            class="text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-sm text-gray-500">{{ $category->tags->count() }} tags in this category</span>
                                        <button 
                                            wire:click="openTagModal({{ $category->id }})"
                                            x-data
                                            x-on:click="$dispatch('open-modal', { name: 'tag-modal' })"
                                            class="px-3 py-1 bg-[#03b8ff] hover:bg-[#0299d5] text-white text-sm rounded-md shadow-sm transition-all duration-200 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Add Tag
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @forelse ($category->tags as $tag)
                                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-md border border-gray-200">
                                                <span>{{ $tag->name }}</span>
                                                <div class="flex items-center space-x-2">
                                                    <button 
                                                        wire:click="openTagModal({{ $category->id }}, {{ $tag->id }})"
                                                        x-data
                                                        x-on:click="$dispatch('open-modal', { name: 'tag-modal' })"
                                                        class="text-blue-600 hover:text-blue-800">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                    </button>
                                                    <button 
                                                        x-data
                                                        x-on:click="Swal.fire({
                                                            title: 'Are you sure?',
                                                            text: 'You won\'t be able to revert this! This tag will be permanently deleted.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ef4444',
                                                            cancelButtonColor: '#708090',
                                                            confirmButtonText: 'Yes, delete it!',
                                                            cancelButtonText: 'Cancel'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.deleteTag({{ $tag->id }})
                                                            }
                                                        })"
                                                        class="text-red-600 hover:text-red-800">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full text-center py-4 text-gray-500 italic">
                                                No tags found for this category.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <p>No tag categories found. Create your first category to get started.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Survey Topics Tab Content - Only for Super Admins -->
                <div x-show="activeTab === 'topics'">
                    <livewire:super-admin.tags.topic-index />
                </div>
                @endif

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $categories->links() }}
                </div>

                <!-- Institution Admin Category Modal -->
                <x-modal name="institution-category-modal" title="{{ $categoryId ? 'Edit Category' : 'Create New Category' }}" focusable>
                    <div class="p-6 relative">
                        <div class="mb-4">
                            <label for="institutionCategoryName" class="block font-medium text-sm text-gray-700">Category Name</label>
                            <input id="institutionCategoryName" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                                type="text" wire:model="categoryName">
                            @error('categoryName') 
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('close-modal', { name: 'institution-category-modal' })"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                Cancel
                            </button>

                            <button
                                type="button"
                                wire:click="saveInstitutionCategory"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-[#03b8ff] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0299d5] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                {{ $categoryId ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </x-modal>

                <!-- Institution Admin Tag Modal -->
                <x-modal name="institution-tag-modal" title="{{ $tagId ? 'Edit Tag' : 'Create New Tag' }}" focusable>
                    <div class="p-6 relative">
                        <div class="mb-4">
                            <label for="institutionTagName" class="block font-medium text-sm text-gray-700">Tag Name</label>
                            <input id="institutionTagName" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                                type="text" wire:model="tagName">
                            @error('tagName') 
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="institutionTagCategoryId" class="block font-medium text-sm text-gray-700">Category</label>
                            <select id="institutionTagCategoryId" 
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                                    wire:model="tagCategoryId">
                                <option value="">Select a category</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('tagCategoryId') 
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('close-modal', { name: 'institution-tag-modal' })"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                Cancel
                            </button>

                            <button
                                type="button"
                                wire:click="saveInstitutionTag"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-[#03b8ff] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0299d5] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                {{ $tagId ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </x-modal>

                <!-- Existing Modals for Super Admin - keep them as they are -->
                <!-- Category Modal -->
                <x-modal name="category-modal" title="{{ $categoryId ? 'Edit Category' : 'Create New Category' }}" focusable>
                    <div class="p-6 relative">
                        <div class="mb-4">
                            <label for="categoryName" class="block font-medium text-sm text-gray-700">Category Name</label>
                            <input id="categoryName" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                                type="text" wire:model="categoryName">
                            @error('categoryName') 
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('close-modal', { name: 'category-modal' })"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                Cancel
                            </button>

                            <button
                                type="button"
                                wire:click="saveCategory"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-[#03b8ff] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0299d5] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                {{ $categoryId ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </x-modal>

                <!-- Tag Modal -->
                <x-modal name="tag-modal" title="{{ $tagId ? 'Edit Tag' : 'Create New Tag' }}" focusable>
                    <div class="p-6 relative">
                        <div class="mb-4">
                            <label for="tagName" class="block font-medium text-sm text-gray-700">Tag Name</label>
                            <input id="tagName" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                                type="text" wire:model="tagName">
                            @error('tagName') 
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tagCategoryId" class="block font-medium text-sm text-gray-700">Category</label>
                            <select id="tagCategoryId" 
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm px-4 py-2 border" 
                                    wire:model="tagCategoryId">
                                <option value="">Select a category</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('tagCategoryId') 
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('close-modal', { name: 'tag-modal' })"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                Cancel
                            </button>

                            <button
                                type="button"
                                wire:click="saveTag"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-[#03b8ff] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0299d5] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition">
                                {{ $tagId ? 'Update' : 'Create' }}
                            </button>
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
    document.addEventListener('livewire:initialized', () => {
        // Remove the confirm-delete event listeners since we're now handling deletion directly
        
        @this.on('category-saved', (message) => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 1800,
                showConfirmButton: false,
            });
            // Close the modal after successful save
            window.dispatchEvent(
                new CustomEvent('close-modal', { detail: { name: 'category-modal' } })
            );
        });
        
        @this.on('tag-saved', (message) => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 1800,
                showConfirmButton: false,
            });
            // Close the modal after successful save
            window.dispatchEvent(
                new CustomEvent('close-modal', { detail: { name: 'tag-modal' } })
            );
        });
        
        @this.on('category-deleted', () => {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Category deleted successfully.',
                timer: 1800,
                showConfirmButton: false,
            });
        });
        
        @this.on('tag-deleted', () => {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Tag deleted successfully.',
                timer: 1800,
                showConfirmButton: false,
            });
        });
        
        @this.on('category-has-tags', () => {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Delete',
                text: 'This category has associated tags. Please delete the tags first.',
                confirmButtonColor: '#3085d6',
            });
        });
        
        @this.on('tag-in-use', () => {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Delete',
                text: 'This tag is currently in use by users or surveys and cannot be deleted.',
                confirmButtonColor: '#3085d6',
            });
        });
        
        @this.on('institution-category-saved', (message) => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 1800,
                showConfirmButton: false,
            });
            // Close the modal after successful save
            window.dispatchEvent(
                new CustomEvent('close-modal', { detail: { name: 'institution-category-modal' } })
            );
        });
        
        @this.on('institution-tag-saved', (message) => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 1800,
                showConfirmButton: false,
            });
            // Close the modal after successful save
            window.dispatchEvent(
                new CustomEvent('close-modal', { detail: { name: 'institution-tag-modal' } })
            );
        });
    });
</script>
@endpush
