{{-- filepath: resources\views\livewire\feed\index.blade.php --}}
<div class="max-w-6xl mx-auto py-8">
    {{-- Top Bar --}}
    <div class="flex justify-between items-center mb-8">
        {{-- Search Bar --}}
        <div class="flex-1 max-w-md">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Search surveys..."
                class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-400 focus:outline-none"
            />
        </div>
        {{-- User Points --}}
        <div class="flex items-center ml-6">
            <svg class="w-8 h-8 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l2.39 7.19H22l-6.19 4.5L17.61 22 12 17.77 6.39 22l1.8-8.31L2 9.19h7.61z"/>
            </svg>
            <span class="text-2xl font-bold text-gray-800">{{ $userPoints }}</span>
        </div>
    </div>

    {{-- Surveys Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        @forelse($surveys as $survey)
            <div class="relative bg-white shadow rounded-xl p-4 flex flex-col items-center h-[500px]">
                {{-- Top bar inside card: profile + name --}}
                <div class="flex items-center w-full mb-2">
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-xl font-bold mr-3">
                        {{-- Placeholder profile image --}}
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M16 20c0-2.21-3.58-4-8-4s-8 1.79-8 4" />
                        </svg>
                    </div>
                    <span class="text-base font-semibold text-gray-800 truncate">{{ $survey->user->name ?? 'User' }}</span>
                </div>
                {{-- Survey Points (top right) --}}
                <div class="absolute top-4 right-4 flex items-center bg-blue-100 px-3 py-1 rounded-full z-10">
                    <svg class="w-5 h-5 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l2.39 7.19H22l-6.19 4.5L17.61 22 12 17.77 6.39 22l1.8-8.31L2 9.19h7.61z"/>
                    </svg>
                    <span class="font-semibold text-blue-700">{{ $survey->points_allocated ?? 0 }}</span>
                </div>
                {{-- Survey Title overlayed above image --}}
                <div class="w-full relative mb-2">
                    <div class="absolute top-2 left-1/2 transform -translate-x-1/2 z-10 bg-white/80 px-3 py-1 rounded text-center text-lg font-semibold">
                        {{ $survey->title }}
                    </div>
                </div>
                {{-- Placeholder Image (taller) --}}
                <div class="w-full flex-1 flex items-center justify-center mb-4 relative">
                    <img src="https://placehold.co/300x260?text=Survey+Image" alt="Survey Image" class="rounded-lg object-cover w-full h-64" />
                    {{-- Read More Button (bottom left of image) --}}
                    <button
                        x-data
                        x-on:click="$dispatch('open-modal', {name : 'view-survey-{{ $survey->id }}'})"
                        class="absolute left-4 bottom-4 px-4 py-1 rounded-full font-bold text-white"
                        style="background-color: #00BBFF;"
                    >
                        Read More
                    </button>
                </div>
                {{-- Description --}}
                <div class="w-full">
                    <div class="text-gray-600 text-sm line-clamp-2">{{ $survey->description }}</div>
                </div>
            </div>

            <x-modal name="view-survey-{{ $survey->id }}" title="Survey Details">
                <livewire:feed.modal.view-survey-modal :survey="$survey" />
            </x-modal>
        @empty
            <div class="col-span-3 text-gray-500 text-center">No published surveys available.</div>
        @endforelse
    </div>
</div>
