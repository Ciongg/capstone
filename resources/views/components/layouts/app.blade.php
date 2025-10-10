<!DOCTYPE html>
<html lang="en">
<head>
        <!-- Meta tags for character set and viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">

    <!-- Ensure HTTPS assets load properly -->
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <!-- Laravel CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO meta tags -->
    <meta name="description" content="Incentivized Research Survey Platform">
    <meta name="keywords" content="Formigo, Survey Platform, Research, Academic Surveys, Incentivized Surveys">
    <meta name="author" content="Formigo Team">

    <!-- Mobile & web app support -->
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Formigo">

    <!-- Open Graph (Facebook, Messenger, Discord, LinkedIn, etc.) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Formigo">
    <meta property="og:description" content="Incentivized Research Survey Platform">
    <meta property="og:image" content="{{ asset('images/landing/formigo.png') }}">

    <!-- Dynamic page title -->
    <title>{{ $title ?? 'Formigo' }}</title>

    <!-- Vite assets (CSS and JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire styles -->
    @livewireStyles

    <!-- External scripts -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Scroll behavior script -->
    <script>
        if (history.scrollRestoration) {
            history.scrollRestoration = 'manual';
        } else {
            window.onbeforeunload = function () {
                window.scrollTo(0, 0);
            }
        }
        window.scrollTo(0,0);
    </script>

    <!-- Inline styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        body.no-scroll {
            overflow: hidden;
            position: fixed;
            width: 100%;
            height: 100%;
        }

        .mobile-menu {
            background-color: #ffffff;
        }
    </style>

</head>
<body>

    <!-- Navigation Bar Section -->
    <nav class="sticky top-0 z-40 transition-all duration-300 shadow-md" 
         :class="scrollY > 50 ? 'bg-white/60 backdrop-blur-sm' : 'bg-white'"
         x-data="{ 
        mobileMenuOpen: false, // Alpine.js state for mobile menu visibility
        scrollY: 0,
        // Function to toggle mobile menu and body scroll lock
        toggleMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
            if (this.mobileMenuOpen) {
                document.body.classList.add('no-scroll'); // Add no-scroll class to body
            } else {
                document.body.classList.remove('no-scroll'); // Remove no-scroll class from body
            }
        }
    }"
         x-init="
            window.addEventListener('scroll', () => {
                scrollY = window.scrollY;
            });
         ">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center gap-4">
                <!-- Left side: Logo/Brand Name -->
                <div class="flex items-center">
                    <a href="/" class="font-bold text-2xl" style="color: #03b8ff;">Formigo</a>
                </div>
                
                <!-- Hamburger Menu Button (visible on mobile only) -->
                <button @click="toggleMenu()" class="lg:hidden text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Desktop Navigation Links (hidden on mobile) -->
                <div class="hidden lg:flex lg:items-center lg:justify-between lg:w-full lg:pl-6">
                    <!-- Desktop Navigation: Left Aligned Links -->
                    <div class="flex items-center space-x-6">
                        <div class="border-l border-gray-300 h-8"></div> <!-- Vertical separator -->
                        <div class="flex items-center space-x-5 text-base">
                            @php
                                // Helper function for dynamic navigation link styling based on current route
                                $navLinkClass = function($condition) {
                                    return $condition ? 'text-[#03b8ff] font-bold' : 'text-gray-700';
                                };
                            @endphp

                            <!-- Desktop Navigation Links: Guest Users -->
                            @guest
                                <a href="/" class="{{ $navLinkClass(request()->is('/')) }} hover:text-[#03b8ff] hover:underline">Home</a>
                                <a href="/about" class="{{ $navLinkClass(request()->is('about')) }} hover:text-[#03b8ff] hover:underline">About</a>
                                <a href="/rewards-info" class="{{ $navLinkClass(request()->is('rewards-info')) }} hover:text-[#03b8ff] hover:underline">Rewards</a>
                            @else
                                {{-- Desktop Navigation Links: Authenticated Users (Common) --}}
                                <a href="/feed" class="{{ $navLinkClass(request()->routeIs('feed.index')) }} hover:text-[#03b8ff] hover:underline">Feed</a>
                                <a href="/rewards" class="{{ $navLinkClass(request()->routeIs('rewards.index')) }} hover:text-[#03b8ff] hover:underline">Redeem</a>
                                <a href="/vouchers" class="{{ $navLinkClass(request()->routeIs('vouchers.index')) }} hover:text-[#03b8ff] hover:underline">Vouchers</a>
                                
                                {{-- Desktop Navigation Links: Role Specific --}}
                                @if(Auth::user()->isResearcher())
                                    {{-- Researcher: Create Survey Button --}}
                                    <button 
                                        x-data
                                        x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                        class="{{ $navLinkClass(request()->is('surveys/create*')) }} hover:text-[#03b8ff] hover:underline"
                                    >Create Survey</button>
                                @elseif(Auth::user()->isInstitutionAdmin())
                                    {{-- Institution Admin: Links based on institution validity --}}
                                    @if(Auth::user()->hasValidInstitution())
                                        {{-- Create Survey Button (if institution is valid) --}}
                                        <button 
                                        x-data
                                        x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                        class="{{ $navLinkClass(request()->is('surveys/create*')) }} hover:text-[#03b8ff] hover:underline"
                                        >Create Survey</button>
                                        
                                        {{-- Institution Dropdown Menu (if institution is valid) --}}
                                        <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                            <button 
                                                @click="open = !open" 
                                                class="{{ $navLinkClass(request()->is('institution/*')) }} hover:text-[#03b8ff] hover:underline flex items-center"
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
                                                <a href="/institution/analytics" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('institution/analytics')) }} hover:bg-gray-100">Analytics</a>
                                                <a href="/institution/users" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('institution/users')) }} hover:bg-gray-100">Users</a>
                                                <a href="/institution/surveys" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('institution/surveys')) }} hover:bg-gray-100">Surveys</a>
                                                <a href="/institution/announcements" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('institution/announcements')) }} hover:bg-gray-100">Announcements</a>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Disabled links (if institution is invalid) --}}
                                        <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Create Survey</span>
                                        <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Institution</span>
                                    @endif
                                @elseif(Auth::user()->isSuperAdmin())
                                    {{-- Super Admin: Manage Dropdown Menu --}}
                                    <button 
                                        x-data
                                        x-on:click="$dispatch('open-modal', {name: 'select-survey-type'})"
                                        class="{{ $navLinkClass(request()->is('surveys/create*')) }} hover:text-[#03b8ff] hover:underline"
                                    >Create Survey</button>
                                    <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                        <button 
                                            @click="open = !open" 
                                            class="{{ $navLinkClass(request()->is('admin/*')) }} hover:text-[#03b8ff] hover:underline flex items-center"
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
                                            <a href="/admin/analytics" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('admin/analytics')) }} hover:bg-gray-100">Analytics</a>
                                            <a href="/admin/surveys" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('admin/surveys*')) }} hover:bg-gray-100">Manage Surveys</a>
                                            <a href="/admin/announcements" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('admin/announcements')) }} hover:bg-gray-100">Manage Announcements</a>
                                            <a href="/admin/reward-redemptions" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->routeIs('admin.reward-redemptions.index')) }} hover:bg-gray-100">Manage Rewards</a>
                                            <a href="/admin/users" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('admin/users*')) }} hover:bg-gray-100">Manage Users</a>
                                            <a href="/admin/requests" class="block px-4 py-2 text-sm {{ $navLinkClass(request()->is('admin/requests')) }} hover:bg-gray-100">Manage Support Requests</a>
                                        </div>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Desktop Navigation: Right Aligned Links (User Profile, Auth Buttons) -->
                    <div class="flex items-center space-x-5 text-base">
                        @auth
                           
                            
                            {{-- Inbox Dropdown Component --}}
                            <livewire:inbox.inbox-dropdown />

                            {{-- User Profile Link (Authenticated Users) --}}
                            <a href="/profile" class="flex items-center space-x-2 {{ request()->routeIs('profile.index') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">
                                <span class="font-semibold mr-5">{{ Auth::user()->name }}</span>
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-400">
                            </a>
                        @else
                            {{-- Authentication Links (Guest Users) --}}
                            <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'text-[#03b8ff] font-bold' : 'text-gray-700' }} hover:text-[#03b8ff] hover:underline">Register</a>
                            <a href="{{ route('login') }}" class="inline-block bg-[#03b8ff] hover:bg-[#02a0e0] text-white font-bold px-6 py-2 rounded-lg">Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Section (slides in from right) -->
        <div 
            x-show="mobileMenuOpen" 
            x-transition:enter="transition ease-in-out duration-200"
            x-transition:enter-start="transform translate-x-full"
            x-transition:enter-end="transform translate-x-0"
            x-transition:leave="transition ease-in-out duration-200"
            x-transition:leave-start="transform translate-x-0"
            x-transition:leave-end="transform translate-x-full"
            class="fixed top-0 right-0 h-full w-4/5 mobile-menu shadow-lg z-50 p-6 overflow-y-auto"
            style="display: none;"
            @keydown.escape.window="toggleMenu()" {{-- Close menu on Escape key press --}}
        >
            <!-- Mobile Menu Header: Logo and Close Button -->
            <div class="flex justify-between items-center mb-6">
                <a href="/" class="font-bold text-2xl" style="color: #03b8ff;">Formigo</a>
                <button @click="toggleMenu()" class="text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Mobile Menu Navigation Links -->
            <div class="flex flex-col space-y-4 text-lg pt-4">
                @guest
                    {{-- Mobile Menu Links: Guest Users --}}
                    <a href="/" @click="mobileMenuOpen = false" class="{{ $navLinkClass(request()->is('/')) }} hover:text-[#03b8ff]">Home</a>
                    <a href="/about" @click="mobileMenuOpen = false" class="{{ $navLinkClass(request()->is('about')) }} hover:text-[#03b8ff]">About</a>
                    <a href="/rewards-info" @click="mobileMenuOpen = false" class="{{ $navLinkClass(request()->is('rewards-info')) }} hover:text-[#03b8ff]">Rewards</a>
                    <hr class="border-gray-300 my-2"> {{-- Separator --}}
                    {{-- Mobile Menu Authentication Buttons: Guest Users --}}
                    <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="bg-[#03b8ff] hover:bg-[#02a0e0] text-white font-bold px-6 py-2 rounded-lg text-center">Register</a>
                    <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="bg-[#03b8ff] hover:bg-[#02a0e0] text-white font-bold px-6 py-2 rounded-lg text-center">Login</a>
                @else
                    {{-- Mobile Menu: User Profile Section (Authenticated Users) --}}
                    <a href="/profile" @click="mobileMenuOpen = false" class="flex items-center mb-4 pb-4 border-b border-gray-200 {{ request()->routeIs('profile.index') ? 'text-[#03b8ff]' : 'text-gray-700' }} hover:text-[#03b8ff]">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-12 h-12 rounded-full object-cover border border-gray-400">
                        <span class="font-semibold ml-3">{{ Auth::user()->name }}</span>
                    </a>

                    {{-- Mobile Menu: Inbox Link --}}
                    <a href="/inbox" @click="mobileMenuOpen = false" class="flex items-center justify-between {{ $navLinkClass(request()->is('inbox')) }} hover:text-[#03b8ff]">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                            </svg>
                            <span>Inbox</span>
                        </div>
                        
                        {{-- Inbox unread badge (reusing component logic) --}}
                        @livewire('inbox.inbox-dropdown', ['mobileMode' => true])
                    </a>
                    
                    {{-- Mobile Menu Links: Authenticated Users (Common) --}}
                    <a href="/feed" @click="mobileMenuOpen = false" class="flex items-center {{ $navLinkClass(request()->routeIs('feed.index')) }} hover:text-[#03b8ff]">
                        <img src="/images/icons/home.svg" alt="Feed" class="w-6 h-6 mr-2">
                        Feed
                    </a>
                    <a href="/rewards" @click="mobileMenuOpen = false" class="flex items-center {{ $navLinkClass(request()->routeIs('rewards.index')) }} hover:text-[#03b8ff]">
                        <img src="/images/icons/redeem.svg" alt="Redeem" class="w-6 h-6 mr-2">
                        Redeem
                    </a>
                    <a href="/vouchers" @click="mobileMenuOpen = false" class="flex items-center {{ $navLinkClass(request()->routeIs('vouchers.index')) }} hover:text-[#03b8ff]">
                        <img src="/images/icons/voucher.svg" alt="Vouchers" class="w-6 h-6 mr-2">
                        Vouchers
                    </a>
                    
                    {{-- Mobile Menu Links: Role Specific --}}
                    @if(Auth::user()->isResearcher())
                        {{-- Researcher: Create Survey Button --}}
                        <button 
                            x-data
                            @click="mobileMenuOpen = false; $dispatch('open-modal', {name: 'select-survey-type'})"
                            class="flex items-center {{ $navLinkClass(request()->is('surveys/create*')) }} hover:text-[#03b8ff] text-left"
                        >
                            <img src="/images/icons/survey.svg" alt="Create Survey" class="w-6 h-6 mr-2">
                            Create Survey
                        </button>
                    @elseif(Auth::user()->isInstitutionAdmin())
                        {{-- Institution Admin: Links based on institution validity --}}
                        @if(Auth::user()->hasValidInstitution())
                            {{-- Create Survey Button (if institution is valid) --}}
                            <button 
                                x-data
                                @click="mobileMenuOpen = false; $dispatch('open-modal', {name: 'select-survey-type'})"
                                class="flex items-center {{ $navLinkClass(request()->is('surveys/create*')) }} hover:text-[#03b8ff] text-left"
                            >
                                <img src="/images/icons/survey.svg" alt="Create Survey" class="w-6 h-6 mr-2">
                                Create Survey
                            </button>
                            
                            {{-- Institution Sub-menu (if institution is valid) --}}
                            <div class="border-t border-gray-200 pt-2 mt-2">
                                <p class="font-semibold text-gray-700 mb-2">Institution</p>
                                <a href="/institution/analytics" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('institution/analytics')) }} hover:text-[#03b8ff]">Analytics</a>
                                <a href="/institution/users" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('institution/users')) }} hover:text-[#03b8ff]">Users</a>
                                <a href="/institution/surveys" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('institution/surveys')) }} hover:text-[#03b8ff]">Surveys</a>
                                <a href="/institution/announcements" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('institution/announcements')) }} hover:text-[#03b8ff]">Announcements</a>
                            </div>
                        @else
                            {{-- Disabled links (if institution is invalid) --}}
                            <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Create Survey</span>
                            <span class="text-gray-400 cursor-not-allowed" title="Your institution is not active in our system">Institution</span>
                        @endif
                    @elseif(Auth::user()->isSuperAdmin())
                        {{-- Super Admin: Create Survey Button --}}
                        <button 
                            x-data
                            @click="mobileMenuOpen = false; $dispatch('open-modal', {name: 'select-survey-type'})"
                            class="flex items-center {{ $navLinkClass(request()->is('surveys/create*')) }} hover:text-[#03b8ff] text-left"
                        >
                            <img src="/images/icons/survey.svg" alt="Create Survey" class="w-6 h-6 mr-2">
                            Create Survey
                        </button>
                        
                        {{-- Super Admin: Manage Sub-menu --}}
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <p class="font-semibold text-gray-700 mb-2">Manage</p>
                            <a href="/admin/analytics" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('admin/analytics')) }} hover:text-[#03b8ff]">Analytics</a>
                            <a href="/admin/surveys" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('admin/surveys*')) }} hover:text-[#03b8ff]">Manage Surveys</a>
                            <a href="/admin/announcements" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('admin/announcements')) }} hover:text-[#03b8ff]">Manage Announcements</a>
                            <a href="/admin/reward-redemptions" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->routeIs('admin.reward-redemptions.index')) }} hover:text-[#03b8ff]">Manage Rewards</a>
                            <a href="/admin/users" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('admin/users*')) }} hover:text-[#03b8ff]">Manage Users</a>
                            <a href="/admin/requests" @click="mobileMenuOpen = false" class="block pl-2 py-2 {{ $navLinkClass(request()->is('admin/requests')) }} hover:text-[#03b8ff]">Manage Support Requests</a>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Mobile Menu Overlay (closes menu on click) -->
        <div 
            x-show="mobileMenuOpen" 
            @click="toggleMenu()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40" {{-- Overlay to cover the page content --}}
            style="display: none;"
        ></div>
    </nav> <!-- End of Navigation Bar Section -->
    
    <!-- Modal Sections -->
    <!-- Survey Creation Modal -->
    <x-modal name="select-survey-type" title="Create Survey" x-on:open="$nextTick(() => $dispatch('survey-modal-opened'))">
        <livewire:surveys.form-builder.modal.survey-type-modal />
    </x-modal>

    {{-- Support Request Modal --}}
    <x-modal name="support-request-modal" title="Support Request">
        <livewire:support-requests.create-support-request-modal />
    </x-modal>
    
    {{-- Announcement Carousel Modal - now available site-wide --}}
    <x-modal name="announcement-carousel-modal" title="Announcements" focusable>
        @livewire('super-admin.announcements.modal.announcement-carousel')
    </x-modal>
    <!-- End of Modal Sections -->

    <!-- Main Content Area -->
    <main class="mx-auto bg-gray-50 min-h-screen" x-data="{}" x-init="
        // Show announcement only on specific routes
        $nextTick(() => {
            const allowedPaths = [
                '/feed',
                '/profile',
                '/rewards',
                '/vouchers'
            ];
            // Also allow /surveys/create and /surveys/create/{uuid}
            const path = window.location.pathname;
            const isCreateSurvey = path.startsWith('/surveys/create');
            if (
                (allowedPaths.includes(path) || isCreateSurvey)
                && !sessionStorage.getItem('announcementShown')
            ) {
                $dispatch('open-modal', { name: 'announcement-carousel-modal' });
                sessionStorage.setItem('announcementShown', 'true');
            }
        });
    ">
        @yield('content') {{-- Blade directive to output the content of the current section --}}
    </main>
    <!-- End of Main Content Area -->

    <!-- Additional Scripts Section -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> {{-- Chart.js library --}}
    @stack('scripts') {{-- Blade directive to push scripts from child views --}}
    @livewireScripts <!-- Required Livewire scripts -->
    
    <!-- Survey Creation Success Modal Event Listener -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Existing survey-created-success event listener
            Livewire.on('survey-created-success', (eventData) => {
                let surveyData = {};
                let uuid = null;

                // Direct access if eventData is the object itself
                if (eventData && typeof eventData === 'object' && eventData.uuid) {
                    surveyData = eventData;
                    uuid = eventData.uuid;
                }
                // Common Livewire event format: array with object as first element
                else if (Array.isArray(eventData) && eventData.length > 0 && eventData[0].uuid) {
                    surveyData = eventData[0];
                    uuid = eventData[0].uuid;
                }

                const title = surveyData.title || 'Untitled';
                const type = surveyData.type || 'new';

                Swal.fire({
                    icon: 'success',
                    title: 'Survey Created Successfully!',
                    text: `Your ${type} survey "${title}" has been created and is ready for editing.`,
                    confirmButtonText: 'Go to Survey Editor',
                    confirmButtonColor: '#03b8ff',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (uuid) {
                            window.location.href = `/surveys/create/${uuid}`;
                        } else {
                            window.location.href = '/feed';
                        }
                    }
                });
            });
            
            // New OTP verification success event listener
            Livewire.on('otp-verified-success', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Verified!',
                    text: 'Your account has been created successfully.',
                    showConfirmButton: true,
                    confirmButtonText: 'Go to Feed',
                    confirmButtonColor: '#3B82F6',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('feed.index') }}';
                    }
                });
            });
        });
    </script>
    <!-- End of Additional Scripts Section -->

    <!-- XP Test Control Panel (Only visible for authenticated users) -->
    @auth
        <div x-data="{ open: false }" class="fixed bottom-0 right-0 m-4 z-50">
            <!-- Floating toggle button - only visible when panel is closed -->
            <button 
                x-show="!open"
                @click="open = !open" 
                class="bg-purple-600 text-white p-2 rounded-full shadow-lg hover:bg-purple-700 focus:outline-none"
                
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </button>
            
            <!-- Control panel -->
            <div
                x-cloak 
                x-show="open" 
                
                class="bg-white border border-gray-200 rounded-lg shadow-xl p-4 mb-2 w-64"
            >
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-purple-800">Test Controls</h3>
                    <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="w-full">
                    <livewire:test-controls />
                </div>
            </div>
        </div>
    @endauth

    <!-- Include Level-Up Listener -->
    @include('livewire.rewards.partials.level-up-listener')
</body>
</html>
</body>
</html>
