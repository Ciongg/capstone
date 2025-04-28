@extends('components.layouts.app')
@section('content')
    <livewire:surveys.form-builder :survey="$survey"/>
@endsection