@props([
    'title' => 'Featured Sponsors',
    'merchants' => null,
])

@php
    /** @var \Illuminate\Support\Collection $items */
    $items = $merchants ?: \App\Models\Merchant::select('id','name','logo_path')->orderBy('name')->get();
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
            /* Simple marquee-like infinite scroll (left-to-right) */
            @keyframes sponsors-marquee-rtl {
                from { transform: translateX(-50%); }
                to   { transform: translateX(0%); }
            }
            .sponsors-banner { overflow: hidden; }
            .sponsors-track { display: flex; align-items: center; white-space: nowrap; }
            .sponsors-scroller {
                display: flex;
                align-items: center;
                gap: 2rem;
                animation: sponsors-marquee-rtl 30s linear infinite;
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
                    @foreach($items as $m)
                        <li class="flex items-center justify-center">
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
                    {{-- Duplicate for seamless looping --}}
                    @foreach($items as $m)
                        <li class="flex items-center justify-center">
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
                </ul>
            </div>
        </div>
    </div>

    <!-- Fullscreen overlay (same behavior as merchant index) -->
    <div
        x-show="fullscreenImageSrc"
        x-cloak
        class="fixed inset-0 z-[100] bg-black/80 flex items-center justify-center p-4"
        @click="fullscreenImageSrc = null"
        @keydown.escape.window="fullscreenImageSrc = null"
    >
        <img :src="fullscreenImageSrc" alt="Merchant Logo" class="max-w-full max-h-full rounded-lg shadow-2xl" />
    </div>
</section>
