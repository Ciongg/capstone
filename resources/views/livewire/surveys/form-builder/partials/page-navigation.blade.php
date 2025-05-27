<div class="space-y-6">
    
    {{-- Alpine.js Data Initialization --}}
    <!-- Sticky Page Selector Container -->
    <div class="sticky top-0 z-30 bg-white shadow px-6 py-3 mb-4 rounded">
                <div class="flex flex-col gap-2">
                    <div>
                        Selected Question: {{$selectedQuestionId}}
                        
                    </div>
                    <div>
                        Selected Page: {{$activePageId}}
            
                    </div>
                </div>
                <!-- Page Selector -->
                @if ($pages->isEmpty())
                    <div class="text-center">
                        <button
                            wire:click="addPage"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            + Add Page
                        </button>
                    </div>
                @else
                    <div class="flex items-center space-x-1 overflow-x-auto py-1">
                        @foreach ($pages as $page)
                            <div wire:key="sticky-page-{{ $page->id }}" class="flex items-center group flex-shrink-0">
                                {{-- Page Button --}}
                                <button
                                    x-on:click="
                                        const newActivePageId = {{ $page->id }};
                                        if (activePageId !== newActivePageId) {
                                            selectedQuestionId = null; 
                                            activePageId = newActivePageId; 
                                            $wire.setActivePage(newActivePageId); 
                                        }
                                    "
                                    type="button"
                                    :class="{
                                        'px-3 py-2 rounded cursor-pointer transition duration-150 ease-in-out whitespace-nowrap': true,
                                        'bg-blue-500 text-white hover:bg-blue-600': activePageId === {{ $page->id }},
                                        'bg-gray-200 text-gray-700 hover:bg-gray-300': activePageId !== {{ $page->id }}
                                    }"
                                    title="{{ $page->title ?: 'Page ' . $page->page_number }}"
                                >
                                    {{ Str::limit($page->title ?: 'Page ' . $page->page_number, 16) }}
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
                                    >
                                        ▲
                                    </button>
                                    <button
                                        wire:click.stop="movePageDown({{ $page->id }})"
                                        type="button"
                                        class="px-1 py-0 text-xs rounded-br {{ $loop->last ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800' }}"
                                        {{ $loop->last ? 'disabled' : '' }}
                                        aria-label="Move page down"
                                    >
                                        ▼
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        {{-- Add Page Button --}}
                        <button
                            wire:click="addPage"
                            class="ml-4 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex-shrink-0"
                        >
                            + Add Page
                        </button>
                    </div>
                @endif
            </div>