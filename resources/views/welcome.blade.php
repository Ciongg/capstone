@extends('components.layouts.app')

@section('content')
    <div class="text-center">
        <h1 class="text-4xl font-semibold mb-6">Welcome to Formigo</h1>

        <!-- Button to navigate to create survey page -->
        <a href="{{route('login')}}" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition duration-200">
            Login
        </a>
    </div>
@endsection
