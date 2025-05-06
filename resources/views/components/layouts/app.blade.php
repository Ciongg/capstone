<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Survey Builder' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles <!-- Required Livewire styles -->
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-white p-4 "> 
        <div class="container mx-auto flex justify-between items-center">
            <!-- Left side -->
            <div class="flex items-center space-x-4">
                <a href="/" class="font-bold text-xl" style="color: #00BBFF;">Formigo</a>
                <div class="border-l border-gray-300 h-6"></div> 

                <div class="flex items-center space-x-4 text-gray-700">
                    @auth
                        <a href="/feed" wire:navigate class="hover:text-[#00BBFF] hover:underline">Feed</a>
                        <a href="/surveys/create" class="hover:text-[#00BBFF] hover:underline">Create Survey</a>
                        <a href="/my-surveys" wire:navigate class="hover:text-[#00BBFF] hover:underline">My Surveys</a>
                        <a href="/rewards" wire:navigate class="hover:text-[#00BBFF] hover:underline">Rewards</a>
                    @else
                        <a href="/" wire:navigate class="hover:text-[#00BBFF] hover:underline">Home</a>
                        <a href="/" wire:navigate class="hover:text-[#00BBFF] hover:underline">About</a>
                        <a href="/rewards" wire:navigate class="hover:text-[#00BBFF] hover:underline">Rewards</a>
                    @endguest
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                @auth
                    <a href="/profile" wire:navigate class="flex items-center space-x-2 hover:underline text-gray-700 hover:text-[#00BBFF]">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                        <span class="font-semibold">{{ Auth::user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 underline font-semibold text-sm">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="text-gray-700 hover:text-[#00BBFF] hover:underline">Login</a>
                    <a href="/" wire:navigate class="text-gray-700 hover:text-[#00BBFF] hover:underline">Register</a>
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
