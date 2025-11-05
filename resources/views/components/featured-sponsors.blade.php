@props([
    'title' => 'Featured Sponsors',
    'merchants' => null,
])

@php
    /** @var \Illuminate\Support\Collection $items */
    $items = $merchants ?: \App\Models\Merchant::select('id','name','logo_path')->orderBy('name')->get();
    // Duplicate items multiple times for smoother infinite scroll
    $duplicateCount = max(3, ceil(12 / max($items->count(), 1)));
@endphp

<section {{ $attributes->merge(['class' => 'w-full py-6']) }} x-data="{ fullscreenImageSrc: null }">
    <div class="mx-auto max-w-7xl">
        {{-- @if($title)
            <div class="text-center">
                <h2 class="text-sm sm:text-base font-semibold text-gray-700 uppercase tracking-wider">
                    {{ $title }}
                </h2>
            </div>
        @endif --}}

        <style>
            /* Seamless infinite scroll from left to right */
            @keyframes sponsors-marquee-ltr {
                0% { transform: translateX(-50%); }
                100% { transform: translateX(0%); }
            }
            .sponsors-banner { 
                overflow: hidden;
                position: relative;
            }
            .sponsors-track { 
                display: flex; 
                align-items: center;
            }
            .sponsors-scroller {
                display: flex;
                align-items: center;
                gap: 2rem;
                animation: sponsors-marquee-ltr 40s linear infinite;
                will-change: transform;
            }
            .sponsors-banner:hover .sponsors-scroller {
                animation-play-state: paused;
            }
            @media (prefers-reduced-motion: reduce) {
                .sponsors-scroller { animation: none; }
            }
        </style>

        <div class="sponsors-banner mt-4">
            <div class="sponsors-track">
                <ul class="sponsors-scroller">
                    @for($i = 0; $i < $duplicateCount; $i++)
                        @foreach($items as $m)
                            <li class="flex items-center justify-center flex-shrink-0">
                                <div class="w-40 md:w-48 h-16 md:h-20 flex items-center justify-center">
                                    @if($m->logo_path)
                                        @php $logo = asset('storage/' . $m->logo_path); @endphp
                                        <button type="button" @click="fullscreenImageSrc = '{{ $logo }}'" class="focus:outline-none cursor-pointer">
                                            <img
                                                src="{{ $logo }}"
                                                alt="{{ $m->name }} logo"
                                                title="{{ $m->name }}"
                                                class="h-12 md:h-16 w-auto object-contain select-none transition-transform duration-200 hover:scale-110"
                                                draggable="false"
                                            />
                                        </button>
                                    @else
                                        <span
                                            class="text-gray-700 text-base md:text-lg font-medium leading-none truncate transition-transform duration-200 hover:scale-110"
                                            title="{{ $m->name }}"
                                        >
                                            {{ $m->name }}
                                        </span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    @endfor
                </ul>
            </div>
        </div>
    </div>

    <!-- Fullscreen overlay (same behavior as feed index) -->
    <div
        x-show="fullscreenImageSrc"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] bg-black/75 flex items-center justify-center p-4 cursor-pointer"
        style="display: none;"
        @click="fullscreenImageSrc = null"
        @keydown.escape.window="fullscreenImageSrc = null"
    >
        <img :src="fullscreenImageSrc" alt="Merchant Logo" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" />
        <button @click="fullscreenImageSrc = null" 
                class="cursor-pointer absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-white text-4xl sm:text-3xl font-bold leading-none rounded-full hover:bg-black hover:bg-opacity-25 focus:outline-none">
            &times;
        </button>
    </div>
</section>
