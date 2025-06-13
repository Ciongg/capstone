@extends('components.layouts.app')

@section('content')
    <livewire:surveys.form-responses.own-form-response :surveyId="$survey->id" :responseId="$response->id" />
@endsection
