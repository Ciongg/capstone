@extends('components.layouts.app')
@section('content')
    <livewire:profile.view-profile :user="$user" />
@endsection