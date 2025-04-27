@extends('components.layouts.app')

@section('content')
    <div class="text-center">
        <h1 class="text-4xl font-semibold mb-6">Welcome to the Survey Platform!</h1>

        <p class="text-lg mb-8">Create and manage your surveys with ease.</p>

        <!-- Button to navigate to create survey page -->
        <a href="{{route('surveys.create')}}" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition duration-200">
            Create a Survey
        </a>
    </div>
@endsection
