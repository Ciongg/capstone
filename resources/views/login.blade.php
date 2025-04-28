@extends('components.layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 p-6">
        <!-- Header -->
        <header class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-blue-600">Formigo</h1>
            <p class="text-gray-500 mt-2">Welcome Back! Please login to continue.</p>
            
        </header>

        <!-- Form Card -->
        <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
            <form action="{{ route('login') }}" method="POST">
            

                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <x-form-error name="email"/>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input 
                        type="text" 
                        name="password" 
                        id="password" 
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <x-form-error name="password"/>
                </div>

                <!-- Button -->
                <div class="flex justify-center">
                    <button 
                        type="submit" 
                        class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition"
                    >
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
