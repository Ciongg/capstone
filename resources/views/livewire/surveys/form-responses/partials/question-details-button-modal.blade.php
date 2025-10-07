@php $modalName = 'view-all-responses-modal' . $question->id; @endphp

{{-- Question title and More Details button container --}}
<div class="flex justify-between items-start gap-4 mb-2">
    {{-- Question title --}}
    <div class="font-semibold text-base sm:text-lg break-words flex-1 text-justify">
        {{ $questionCounter }}. {{ $question->question_text }}
    </div>
    
    {{-- "More Details" button --}}
    <button
        x-data
        x-on:click="$dispatch('open-modal', {name : '{{ $modalName }}'})"
        class="text-blue-600 underline font-semibold hover:text-blue-800 text-xs sm:text-sm bg-white px-2 py-1 rounded flex-shrink-0"
        type="button"
    >
        More Details
    </button>
</div>

{{-- Modal for all responses to this question --}}
<div wire:ignore.self>
    <x-modal name="{{ $modalName }}" title="All Responses">
        <livewire:surveys.form-responses.modal.view-all-responses-modal :question="$question" :key="'modal-'.$question->id" />
    </x-modal>
</div>
