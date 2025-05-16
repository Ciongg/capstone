<div {{ $attributes->merge(['class' => 'flex flex-col items-center']) }} x-data="{ showLanguages: false }">
    <button 
        type="button"
        class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 transition-colors" 
        title="Translate to preferred language"
        @click="showLanguages = !showLanguages"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
        </svg>
    </button>
    <span class="text-xs text-gray-500 mt-1">Translate</span>
    
    {{-- Language Selection Dropdown --}}
    <div 
        x-show="showLanguages" 
        @click.away="showLanguages = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute mt-16 w-48 py-2 bg-white rounded-md shadow-lg z-50"
        style="display: none;"
    >
        <div class="py-1">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="showLanguages = false">English</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="showLanguages = false">Spanish</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="showLanguages = false">French</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="showLanguages = false">German</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="showLanguages = false">Chinese</a>
        </div>
    </div>
</div>
