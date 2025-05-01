@extends('components.layouts.app')
@section('content')
    <livewire:surveys.form-builder.form-builder :survey="$survey"/>
@endsection