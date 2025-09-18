<div class="bg-white">
    <div class="min-h-screen flex items-center justify-center mt-2">
        <!-- Main container with centered shadow and rounded corners -->
        <div class="bg-white shadow-[0_0_25px_rgba(0,0,0,0.15)] rounded-3xl flex flex-col md:flex-row w-full max-w-6xl overflow-hidden">
            <!-- Left container: Image Carousel - Hidden on small screens, full width on md+ -->
            <div class="hidden md:block md:w-1/2 md:flex md:justify-center md:items-center">
                <div class="w-full h-full">
                    @include('components.carousel')
                </div>
            </div>
            
            <!-- Right container: White outer container - Full width on small screens -->
            <div class="w-full md:w-1/2 bg-white p-4 sm:p-8 flex items-center justify-center">
                <!-- Blue inner container with inset shadow - adjusted padding -->
                <div class="w-full bg-[#D4F3FF] p-4 sm:p-6 rounded-2xl shadow-inner py-4 sm:py-6">
                    <header class="mb-6 text-center">
                        <h1 class="text-3xl font-bold text-blue-500 mb-3">Welcome Back!</h1>
                        <p class="text-gray-800 text-lg">Enter your login credentials</p>
                    </header>

                    @if($warningMessage)
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800">
                                        {{ $warningMessage }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="attemptLogin" class="space-y-8">
                        @csrf

                        <!-- Email - added more spacing -->
                        <div class="mb-4 relative">
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
                                    maxlength="254"
                                    required
                                >
                            </div>
                            @error('email') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
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
                                    maxlength="128"
                                    required
                                >
                              
                            </div>
                            @error('password') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Remember Me and Forgot password row -->
                        <div class="mb-4 flex justify-between items-center">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox"
                                    wire:model="remember"
                                    id="remember"
                                    class="cursor-pointer h-5 w-5 rounded border-gray-300 text-blue-500 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2 text-sm text-gray-700">Remember me for 30 days</span>
                            </label>
                            <button type="button" wire:click="$dispatch('open-modal', { name: 'forgot-password' })" class="cursor-pointer text-blue-500 italic text-sm hover:text-blue-700">Forgot Password?</button>
                        </div>

                        <!-- Login Button - increased spacing -->
                        <div class="mb-4">
                            <button 
                                type="submit" 
                                class="cursor-pointer w-full py-3 rounded-lg transition bg-[#03b8ff] hover:bg-blue-500 text-white font-bold text-lg uppercase"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="attemptLogin">LOGIN</span>
                                <span wire:loading wire:target="attemptLogin">Logging in...</span>
                            </button>
                        </div>

                        <!-- OR Divider -->
                        <div class="flex items-center my-4">
                            <div class="flex-grow border-t border-gray-300"></div>
                            <span class="mx-2 text-gray-500 text-sm">or</span>
                            <div class="flex-grow border-t border-gray-300"></div>
                        </div>

                        <!-- Google Login Button -->
                        <div class="mb-4">
                            <a 
                                href="{{ route('google.redirect', ['remember' => $remember ? 1 : 0]) }}"
                                class="w-full flex items-center justify-center py-3 px-6 rounded-lg transition font-medium bg-white border border-gray-300 hover:bg-gray-100 text-gray-800"
                            >
                                <img src="/images/icons/google.svg" alt="Google" class="h-5 w-5 mr-2 inline" />
                                <span class="ml-2">Login with Google</span>
                            </a>
                        </div>

                        <div class="text-center pt-2">
                            <p class="text-gray-800 text-md">
                                Don't have an account? <a href="{{ route('register') }}" wire:navigate class="text-blue-500 italic hover:text-blue-700">Sign Up</a>
                            </p>
                            <div class="flex items-center justify-center my-3">
                                <div class="flex-grow border-t border-gray-300"></div>
                        
                            </div>
                            <div class="flex items-center justify-center gap-3">
                                <a target="_blank" href="{{ route('terms-of-use') }}" class="text-sm text-blue-500 hover:text-blue-700">Terms & Conditions</a>
                                <span class="h-4 w-px bg-gray-300"></span>
                                <a target="_blank" href="{{ route('privacy-policy') }}" class="text-sm text-blue-500 hover:text-blue-700">Privacy Policy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <livewire:auth.forgot-password />
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Handle validation errors
    window.addEventListener('validation-error', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: event.detail.message || 'Please check your input and try again.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    // Handle login errors
    window.addEventListener('login-error', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: event.detail.message || 'Invalid credentials. Please try again.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    // Handle archived account error
    window.addEventListener('archived-account', function (event) {
        Swal.fire({
            icon: 'warning',
            title: 'Account Archived',
            text: event.detail.message || 'This account has been archived. Please contact the Formigo support team for assistance.',
            showConfirmButton: true,
        });
    });
    
    // Handle account status changes
    window.addEventListener('account-status-change', function (event) {
        Swal.fire({
            icon: event.detail.type || 'info',
            title: event.detail.title || 'Account Status Updated',
            text: event.detail.message,
            timer: 4000,
            showConfirmButton: true,
        });
    });
</script>
@endpush
