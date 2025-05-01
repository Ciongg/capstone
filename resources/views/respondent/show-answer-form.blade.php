@extends('components.layouts.app')

@section('content')
    <livewire:surveys.answer-survey.answer-survey :survey="$survey" />
@endsection