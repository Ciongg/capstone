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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Scroll to top on page load/refresh --}}
    <script>
        if (history.scrollRestoration) {
            history.scrollRestoration = 'manual';
        } else {
            window.onbeforeunload = function () {
                window.scrollTo(0, 0);
            }
        }
        window.scrollTo(0,0); // Fallback for some browsers
    </script>
</head>
<body >

    <!-- Navigation Bar -->
    <nav class="bg-white p-6 "> 
        <div class="container mx-auto flex justify-between items-center">
            <!-- Left side -->
            <div class="flex items-center space-x-6">
                <a href="/" class="font-bold text-2xl" style="color: #03b8ff;">Formigo</a>
                <div class="border-l border-gray-300 h-8"></div> 

                <div class="flex items-center space-x-5 text-base">
                    @guest
                        <a href="/" wire:navigate class="{{ request()->is('/') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Home</a>
                       
                        <a href="/about" wire:navigate class="{{ request()->is('about') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">About</a>
                        <a href="/rewards-info" wire:navigate class="{{ request()->is('rewards-info') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Rewards</a>
                    @endguest
                        
                    @auth
                        @if(Auth::user()->isResearcher())
                            <a href="/feed" wire:navigate class="{{ request()->routeIs('feed.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Feed</a>
                            <button 
                                x-data
                                x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                class="{{ request()->is('surveys/create*') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline"
                            >Create Survey</button>
                            <a href="/rewards"  class="{{ request()->routeIs('rewards.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Redeem</a>
                            <a href="/vouchers" wire:navigate class="{{ request()->routeIs('vouchers.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Vouchers</a>
                        @elseif(Auth::user()->isInstitutionAdmin())
                            {{-- Common links for Institution Admin --}}
                            <a href="/feed" wire:navigate class="{{ request()->routeIs('feed.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Feed</a>
                            <a href="/rewards" class="{{ request()->routeIs('rewards.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Redeem</a>
                            <a href="/vouchers" wire:navigate class="{{ request()->routeIs('vouchers.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Vouchers</a>
                            
                            @if(Auth::user()->hasValidInstitution())
                                {{-- Normal enabled links when institution is valid --}}
                                <button 
                                x-data
                                x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                class="{{ request()->is('surveys/create*') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline"
                                >Create Survey</button>
                                
                                {{-- Institution dropdown menu --}}
                                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                    <button 
                                        @click="open = !open" 
                                        class="{{ request()->is('institution/*') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline flex items-center"
                                    >
                                        Institution
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <div 
                                        x-show="open" 
                                        x-transition:enter="transition ease-out duration-100" 
                                        x-transition:enter-start="transform opacity-0 scale-95" 
                                        x-transition:enter-end="transform opacity-100 scale-100" 
                                        class="absolute z-50 mt-2 bg-white border border-gray-200 rounded-md shadow-lg py-1 w-48"
                                        style="display: none;"
                                    >
                                        <a href="/institution/analytics" class="block px-4 py-2 text-sm {{ request()->is('institution/analytics') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:bg-gray-100">Analytics</a>
                                        <a href="/institution/users" wire:navigate class="block px-4 py-2 text-sm {{ request()->is('institution/users') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:bg-gray-100">Users</a>
                                    </div>
                                </div>
                            @else
                                {{-- Disabled links when institution is invalid --}}
                                <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Create Survey</span>
                                <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Institution</span>
                            @endif
                        @elseif(Auth::user()->isSuperAdmin())
                            {{-- Super Admin specific links --}}
                            <a href="/feed" wire:navigate class="{{ request()->routeIs('feed.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Feed</a>
                            <a href="/rewards" class="{{ request()->routeIs('rewards.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Redeem</a>
                            <a href="/vouchers" wire:navigate class="{{ request()->routeIs('vouchers.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Vouchers</a>
                            
                            {{-- Manage dropdown menu --}}
                            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                <button 
                                    @click="open = !open" 
                                    class="{{ request()->is('admin/*') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline flex items-center"
                                >
                                    Manage
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-100" 
                                    x-transition:enter-start="transform opacity-0 scale-95" 
                                    x-transition:enter-end="transform opacity-100 scale-100" 
                                    class="absolute z-50 mt-2 bg-white border border-gray-200 rounded-md shadow-lg py-1 w-48"
                                    style="display: none;"
                                >
                                    <a href="/admin/surveys" wire:navigate class="block px-4 py-2 text-sm {{ request()->is('admin/surveys*') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:bg-gray-100">Manage Surveys</a>
                                    <a href="/admin/reward-redemptions" wire:navigate class="block px-4 py-2 text-sm {{ request()->routeIs('reward-redemptions.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:bg-gray-100">Manage Rewards</a>
                                    <a href="/admin/users" wire:navigate class="block px-4 py-2 text-sm {{ request()->is('admin/users*') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:bg-gray-100">Manage Users</a>
                                    <a href="/admin/reports" wire:navigate class="block px-4 py-2 text-sm {{ request()->is('admin/reports') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:bg-gray-100">Manage Support Request</a>
                                </div>
                            </div>
                        @else
                            {{-- Default for other authenticated users (e.g., 'respondent') --}}
                            <a href="/feed" wire:navigate class="{{ request()->routeIs('feed.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Feed</a>
                            <a href="/rewards"  class="{{ request()->routeIs('rewards.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Redeem</a>
                            <a href="/vouchers" wire:navigate class="{{ request()->routeIs('vouchers.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Vouchers</a>
                        @endif
                    @endauth
                </div>
            </div>
            
            <!-- Right side -->
            <div class="flex items-center space-x-5 text-base">
                @auth
                    {{-- Inbox Icon Button --}}
                    <button class="text-gray-700 hover:text-[#03b8ff]" title="Inbox">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                        </svg>
                    </button>

                    <a href="/profile" class="flex items-center space-x-2 {{ request()->routeIs('profile.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">
                        <span class="font-semibold mr-5">{{ Auth::user()->name }}</span>
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-400">
                    </a>
                    {{-- Logout button removed from here --}}
                @else
                    <a href="{{ route('register') }}" wire:navigate class="{{ request()->routeIs('register') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Register</a>
                    <a href="{{ route('login') }}" wire:navigate class="inline-block bg-[#03b8ff] hover:bg-[#02a0e0] text-white font-bold px-6 py-2 rounded-lg">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Survey Creation Modal -->
    <x-modal name="select-survey-type" title="Create Survey">
        <livewire:surveys.form-builder.modal.survey-type-modal />
    </x-modal>

    {{-- Support Request Modal --}}
    <x-modal name="support-request-modal" title="Support Request">
        <livewire:support-requests.create-support-request-modal />
    </x-modal>

    <!-- Script to handle data passing between modals -->
    <script>
        document.addEventListener('alpine:init', () => {
            // No longer need the store since everything is in one modal now
        });

        // Ensure scroll to top on initial load for all scenarios
        window.addEventListener('load', () => {
            window.scrollTo(0, 0);
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
