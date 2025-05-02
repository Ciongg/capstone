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
    <nav class="p-4 text-white" style="background-color: #00BBFF;">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Left side -->
            <div class="flex items-center space-x-6">
                <a href="/" class="font-bold text-xl">Formigo</a>

                @auth
                    <a href="/feed" wire:navigate class="hover:underline">Feed</a>
                    <a href="/surveys/create" wire:navigate class="hover:underline">Create Survey</a>
                    <a href="/my-surveys" wire:navigate class="hover:underline">My Surveys</a>
                    <a href="/rewards" wire:navigate class="hover:underline">Rewards</a>
                @endauth
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Profile Link -->
                    <a href="/profile" class="flex items-center space-x-2 hover:underline">
                        <span class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-lg font-bold">
                            <!-- Placeholder profile image -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M16 20c0-2.21-3.58-4-8-4s-8 1.79-8 4" />
                            </svg>
                        </span>
                        <span class="font-semibold text-white">{{ Auth::user()->name }}</span>
                    </a>
                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="hover:underline">Login</a>
                    <a href="/" class="hover:underline">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
    @livewireScripts <!-- Required Livewire scripts -->
</body>
</html>
