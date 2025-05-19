<div>
    <div class="mt-8 min-h-screen flex items-center justify-center">
        <!-- Main container with centered shadow and rounded corners -->
        <div class="bg-white shadow-[0_0_25px_rgba(0,0,0,0.15)] rounded-3xl flex flex-col md:flex-row w-full max-w-6xl overflow-hidden">
            <!-- Left container: Image Carousel -->
            @include('components.carousel')
            
            <!-- Right container: White outer container -->
            <div class="w-full md:w-1/2 bg-white p-8 flex items-center justify-center">
                <!-- Blue inner container with inset shadow - adjusted padding -->
                <div class="w-full bg-[#D4F3FF] p-10 rounded-2xl shadow-inner py-12">
                    <header class="mb-10 text-center">
                        <h1 class="text-3xl font-bold text-[#03b8ff] mb-3">Welcome Back!</h1>
                        <p class="text-gray-800 text-lg">Enter your login credentials</p>
                    </header>

                    <form wire:submit.prevent="attemptLogin" class="space-y-8">
                        @csrf

                        <!-- Email - added more spacing -->
                        <div class="mb-8 relative">
                            <label for="emailLogin" class="block text-sm font-medium text-gray-800 mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input 
                                    type="email" 
                                    wire:model.defer="email"
                                    id="emailLogin" 
                                    class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('email') border-red-400 @enderror"
                                    placeholder="enter your email address"
                                    required
                                >
                            </div>
                            @error('email') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password - added more spacing -->
                        <div class="mb-4 relative">
                            <label for="passwordLogin" class="block text-sm font-medium text-gray-800 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input 
                                    type="password" 
                                    wire:model.defer="password"
                                    id="passwordLogin" 
                                    class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('password') border-red-400 @enderror"
                                    placeholder="enter your password"
                                    required
                                >
                              
                            </div>
                            @error('password') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Forgot password link - increased spacing -->
                        <div class="mb-8 text-right">
                            <a href="#" class="text-[#03b8ff] underline text-sm">Forgot Password?</a>
                        </div>

                        <!-- Login Button - increased spacing -->
                        <div class="mb-8">
                            <button 
                                type="submit" 
                                class="w-full py-3 rounded-lg transition bg-[#03b8ff] hover:bg-blue-500 text-white font-bold text-lg uppercase"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="attemptLogin">LOGIN</span>
                                <span wire:loading wire:target="attemptLogin">Logging in...</span>
                            </button>
                        </div>

                        <div class="text-center pt-2">
                            <p class="text-gray-800 text-sm">
                                Don't have an account? <a href="{{ route('register') }}" wire:navigate class="text-[#03b8ff] underline font-semibold">Sign Up</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
