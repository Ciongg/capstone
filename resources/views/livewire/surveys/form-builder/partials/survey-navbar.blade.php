<div class="bg-white shadow flex items-center justify-between px-6 py-3 mb-4">
    {{-- Left Side: Title --}}
    <div class="flex items-center space-x-4">
        <input
            type="text"
            wire:model.defer="surveyTitle"
            wire:blur="updateSurveyTitle"
            class="text-xl font-bold border-b border-gray-300 focus:border-blue-500 outline-none bg-transparent py-1"
            style="min-width: 200px;"
            @if($survey->is_locked) readonly @endif
        />
        <span class="text-gray-500 italic text-sm">Survey Title</span>
    </div>

    {{-- Right Side: Buttons & Status --}}
    <div x-data="{ open: false }" class="relative">
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

            {{-- Preview Button - still accessible when locked --}}
            <a href="{{ route('surveys.preview', $survey->id) }}" wire:navigate
               class="inline-flex items-center h-9 px-4 py-1.5 bg-purple-500 text-white text-sm font-medium rounded hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
            >
               <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639l4.458-4.458a1.012 1.012 0 0 1 1.414 0l4.458 4.458a1.012 1.012 0 0 1 0 .639l-4.458 4.458a1.012 1.012 0 0 1-1.414 0l-4.458-4.458Z" />
                 <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
               </svg>
               Preview
            </a>

            {{-- View Responses Button - still accessible when locked --}}
            @if($hasResponses)
                <a href="{{ route('surveys.responses', $survey->id) }}"
                   class="inline-flex items-center h-9 px-4 py-1.5 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    View Responses
                </a>
            @endif

            {{-- Publish/Unpublish Buttons - disabled when locked --}}
            @if($survey->status === 'published' || $survey->status === 'ongoing')
                <button
                    wire:click="unpublishSurvey"
                    class="inline-flex items-center h-9 px-4 py-1.5 bg-yellow-500 text-white text-sm font-medium rounded hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    @if($survey->is_locked) disabled @endif
                >
                    Unpublish
                </button>
            @else
                <button
                    wire:click="publishSurvey"
                    class="inline-flex items-center h-9 px-4 py-1.5 bg-green-500 text-white text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    @if($survey->is_locked) disabled @endif
                >
                    Publish
                </button>
            @endif

            {{-- Delete All Button - disabled when locked --}}
            <button
                wire:click="deleteAll"
                class="inline-flex items-center h-9 px-4 py-1.5 bg-red-500 text-white text-sm font-medium rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                title="Delete All Questions and Pages"
                @if($survey->is_locked) disabled @endif
            >
                Delete All
            </button>

            {{-- Survey Settings Button (Icon) - settings modal might be viewable but not editable when locked --}}
            <button
                x-data
                x-on:click="$dispatch('open-modal', {name : 'survey-settings-modal-{{ $survey->id }}'})"
                class="flex items-center justify-center h-9 w-9 px-2 py-1.5 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                title="Survey Settings"
            >
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.646.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 1.255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.333.184-.583.496-.646.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.646-.87-.074-.04-.147-.083-.22-.127-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.759 6.759 0 0 1 0-1.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.184.582-.496.646-.87l.213-1.281Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
        </div>

        {{-- Hamburger Menu Button (visible below large screens) --}}
        <button @click="open = !open" class="lg:hidden p-2 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        {{-- Dropdown Menu (visible below large screens when open) --}}
        <div
            x-show="open"
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-2 w-56 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50 lg:hidden"
            style="display: none;" {{-- Prevents flash of content before Alpine initializes --}}
        >
            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                {{-- Display Status --}}
                <span class="block px-4 py-2 text-sm text-gray-500">Status: {{ ucfirst($survey->status) }}</span>

                {{-- Preview Button --}}
                <a href="{{ route('surveys.preview', $survey->id) }}" wire:navigate
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                   <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639l4.458-4.458a1.012 1.012 0 0 1 1.414 0l4.458 4.458a1.012 1.012 0 0 1 0 .639l-4.458 4.458a1.012 1.012 0 0 1-1.414 0l-4.458-4.458Z" />
                       <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                   </svg>
                   Preview
                </a>

                {{-- View Responses Button --}}
                @if($hasResponses)
                    <a href="{{ route('surveys.responses', $survey->id) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                        View Responses
                    </a>
                @endif

                {{-- Publish/Unpublish Buttons - disabled when locked --}}
                @if($survey->status === 'published' || $survey->status === 'ongoing')
                    <button
                        wire:click="unpublishSurvey" @click="open = false"
                        class="w-full text-left block px-4 py-2 text-sm text-yellow-700 hover:bg-gray-100 hover:text-yellow-900" role="menuitem"
                        @if($survey->is_locked) disabled @endif
                    >
                        Unpublish
                    </button>
                @else
                    <button
                        wire:click="publishSurvey" @click="open = false"
                        class="w-full text-left block px-4 py-2 text-sm text-green-700 hover:bg-gray-100 hover:text-green-900" role="menuitem"
                        @if($survey->is_locked) disabled @endif
                    >
                        Publish
                    </button>
                @endif

                {{-- Delete All Button - disabled when locked --}}
                <button
                    wire:click="deleteAll" @click="open = false"
                    class="w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 hover:text-red-900" role="menuitem"
                    title="Delete All Questions and Pages"
                    @if($survey->is_locked) disabled @endif
                >
                    Delete All
                </button>

                {{-- Survey Settings Button - MOVED TO END --}}
                 <button
                    x-data
                    x-on:click="$dispatch('open-modal', {name : 'survey-settings-modal-{{ $survey->id }}'}); open = false;"
                    class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    role="menuitem"
                    title="Survey Settings"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.646.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 1.255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.333.184-.583.496-.646.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.646-.87-.074-.04-.147-.083-.22-.127-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.759 6.759 0 0 1 0-1.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.184.582-.496.646-.87l.213-1.281Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Settings
                </button>
            </div>
        </div>
    </div>
</div>
