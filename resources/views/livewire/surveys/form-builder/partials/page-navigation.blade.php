<div class="space-y-6 min-w-[300px]">
    
    {{-- Alpine.js Data Initialization --}}
    <!-- Sticky Page Selector Container -->
    <div class="sticky top-0 z-30 bg-white shadow px-3 sm:px-6 py-3 mb-4 rounded overflow-hidden min-w-[300px]">
                <!-- Page Selector -->
                @if ($pages->isEmpty())
                    <div class="text-center">
                        <button
                            wire:click="addItem('page')"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center justify-center w-32 h-10"
                            wire:loading.attr="disabled"
                            wire:target="addItem('page')"
                        >
                            <span wire:loading.remove wire:target="addItem('page')">+ Add Page</span>
                            <svg wire:loading wire:target="addItem('page')" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </button>
                    </div>
                @else
                    <div class="flex items-center space-x-1 overflow-x-auto py-1 scrollbar-hide">
                        @foreach ($pages as $page)
                            <div wire:key="sticky-page-{{ $page->id }}" class="flex items-center group flex-shrink-0">
                                {{-- Page Button --}}
                                <button
                                   x-on:click="selectedQuestionId = null; activePageId = {{ $page->id }}; $wire.setActivePage({{ $page->id }});"
                                    type="button"
                                    :class="{
                                        'px-2 sm:px-3 py-1 sm:py-2 rounded cursor-pointer transition duration-150 ease-in-out whitespace-nowrap text-sm': true,
                                        'bg-blue-500 text-white hover:bg-blue-600': activePageId === {{ $page->id }},
                                        'bg-gray-200 text-gray-700 hover:bg-gray-300': activePageId !== {{ $page->id }}
                                    }"
                                    title="{{ $page->title ?: 'Page ' . $page->page_number }}"
                                >
                                    {{ Str::limit($page->title ?: 'Page ' . $page->page_number, 12) }}
                                </button>

                                
                                {{-- Reorder Buttons (Only show if page is active) --}}
                                <div
                                    x-show="activePageId === {{ $page->id }}"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="flex flex-col ml-1 flex-shrink-0"
                                    x-cloak
                                >
                                    <button
                                        wire:click.stop="movePageUp({{ $page->id }})"
                                        type="button"
                                        class="px-1 py-0 text-xs rounded-tr {{ $loop->first ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }}"
                                        {{ $loop->first ? 'disabled' : '' }}
                                        aria-label="Move page up"
                                        wire:loading.attr="disabled"
                                        wire:target="movePageUp({{ $page->id }})"
                                    >
                                        <span wire:loading.remove wire:target="movePageUp({{ $page->id }})">▲</span>
                                        <span wire:loading wire:target="movePageUp({{ $page->id }})">
                                            <svg class="animate-spin h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button
                                        wire:click.stop="movePageDown({{ $page->id }})"
                                        type="button"
                                        class="px-1 py-0 text-xs rounded-br {{ $loop->last ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }}"
                                        {{ $loop->last ? 'disabled' : '' }}
                                        aria-label="Move page down"
                                        wire:loading.attr="disabled"
                                        wire:target="movePageDown({{ $page->id }})"
                                    >
                                        <span wire:loading.remove wire:target="movePageDown({{ $page->id }})">▼</span>
                                        <span wire:loading wire:target="movePageDown({{ $page->id }})">
                                            <svg class="animate-spin h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        {{-- Add Page Button --}}
                        <button
                            wire:click="addItem('page')"
                            class="ml-2 sm:ml-4 px-2 sm:px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex-shrink-0 flex items-center justify-center w-24 h-8"
                            wire:loading.attr="disabled"
                            wire:target="addItem('page')"
                        >
                            <span wire:loading.remove wire:target="addItem('page')">+ Add Page</span>
                            <svg wire:loading wire:target="addItem('page')" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>