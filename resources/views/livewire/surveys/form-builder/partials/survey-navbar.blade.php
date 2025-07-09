<div class="bg-white shadow flex items-center justify-between px-3 sm:px-6 py-3 mb-4 min-w-[300px]">
    {{-- Left Side: Title --}}
    <div class="flex items-center flex-1 min-w-0 mr-4">
        <input
            type="text"
            value="{{ $surveyTitle }}"
            class="text-sm sm:text-base font-bold border-b border-gray-300 outline-none bg-transparent py-1 cursor-default w-full min-w-0"
            style="max-width: 80%;"
            placeholder="Untitled Survey"
            readonly
        />
    </div>

    {{-- Right Side: Buttons & Status --}}
    <div x-data="{ open: false }" class="relative flex-shrink-0">
        {{-- Buttons visible on large screens and up --}}
        <div class="hidden lg:flex items-center space-x-3">
            {{-- Display Status --}}
            <span @class([
                'inline-flex items-center h-9 px-3 py-1.5 text-xs font-semibold rounded-full',
                'bg-gray-100 text-gray-700' => $survey->status === 'pending',
                'bg-blue-100 text-blue-700' => $survey->status === 'published',
                'bg-amber-100 text-amber-700' => $survey->status === 'ongoing',
                'bg-green-100 text-green-700' => $survey->status === 'finished',
                'bg-red-100 text-red-800' => $survey->status === 'closed',
                'bg-gray-100 text-gray-800' => !in_array($survey->status, ['pending', 'published', 'ongoing', 'finished', 'closed']),
            ])>
                Status: {{ ucfirst($survey->status) }}
            </span>
            {{-- Publish/Unpublish Buttons - disabled when locked --}}
            @if($survey->status === 'published')
                {{-- Show unpublish button only if survey is not yet ongoing (no responses yet) --}}
                @if($survey->status !== 'ongoing')
                    <button
                        type="button"
                        x-on:click="confirmUnpublish()"
                        class="inline-flex items-center h-9 px-4 py-1.5 bg-yellow-100 text-yellow-700 text-sm font-medium rounded hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                        @if($survey->is_locked) disabled @endif
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        Unpublish
                    </button>
                @endif
                <a href="{{ route('surveys.preview', $survey->id) }}" wire:navigate
                class="px-4 py-2 bg-blue-100 text-gray-700 rounded hover:bg-blue-200 flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Preview
                </a>
            @elseif($survey->status === 'pending')
                <button
                    type="button"
                    x-on:click="confirmPublish()"
                    class="inline-flex items-center h-9 px-4 py-1.5 bg-green-100 text-green-700 text-sm font-medium rounded hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    @if($survey->is_locked) disabled @endif
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.893 13.393l-1.135-1.135a2.252 2.252 0 01-.421-.585l-1.08-2.16a.414.414 0 00-.663-.107.827.827 0 01-.812.21l-1.273-.363a.89.89 0 00-.738 1.595l.587.39c.59.395.674 1.23.172 1.732l-.2.2c-.212.212-.33.498-.33.796v.41c0 .409-.11.809-.32 1.158l-1.315 2.191a2.11 2.11 0 01-1.81 1.025 1.055 1.055 0 01-1.055-1.055v-1.172c0-.92-.56-1.747-1.414-2.089l-.655-.261a2.25 2.25 0 01-1.383-2.46l.007-.042a2.25 2.25 0 01.29-.787l.09-.15a2.25 2.25 0 012.37-1.048l1.178.236a1.125 1.125 0 001.302-.795l.208-.73a1.125 1.125 0 00-.578-1.315l-.665-.332-.091.091a2.25 2.25 0 01-1.591.659h-.18c-.249 0-.487.1-.662.274a.931.931 0 01-1.458-1.137l1.411-2.353a2.25 2.25 0 00.286-.76m11.928 9.869A9 9 0 008.965 3.525m11.928 9.868A9 9 0 118.965 3.525" />
                    </svg>
                    Publish
                </button>
                <a href="{{ route('surveys.preview', $survey->id) }}" wire:navigate
                class="px-4 py-2 bg-blue-100 text-gray-700 rounded hover:bg-blue-200 flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Preview
                </a>
            @elseif($survey->status === 'ongoing')
                {{-- Don't show unpublish button for ongoing surveys --}}
                <a href="{{ route('surveys.preview', $survey->id) }}" wire:navigate
                class="px-4 py-2 bg-blue-100 text-gray-700 rounded hover:bg-blue-200 flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Preview
                </a>
            @endif
            
            {{-- View Responses Button - still accessible when locked --}}
            @if($hasResponses)
            <a href="{{ route('surveys.responses', $survey->id) }}"
               class="px-4 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                View Responses
            </a>
            @endif


            {{-- Delete All Button - Only show when NOT ongoing --}}
            @if($survey->status !== 'ongoing')
                <button
                    type="button"
                    x-on:click="confirmReset()"
                    class="px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 flex items-center"
                    title="Delete all questions and pages"
                    @if($survey->is_locked) disabled @endif
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Reset
                </button>
            @endif

            {{-- Survey Settings Button - Only show when NOT ongoing --}}
            @if($survey->status !== 'ongoing')
                <button
                    x-data
                    x-on:click="$dispatch('open-modal', {name : 'survey-settings-modal-{{ $survey->id }}'})"
                    class="flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    title="Survey Settings"
                    @if($survey->is_locked) disabled @endif
                >
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.646.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 1.255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.333.184-.583.496-.646.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.646-.87-.074-.04-.147-.083-.22-.127-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.759 6.759 0 0 1 0-1.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.184.582-.496.646-.87l.213-1.281Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Settings
                </button>
            @endif
        </div>

        {{-- Hamburger Menu Button (visible below large screens) --}}
        <button x-on:click="open = !open" class="lg:hidden p-2 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        {{-- Bottom Modal Menu (visible below large screens when open) --}}
        <div
            x-show="open"
            x-on:click.away="open = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-full"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-full"
            class="fixed inset-x-0 bottom-0 z-[100] bg-white rounded-t-xl shadow-xl lg:hidden"
            style="display: none; height: 50vh;" {{-- Prevents flash of content before Alpine initializes --}}
        >
            {{-- Close button --}}
            <div class="flex justify-end p-4 border-b border-gray-200">
                <button @click="open = false" class="p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Status indicator --}}
            <div class="flex justify-center py-3">
                <span @class([
                    'inline-flex items-center px-4 py-1.5 text-sm font-semibold rounded-full',
                    'bg-gray-100 text-gray-700' => $survey->status === 'pending',
                    'bg-blue-100 text-blue-700' => $survey->status === 'published',
                    'bg-amber-100 text-amber-700' => $survey->status === 'ongoing',
                    'bg-green-100 text-green-700' => $survey->status === 'finished',
                    'bg-red-100 text-red-800' => $survey->status === 'closed',
                    'bg-gray-100 text-gray-800' => !in_array($survey->status, ['pending', 'published', 'ongoing', 'finished', 'closed']),
                ])>
                    Status: {{ ucfirst($survey->status) }}
                </span>
            </div>

            {{-- Menu buttons in rows --}}
            <div class="p-4 overflow-y-auto" style="max-height: calc(50vh - 100px);">
                <div class="grid grid-cols-2 gap-3">
                    {{-- Preview Button --}}
                    <a href="{{ route('surveys.preview', $survey->id) }}" wire:navigate
                       class="flex flex-col items-center justify-center p-3 text-center bg-blue-100 rounded-lg hover:bg-blue-200">
                       <svg class="w-6 h-6 mb-2 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                           <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                       </svg>
                       <span class="text-sm font-medium text-gray-700">Preview</span>
                    </a>

                    {{-- View Responses Button --}}
                    @if($hasResponses)
                        <a href="{{ route('surveys.responses', $survey->id) }}"
                           class="flex flex-col items-center justify-center p-3 text-center bg-blue-100 rounded-lg hover:bg-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-2 text-blue-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                            </svg>
                            <span class="text-sm font-medium text-blue-700">View Responses</span>
                        </a>
                    @endif

                    {{-- Publish/Unpublish Buttons - disabled when locked --}}
                    @if($survey->status === 'published')
                        {{-- Show unpublish button only if survey is not yet ongoing (no responses yet) --}}
                        @if($survey->status !== 'ongoing')
                            <button
                                type="button"
                                x-on:click="open = false; confirmUnpublish()"
                                class="flex flex-col items-center justify-center p-3 text-center bg-yellow-100 rounded-lg hover:bg-yellow-200"
                                @if($survey->is_locked) disabled @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-2 text-yellow-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <span class="text-sm font-medium text-yellow-700">Unpublish</span>
                            </button>
                        @endif
                    @elseif($survey->status === 'pending')
                        <button
                            type="button"
                            x-on:click="open = false; confirmPublish()"
                            class="flex flex-col items-center justify-center p-3 text-center bg-green-100 rounded-lg hover:bg-green-200"
                            @if($survey->is_locked) disabled @endif
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-2 text-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.893 13.393l-1.135-1.135a2.252 2.252 0 01-.421-.585l-1.08-2.16a.414.414 0 00-.663-.107.827.827 0 01-.812.21l-1.273-.363a.89.89 0 00-.738 1.595l.587.39c.59.395.674 1.23.172 1.732l-.2.2c-.212.212-.33.498-.33.796v.41c0 .409-.11.809-.32 1.158l-1.315 2.191a2.11 2.11 0 01-1.81 1.025 1.055 1.055 0 01-1.055-1.055v-1.172c0-.92-.56-1.747-1.414-2.089l-.655-.261a2.25 2.25 0 01-1.383-2.46l.007-.042a2.25 2.25 0 01.29-.787l.09-.15a2.25 2.25 0 012.37-1.048l1.178.236a1.125 1.125 0 001.302-.795l.208-.73a1.125 1.125 0 00-.578-1.315l-.665-.332-.091.091a2.25 2.25 0 01-1.591.659h-.18c-.249 0-.487.1-.662.274a.931.931 0 01-1.458-1.137l1.411-2.353a2.25 2.25 0 00.286-.76m11.928 9.869A9 9 0 008.965 3.525m11.928 9.868A9 9 0 118.965 3.525" />
                            </svg>
                            <span class="text-sm font-medium text-green-700">Publish</span>
                        </button>
                    @endif

                    {{-- Delete All Button - Only show when NOT ongoing --}}
                    @if($survey->status !== 'ongoing')
                        <button
                            type="button"
                            x-on:click="open = false; confirmReset()"
                            class="flex flex-col items-center justify-center p-3 text-center bg-red-100 rounded-lg hover:bg-red-200"
                            @if($survey->is_locked) disabled @endif
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-2 text-red-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span class="text-sm font-medium text-red-700">Reset</span>
                        </button>
                    @endif

                    {{-- Settings Button - Only show when NOT ongoing --}}
                    @if($survey->status !== 'ongoing')
                        <button
                            x-data
                            x-on:click="open = false; $dispatch('open-modal', {name : 'survey-settings-modal-{{ $survey->id }}'})"
                            class="flex flex-col items-center justify-center p-3 text-center bg-gray-100 rounded-lg hover:bg-gray-200"
                            @if($survey->is_locked) disabled @endif
                        >
                            <svg class="w-6 h-6 mb-2 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.646.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 1.255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.333.184-.583.496-.646.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.646-.87-.074-.04-.147-.083-.22-.127-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.759 6.759 0 0 1 0-1.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.184.582-.496.646-.87l.213-1.281Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Settings</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmPublish() {
        Swal.fire({
            title: 'Publish Survey?',
            html: '<div class="p-2">Are you sure you want to publish this survey? Once published, it will be available for responses.</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, publish it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.find('{{ $_instance->getId() }}').publishSurvey();
            }
        });
    }
    
    function confirmUnpublish() {
        Swal.fire({
            title: 'Unpublish Survey?',
            html: '<div class="p-2">Are you sure you want to unpublish this survey? It will no longer be available for responses.</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, unpublish it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#eab308',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.find('{{ $_instance->getId() }}').unpublishSurvey();
            }
        });
    }
    
    function confirmReset() {
        Swal.fire({
            title: 'Reset Survey?',
            html: '<div class="p-2">Are you sure you want to reset this survey? This will delete all pages and questions associated with it.</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reset it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.find('{{ $_instance->getId() }}').deleteAll();
            }
        });
    }
</script>
@endpush