@props(['questionId'])

<div 
    {{ $attributes->merge(['class' => 'inline-block relative ml-2']) }} 
    x-data="{ showLanguages: false, isTranslating: false }"
>
    <!-- Toggle Button -->
    <button 
        type="button"
        class="flex items-center gap-1 p-1 bg-blue-50 rounded-full hover:bg-blue-100 transition-colors"
        title="Translate question"
        @click="showLanguages = !showLanguages"
        :disabled="isTranslating"
    >
        <!-- Loading spinner -->
        <div x-show="isTranslating" class="flex items-center">
            <svg class="animate-spin w-4 h-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <!-- Default static icon -->
        <svg 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke-width="1.5" 
            stroke="currentColor" 
            class="w-4 h-4 text-blue-600"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="showLanguages" 
        @click.away="showLanguages = false"
        x-transition
        class="absolute right-0 mt-2 w-40 py-1 bg-white rounded-md shadow-lg z-50"
        style="display: none;"
    >
        @foreach([
            'en' => 'English', 'tl' => 'Filipino', 'zh-CN' => 'Simplified Chinese', 
            'zh-TW' => 'Traditional Chinese', 'ar' => 'Arabic', 'ja' => 'Japanese', 
            'vi' => 'Vietnamese', 'th' => 'Thai', 'ms' => 'Malay'
        ] as $code => $name)
            <a href="#" 
               class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-100" 
               @click.prevent="
                   isTranslating = true;
                   showLanguages = false;
                   $wire.translateQuestion({{ $questionId }}, '{{ $code }}')
                       .then(() => { isTranslating = false; })
                       .catch(() => { isTranslating = false; });
               ">
                {{ $name }}
            </a>
        @endforeach
    </div>
</div>

