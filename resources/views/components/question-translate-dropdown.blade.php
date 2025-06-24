@props(['questionId', 'translatingQuestions' => []])
<div {{ $attributes->merge(['class' => 'inline-block relative ml-2']) }} x-data="{ showLanguages: false }">
    <button 
        type="button"
        class="p-1 bg-blue-50 rounded-full hover:bg-blue-100 transition-colors"
        title="Translate question"
        @click="showLanguages = !showLanguages"
        @if(isset($translatingQuestions[$questionId]) && $translatingQuestions[$questionId])
            disabled
        @endif
    >
        @if(isset($translatingQuestions[$questionId]) && $translatingQuestions[$questionId])
            {{-- Loading spinner replaces icon when translating --}}
            <svg class="animate-spin w-4 h-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
            </svg>
        @endif
    </button>
    
    <!-- Language Selection Dropdown -->
    <div 
        x-show="showLanguages" 
        @click.away="showLanguages = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-40 py-1 bg-white rounded-md shadow-lg z-50"
        style="display: none;">
        
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'en'); showLanguages = false;">
           English
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'tl'); showLanguages = false;">
           Filipino
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'zh-CN'); showLanguages = false;">
           Simplified Chinese
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'zh-TW'); showLanguages = false;">
           Traditional Chinese
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'ar'); showLanguages = false;">
           Arabic
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'ja'); showLanguages = false;">
           Japanese
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'vi'); showLanguages = false;">
           Vietnamese
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'th'); showLanguages = false;">
           Thai
        </a>
        <a href="#" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
           @click.prevent="$wire.translateQuestion({{ $questionId }}, 'ms'); showLanguages = false;">
           Malay
        </a>
    </div>
</div>
