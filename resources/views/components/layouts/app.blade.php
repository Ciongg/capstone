<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Formigo' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles <!-- Required Livewire styles -->
    
    <!-- Add Confetti.js for level-up animations -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>
<body >

    <!-- Navigation Bar -->
    <nav class="bg-white p-6 "> 
        <div class="container mx-auto flex justify-between items-center">
            <!-- Left side -->
            <div class="flex items-center space-x-6">
                <a href="/" class="font-bold text-2xl" style="color: #03b8ff;">Formigo</a>
                <div class="border-l border-gray-300 h-8"></div> 

                <div class="flex items-center space-x-5 text-gray-700 text-base">
                    @guest
                        <a href="/" wire:navigate class="hover:text-[#03b8ff] hover:underline">Home</a>
                        <a href="/" wire:navigate class="hover:text-[#03b8ff] hover:underline">About</a>
                        <a href="/rewards"  class="hover:text-[#03b8ff] hover:underline">Rewards</a>
                    @endguest

                    @auth
                    {{-- Show  Redeem links only to regular users and researchers (not admins) --}}
                        @if(!Auth::user()->isSuperAdmin())
                            <a href="/feed" wire:navigate class="hover:text-[#03b8ff] hover:underline">Feed</a>
                            <a href="/rewards"  class="hover:text-[#03b8ff] hover:underline">Redeem</a>
                        @endif

                        {{-- Show only to Researchers --}}
                        @if(Auth::user()->isResearcher())
                            <button 
                                x-data
                                x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                class="hover:text-[#03b8ff] hover:underline"
                            >Create Survey</button>
                            <a href="/admin/reward-redemptions" wire:navigate class="hover:text-[#03b8ff] hover:underline">Manage Rewards</a>
                            <a href="/admin/users" wire:navigate class="hover:text-[#03b8ff] hover:underline">Manage Users</a>
                            {{-- <a href="/my-surveys" wire:navigate class="hover:text-[#03b8ff] hover:underline">My Surveys</a> --}}
                        @endif

                        @if(Auth::user()->isInstitutionAdmin())
                            {{-- Institution Admin specific links --}}
                            @if(Auth::user()->hasValidInstitution())
                                {{-- Normal enabled links when institution is valid --}}
                                <button 
                                    x-data
                                    x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                    class="hover:text-[#03b8ff] hover:underline"
                                >Create Institution Survey</button>
                                <a href="/institution/analytics" wire:navigate class="hover:text-[#03b8ff] hover:underline">Analytics</a>
                                <a href="/institution/users" wire:navigate class="hover:text-[#03b8ff] hover:underline">Users</a>
                            @else
                                {{-- Disabled links when institution is invalid --}}
                                <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Create Institution Survey</span>
                                <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Analytics</span>
                                <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Users</span>
                            @endif
                        @endif

                        @if(Auth::user()->isSuperAdmin())
                            {{-- Super Admin specific links --}}
                            <a href="/admin/surveys" wire:navigate class="hover:text-[#03b8ff] hover:underline">Manage Surveys</a>
                            <a href="/admin/reward-redemptions" wire:navigate class="hover:text-[#03b8ff] hover:underline">Manage Rewards</a>
                            <a href="/admin/users" wire:navigate class="hover:text-[#03b8ff] hover:underline">Manage Users</a>
                            <a href="/admin/reports" wire:navigate class="hover:text-[#03b8ff] hover:underline">Manage Reports</a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-5 text-base">
                @auth
                    <a href="/profile" wire:navigate class="flex items-center space-x-2 hover:underline text-gray-700 hover:text-[#03b8ff]">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover border border-gray-200">
                        <span class="font-semibold">{{ Auth::user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 underline font-semibold">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="text-gray-700 hover:text-[#03b8ff] hover:underline">Login</a>
                    <a href="{{ route('register') }}" wire:navigate class="text-gray-700 hover:text-[#03b8ff] hover:underline">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Survey Creation Modal -->
    <x-modal name="select-survey-type" title="Create Survey">
        <livewire:surveys.form-builder.modal.survey-type-modal />
    </x-modal>

    <!-- Script to handle data passing between modals -->
    <script>
        document.addEventListener('alpine:init', () => {
            // No longer need the store since everything is in one modal now
        });
    </script>

    <!-- Main Content -->
    <main class=" mx-auto ">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
    @livewireScripts <!-- Required Livewire scripts -->
</body>
</html>
