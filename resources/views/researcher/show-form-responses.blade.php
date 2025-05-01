@extends('components.layouts.app')
@section('content')
    <livewire:surveys.form-responses.form-responses :survey-id="$survey->id" />
@endsection