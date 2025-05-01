@extends('components.layouts.app')
@section('content')
    <livewire:surveys.form-responses.individual-responses :survey-id="$surveyId" />
@endsection