@if($mobileMode)
    {{-- Mobile badge only - always include a div as root even when empty --}}
    <div class="mobile-badge">
        @if ($unreadCount > 0)
            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </div>
@else
    {{-- Desktop full dropdown --}}
    <div x-data="{ open: false }" @click.away="open = false" class="relative">
        <button 
            @click="open = !open" 
            class="text-gray-700 hover:text-[#03b8ff] relative"
            title="Inbox"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
            </svg>
            {{-- Inbox Badge (shows unread count) --}}
            @if ($unreadCount > 0)
                <span class="absolute top-[-6px] right-[-6px] bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>
        
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-200" 
            x-transition:enter-start="transform opacity-0 scale-95" 
            x-transition:enter-end="transform opacity-100 scale-100" 
            x-transition:leave="transition ease-in duration-100" 
            x-transition:leave-start="transform opacity-100 scale-100" 
            x-transition:leave-end="transform opacity-0 scale-95" 
            class="absolute right-0 z-50 mt-2 w-[28rem] bg-white rounded-md shadow-lg overflow-hidden"
            style="display: none;"
        >
            <div class="py-2">
                <div class="px-4 py-2 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Inbox</h3>
                    @if ($unreadCount > 0)
                        <button wire:click="markAllAsRead" class="text-sm text-blue-600 hover:text-blue-800">
                            Mark all as read
                        </button>
                    @endif
                </div>
                
                <div class="max-h-80 overflow-y-auto">
                    @forelse($messages as $message)
                        <a 
                            href="{{ $message->url ?? route('inbox.show', $message->uuid) }}" 
                            class="block px-4 py-3 hover:bg-gray-50 border-b flex items-start {{ is_null($message->read_at) ? 'bg-blue-50' : '' }}"
                            x-data
                            @click.prevent="
                                $wire.markAsRead({{ $message->id }}).then(() => {
                                    window.location = '{{ $message->url ?? route('inbox.show', $message->uuid) }}';
                                });
                            "
                        >
                            <div class="flex-shrink-0 mr-3">
                                <div class="w-10 h-10 rounded-full bg-blue-400 flex items-center justify-center text-white">
                                    {{ substr($message->sender->name ?? 'Formigo', 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow">
                                <p class="font-medium {{ is_null($message->read_at) ? 'text-blue-600' : '' }}">
                                    {{ $message->subject }}
                                </p>
                                <p class="text-sm text-gray-600 truncate">
                                    {{ Str::limit($message->message, 50) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $message->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-6 text-center text-gray-500">
                            No messages yet
                        </div>
                    @endforelse
                </div>
                
                <div class="px-4 py-2 border-t text-center">
                    <a href="{{ route('inbox.index') }}" class="text-sm text-blue-600 hover:underline">
                        See All Messages
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
