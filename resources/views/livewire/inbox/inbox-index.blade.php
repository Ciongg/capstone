<div>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-800">Inbox</h1>
                
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Filter Options --}}
                    <div class="flex rounded-md shadow-sm">
                        <button 
                            wire:click="filterMessages('all')" 
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-l-md border border-gray-300 {{ $filter === 'all' ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                        >
                            All
                        </button>
                        <button 
                            wire:click="filterMessages('unread')" 
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-r-md border border-gray-300 {{ $filter === 'unread' ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                        >
                            Unread
                        </button>
                    </div>
                    
                    <div class="flex gap-2">
                        @if ($unreadCount > 0)
                            <div class="bg-blue-100 rounded-md">
                                <button 
                                    wire:click="markAllAsRead" 
                                    class="px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 rounded-md"
                                >
                                    Mark all as read
                                </button>
                            </div>
                        @endif
                        
                        @if ($hasAnyMessages) {{-- Changed condition to use the new property --}}
                            <div class="bg-red-100 rounded-md">
                                <button 
                                    wire:click="clearInbox" 
                                    wire:confirm="Are you sure you want to clear your inbox? This action cannot be undone."
                                    class="px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-200 rounded-md"
                                >
                                    Clear inbox
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div>
                @forelse ($messages as $message)
                    <div class="px-6 py-4 border-b {{ is_null($message->read_at) ? 'bg-blue-50' : '' }}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-4">
                                <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white">
                                    {{ substr($message->sender->name ?? 'U', 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-medium {{ is_null($message->read_at) ? 'text-blue-600' : 'text-gray-900' }}">
                                        {{ $message->subject }}
                                    </h3>
                                    <span class="text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-gray-600 mt-1 whitespace-pre-line">
                                    {{ $message->message }}
                                </div>
                                
                                <div class="mt-2 flex items-center space-x-4">
                                    @if ($message->url)
                                    <a href="{{ $message->url }}" class="text-sm text-blue-600 hover:underline">
                                        View related content
                                    </a>
                                    @endif
                                    
                                    @if (is_null($message->read_at))
                                        <button 
                                            wire:click="markAsRead({{ $message->id }})"
                                            class="text-sm text-gray-500 hover:text-gray-700"
                                        >
                                            Mark as read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        <p>You have no messages in your inbox.</p>
                    </div>
                @endforelse
                
                <div class="px-6 py-3">
                    {{ $messages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
