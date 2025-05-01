<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Survey Builder' }}</title>



    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles <!-- Required Livewire styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-blue-500 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Left side -->
            <div class="flex items-center space-x-6">
                <a href="/" class="font-bold text-xl">Formigo</a>

                @auth
                    <a href="/feed" class="hover:underline">Feed</a>
                    <a href="/surveys/create" class="hover:underline">Create Survey</a>
                    <a href="/my-surveys" class="hover:underline">My Surveys</a>
                    <a href="/rewards" class="hover:underline">Rewards</a>
                @endauth
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:underline">Login</a>
                    <a href="/" class="hover:underline">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        @yield('content')
    </main>

    @livewireScripts <!-- Required Livewire scripts -->
</body>
</html>
