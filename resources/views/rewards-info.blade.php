@extends('components.layouts.app')

@section('content')
<main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Updated Hero Section with Gray Box -->
    <div class="bg-gray-100 rounded-xl p-8 mb-4 shadow-md">
        <div class="text-center mb-8">
            <!-- Updated heading with gift icon to the right -->
            <div class="flex justify-center items-center mb-6">
                <h1 class="text-4xl lg:text-5xl text-gray-800 font-semibold">Complete Surveys & <br>get exclusive rewards</h1>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-[#03b8ff] ml-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                </svg>
            </div>
            
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Complete surveys on our platform and earn exclusive rewards for your time and insights, making your participation both meaningful and rewarding. The more you engage, the more you gain!
            </p>
        </div>
        
        <!-- Two Cards for Sign Up and Earn -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <!-- Sign Up Card -->
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Sign Up</h3>
                <p class="text-gray-600">Become a member of the academic community</p>
            </div>
            
            <!-- Earn Points Card -->
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z" fill="currentColor"/>
                        <path d="M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z" fill="currentColor"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Answer & Earn Points</h3>
                <p class="text-gray-600">Gather points and redeem for rewards!</p>
            </div>
        </div>
    </div>


        <!-- Call to Action -->
    <section class="text-center py-12 bg-gradient-to-r from-blue-500 to-[#03b8ff] rounded-lg text-white">
        <h2 class="text-3xl font-bold mb-4">Start Earning Rewards Today!</h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto">Join Formigo now to create surveys, earn points, and unlock valuable rewards that enhance your research experience.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-white hover:bg-gray-100 text-[#03b8ff] font-semibold px-8 py-3 rounded-lg text-lg transition duration-200">
                Sign Up Free
            </a>
            <a href="{{ route('rewards.index') }}" class="bg-transparent hover:bg-white/10 text-white font-semibold px-8 py-3 rounded-lg text-lg border border-white transition duration-200">
                Browse Rewards
            </a>
        </div>
    </section>

    

    <!-- Experience Points Section -->
    <section class="mb-16">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8 mt-8 text-center">Experience Points & Levels</h2>
        
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <p class="text-gray-700 mb-6">As you use Formigo, you'll gain experience points (XP) that help you level up. Higher levels unlock access to better rewards and more valuable vouchers from our brand partners.</p>
                
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 rounded-full bg-yellow-500 text-white flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Level Progression</h3>
                        <p class="text-gray-600">Each level requires more XP than the previous one, reflecting your growing expertise in research.</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-yellow-500 text-white flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Exclusive Rewards</h3>
                        <p class="text-gray-600">Higher levels unlock premium voucher rewards from top brands and higher value rewards.</p>
                    </div>
                </div>
            </div>
            
         
        </div>
    </section>

    <!-- System and Voucher Rewards -->
    <div class="flex flex-col md:flex-row justify-center items-center gap-8 mb-16">
        <div class="bg-white p-6 rounded-lg shadow-md max-w-md">
            <div class="w-16 h-16 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-center mb-3">System Rewards</h3>
            <p class="text-gray-600 text-center">Boost your survey visibility and reach your target audience faster with our system rewards.</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md max-w-md">
            <div class="w-16 h-16 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-center mb-3">Voucher Rewards</h3>
            <p class="text-gray-600 text-center">Exchange your earned points for real-world gift vouchers from our partner brands and stores.</p>
        </div>
    </div>


</main>
@endsection
