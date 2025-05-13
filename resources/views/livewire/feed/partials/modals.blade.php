{{-- Survey Detail Modal --}}
<x-modal name="surveyDetailModal" title="Survey Details" @close="$wire.closeSurveyModal()">
    {{-- Content INSIDE the modal is conditional based on Livewire's $modalSurveyId --}}
    @if($modalSurveyId)
        <livewire:feed.modal.view-survey-modal :survey="\App\Models\Survey::find($modalSurveyId)" :wire:key="'modal-view-' . $modalSurveyId . '-' . now()->timestamp" />
    @else
        {{-- Placeholder for when modalSurveyId is null or content is loading --}}
        <div class="p-6 animate-pulse">
            <div class="h-6 bg-gray-300 rounded w-3/4 mb-4"></div>
            <div class="h-32 bg-gray-300 rounded mb-4"></div>
            <div class="space-y-3">
                <div class="h-4 bg-gray-300 rounded"></div>
                <div class="h-4 bg-gray-300 rounded w-5/6"></div>
                <div class="h-4 bg-gray-300 rounded w-4/6"></div>
            </div>
        </div>
    @endif
</x-modal>

{{-- Fullscreen Image Overlay --}}
<div x-show="fullscreenImageSrc" @click.self="fullscreenImageSrc = null" class="fixed inset-0 bg-black bg-opacity-75 z-[100] flex items-center justify-center p-4" style="display: none;">
    <div class="relative">
        <button @click="fullscreenImageSrc = null" class="absolute -top-3 -right-3 m-2 text-white bg-black bg-opacity-50 rounded-full p-1.5 hover:bg-opacity-75 z-[101]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img :src="fullscreenImageSrc" alt="Fullscreen image" class="max-w-full max-h-[90vh] rounded-lg shadow-md object-contain" />
    </div>
</div>