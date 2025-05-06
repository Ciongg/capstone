@extends('components.layouts.app')

@section('content')
    {{-- Pass the isPreview variable to the Livewire component --}}
    <livewire:surveys.answer-survey.answer-survey :survey="$survey" :isPreview="$isPreview ?? false" />
@endsection