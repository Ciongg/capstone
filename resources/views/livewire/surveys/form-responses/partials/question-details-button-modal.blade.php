@php $modalName = 'view-all-responses-modal' . $question->id; @endphp

{{-- "More Details" button --}}
<div class="absolute top-4 right-4">
    <button
        x-data
        x-on:click="$dispatch('open-modal', {name : '{{ $modalName }}'})"
        class="text-blue-600 underline font-semibold hover:text-blue-800 text-sm"
        type="button"
    >
        More Details
    </button>
</div>

{{-- Modal for all responses to this question --}}
<x-modal name="{{ $modalName }}" title="All Responses">
    <livewire:surveys.form-responses.modal.view-all-responses-modal :question="$question" />
</x-modal>
