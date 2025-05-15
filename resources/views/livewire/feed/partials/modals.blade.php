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

