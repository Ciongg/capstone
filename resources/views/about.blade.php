<x-layouts.app>
<main class="container mx-auto py-12 px-4">
    <!-- Hero Section -->
    <div class="text-center mb-16 bg-gray-100 rounded-lg py-8 px-6">
        <h1 class="text-4xl lg:text-5xl text-gray-800 font-semibold mb-4">Together We Empower Research!</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">Connecting academic researchers with willing participants through an innovative, reward-based survey platform.</p>
    </div>

    <!-- About Us Section -->
    <section class="mb-16">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8 text-center">About Formigo</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- About Card 1 -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>

                    </div>
                    <h3 class="text-2xl font-semibold text-gray-800">Our Mission</h3>
                </div>
                <p class="text-gray-700 mb-6 text-justify">Formigo is a dedicated platform for academic communities where people, especially students, can easily create and post their surveys. We connect researchers with willing participants through an incentivized system that rewards survey responses.</p>
            </div>
            
            <!-- About Card 2 -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-800">The Problem We Solve</h3>
                </div>
                <p class="text-gray-700 mb-6 text-justify">Researchers often struggle to collect quality survey responses because of low engagement, lack of targeting, and unmotivated participants. Traditional methods offer little control over who responds, leading to irrelevant or unreliable data, while also raising concerns about privacy and data security.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="text-center py-12 bg-gray-100 rounded-lg mb-16">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Ready to transform research?</h2>
        <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">Join Formigo today and be part of a community that's revolutionizing academic research.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-[#03b8ff] hover:bg-[#02a0e0] text-white font-semibold px-8 py-3 rounded-lg text-lg">
                Sign Up Now
            </a>
            <a href="{{ route('login') }}" class="bg-white hover:bg-gray-100 text-[#03b8ff] font-semibold px-8 py-3 rounded-lg text-lg border border-[#03b8ff]">
                Log In
            </a>
        </div>
    </section>

    <!-- Key Features Section -->
    <section class="mb-16">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8 text-center">Key Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Academic Platform</h3>
                <p class="text-gray-600">Dedicated platform for academic communities to create, distribute, and collect survey responses.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Incentive-Based System</h3>
                <p class="text-gray-600">Motivate participation through real rewards and light gamification elements.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Targeted Distribution</h3>
                <p class="text-gray-600">Connect with precisely the participants you need based on demographics and institutional affiliation.</p>
            </div>
            
            <!-- Feature 4 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Survey Analytics</h3>
                <p class="text-gray-600">Comprehensive tools for survey creation, management, and analyzing results.</p>
            </div>
            
            <!-- Feature 5 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Trust Score System</h3>
                <p class="text-gray-600">Build credibility with user trust scores and response validation reporting.</p>
            </div>
            
            <!-- Feature 6 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#03b8ff] text-white flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">AI & Translation</h3>
                <p class="text-gray-600">Integrated AI result summarization and language translation capabilities.</p>
            </div>
        </div>
    </section>

    <!-- Partnership Benefits -->
    <section class="mb-16">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8 text-center">Partnership Benefits</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- For Universities -->
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-[#03b8ff]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                    For Universities
                </h3>
                <ul class="space-y-3 text-gray-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Verified researchers with school-specific email accounts
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Insights on survey data gathering and research topics
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Streamlined in-house surveys and evaluations
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Enhanced brand visibility and recognition
                    </li>
                </ul>
            </div>
            
            <!-- For Brands -->
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-[#03b8ff]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    For Brands
                </h3>
                <ul class="space-y-3 text-gray-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Direct advertisement through voucher offerings
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Increased brand awareness and customer traction
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Access to academic audience demographics
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#03b8ff] mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Support for academic research initiatives
                    </li>
                </ul>
            </div>
        </div>
    </section>
</main>
</x-layouts.app>