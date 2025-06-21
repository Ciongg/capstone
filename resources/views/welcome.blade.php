@extends('components.layouts.app')

@section('content')
<main class="container mx-auto py-12 px-4 sm:px-8">
    <div class="flex flex-col md:flex-row items-center mb-16">
        <!-- Left Section: Text, Button, Stars - Explicitly centered on mobile -->
        <div class="md:w-1/2 text-center md:text-left mb-8 md:mb-0 md:pr-8">
            <h1 class="text-4xl lg:text-5xl text-gray-800 mb-6 leading-tight">Powering Research<br>Rewarding Participants</h1>
            
            <!-- Center button on mobile, left-aligned on desktop -->
            <div class="flex justify-center md:justify-start">
                <a href="{{ route('login') }}" class="mt-4 mb-8 inline-block bg-[#03b8ff] hover:bg-[#02a0e0] text-white font-bold px-8 py-2 rounded-lg text-lg">
                    Login
                </a>
            </div>
            
            <!-- Center reviews section on mobile -->
            <div class="flex justify-center md:justify-start mt-6">
                <!-- Profile Pictures of Reviewers - Now First -->
                <div class="flex -space-x-3 mb-2 mr-3">
                    <img src="{{ asset('images/landing/person1.jpg') }}" alt="Reviewer 1" class="w-8 h-8 rounded-full border-2 border-white object-cover">
                    <img src="{{ asset('images/landing/person2.jfif') }}" alt="Reviewer 2" class="w-8 h-8 rounded-full border-2 border-white object-cover">
                    <img src="{{ asset('images/landing/person4.jpg') }}" alt="Reviewer 3" class="w-8 h-8 rounded-full border-2 border-white object-cover">
                </div>
                
                <!-- Stars in a single row -->
                <div class="flex flex-col items-start mt-1">
                    <div class="flex">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400 fill-current inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        @endfor
                    </div>
                    <span class="text-gray-600 text-sm mt-1">100+ reviews</span>
                </div>
            </div>
        </div>

        <!-- Right Section: Images -->
        <div class="md:w-1/2">
            <!-- Small screens (under 768px): Single image -->
            <div class="md:hidden flex justify-center">
                <img src="{{ asset('images/landing/feed-full.png') }}" alt="Landing Image 1" class="w-3/4 rounded-lg shadow-xl">
            </div>
            
            <!-- Larger screens (768px+): Overlapping images -->
            <div class="hidden md:block relative flex justify-center md:justify-center items-center h-64 md:h-auto px-4">
                <img src="{{ asset('images/landing/feed-full.png') }}" alt="Landing Image 1" class="absolute w-3/4 lg:w-2/3 rounded-lg shadow-xl transform translate-x-[20px] translate-y-[-20px]">
                <img src="{{ asset('images/landing/rewards.png') }}" alt="Landing Image 2" class="relative w-3/4 lg:w-2/3 rounded-lg shadow-xl transform translate-x-[120px] translate-y-[90px]">
            </div>
        </div>
    </div>

    <!-- Getting Started Section - Already centered in mobile -->
    <section class="mt-16 py-8 sm:py-12 px-4">
        <div class="bg-gray-100 py-8 sm:py-12 rounded-lg mb-8 sm:mb-10 text-center">
            <h2 class="text-4xl sm:text-5xl text-center text-gray-800">Getting Started is Easy</h2>
        </div>
        
        <!-- Card grid - Already using text-center -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-gray-700">
            <div class="text-center">
                {{-- Login SVG Icon --}}
                <svg class="w-16 h-16 text-[#03b8ff] mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <h3 class="text-lg font-semibold">Login</h3>
                <p class="text-sm mt-2">If you don’t have an account, create one. Use school email for educational access.</p>
                <a href="{{ route('register') }}" class="text-[#03b8ff] text-sm mt-1 inline-block font-medium hover:underline">CREATE ACCOUNT</a>
            </div>
            <div class="text-center">
                {{-- Survey SVG Icon --}}
                <svg class="w-16 h-16 text-[#03b8ff] mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                </svg>
                <h3 class="text-lg font-semibold">Answer Applicable Survey</h3>
                <p class="text-sm mt-2">Choose a survey that matches your profile and eligibility requirements.</p>
            </div>
            <div class="text-center">
                {{-- Points/Reward SVG Icon --}}
                <svg class="w-16 h-16 text-[#03b8ff] mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <h3 class="text-lg font-semibold">Earn points</h3>
                <p class="text-sm mt-2">Rack up points by answering surveys and redeem them for rewards.</p>
                <a href="/rewards" class="text-[#03b8ff] text-sm mt-1 inline-block font-medium hover:underline">VIEW REWARDS</a>
            </div>
        </div>
    </section>
</main>

@endsection
