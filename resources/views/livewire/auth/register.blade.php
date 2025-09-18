<div class="bg-white" x-data="{ checkboxChecked: false }">
    <div class="min-h-screen flex items-center justify-center mt-10">
        <!-- Main container with centered shadow and rounded corners -->
        <div class="bg-white shadow-[0_0_25px_rgba(0,0,0,0.15)] rounded-3xl flex flex-col md:flex-row w-full max-w-6xl overflow-hidden">
            <!-- Left container: Image Carousel - Hidden on small screens, full width on md+ -->
            <div class="hidden md:block md:w-1/2 md:flex md:justify-center md:items-center">
                <div class="w-full h-full">
                    @include('components.carousel')
                </div>
            </div>
            
            <!-- Right container: Registration Form - Full width on small screens -->
            <div class="w-full md:w-1/2 bg-white p-4 sm:p-8 flex items-center justify-center">
                <!-- Blue inner container with inset shadow -->
                <div class="w-full bg-[#D4F3FF] p-3 sm:p-5 rounded-2xl shadow-inner">
                    <header class="mb-6 text-center">
                        <h1 class="text-2xl font-bold text-blue-500 mb-2">Create Your Account</h1>
                        <p class="text-gray-800">Fill in your information to get started</p>
                    </header>

                    <form wire:submit.prevent="registerUser">
                        @csrf

                        <!-- Name Fields - Stack on small screens, side by side on md+ -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-800 mb-1">First Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        wire:model.defer="first_name"
                                        id="first_name" 
                                        class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('first_name') border-red-400 @enderror"
                                        placeholder="Enter your first name"
                                        maxlength="40"
                                        required
                                    >
                                </div>
                                @error('first_name') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-800 mb-1">Last Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        wire:model.defer="last_name"
                                        id="last_name" 
                                        class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('last_name') border-red-400 @enderror"
                                        placeholder="Enter your last name"
                                        maxlength="40"
                                        required
                                    >
                                </div>
                                @error('last_name') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="emailReg" class="block text-sm font-medium text-gray-800 mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input 
                                    type="email" 
                                    wire:model.defer="email"
                                    id="emailReg" 
                                    class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('email') border-red-400 @enderror"
                                    placeholder="Enter your email address"
                                    maxlength="256"
                                    required
                                >
                            </div>
                            @error('email') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone_number" class="block text-sm font-medium text-gray-800 mb-1">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model.defer="phone_number"
                                    id="phone_number" 
                                    class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('phone_number') border-red-400 @enderror"
                                    placeholder="Enter your phone number"
                                    maxlength="11"
                                    required
                                >
                            </div>
                            @error('phone_number') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password Fields - Stack on small screens, side by side on md+ -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <!-- Password -->
                            <div>
                                <label for="passwordReg" class="block text-sm font-medium text-gray-800 mb-1">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="password" 
                                        wire:model.defer="password"
                                        id="passwordReg" 
                                        class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('password') border-red-400 @enderror"
                                        placeholder="Create password"
                                        maxlength="128"
                                        required
                                    >
                                </div>
                                @error('password') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-800 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="password" 
                                        wire:model.defer="password_confirmation"
                                        id="password_confirmation" 
                                        class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                        placeholder="Confirm password"
                                        maxlength="128"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox"
                                    x-model="checkboxChecked" 
                                    wire:model="terms"
                                    id="terms"
                                    class="rounded cursor-pointer border-gray-300 text-blue-500 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5 @error('terms') border-red-500 @enderror"
                                >
                                <span class="ml-2 text-sm text-gray-800">
                                    I agree to the 
                                    <a href="{{ route('terms-of-use') }}" class="text-blue-500 italic hover:text-blue-700" target="_blank">Terms and Conditions</a>
                                    and
                                    <a href="{{ route('privacy-policy') }}" class="text-blue-500 italic hover:text-blue-700" target="_blank">Privacy Policy</a>.
                                </span>
                            </label>
                            @error('terms') <span class="text-red-600 text-xs mt-1 hidden">{{ $message }}</span> @enderror
                        </div>

                        <!-- Button -->
                        <div class="mb-4" x-data x-init="$watch('$wire.showOtpModal', value => { if (value) { $nextTick(() => $dispatch('open-modal', { name: 'otp-verification' })) } })">
                            <button 
                                type="submit" 
                                class="w-full py-2 px-6 rounded-lg transition"
                                :class="{ 'bg-[#03b8ff] hover:bg-[#02a0e0] text-white cursor-pointer': checkboxChecked, 'bg-gray-300 text-gray-500 cursor-not-allowed': !checkboxChecked }"
                                :disabled="!checkboxChecked"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="registerUser">REGISTER</span>
                                <span wire:loading wire:target="registerUser">Registering...</span>
                            </button>

                            
                        </div>

                        <!-- OR Divider -->
                        <div class="flex items-center my-4">
                            <div class="flex-grow border-t border-gray-300"></div>
                            <span class="mx-2 text-gray-500 text-sm">or</span>
                            <div class="flex-grow border-t border-gray-300"></div>
                        </div>

                        <!-- Google Register Button -->
                        <div class="mb-4">
                            <a 
                                href="{{ route('google.redirect') }}"
                                class="w-full flex items-center justify-center py-2 px-6 rounded-lg transition font-medium"
                                :class="{ 'bg-white border border-gray-300 hover:bg-gray-100 text-gray-800 cursor-pointer': checkboxChecked, 'bg-gray-200 text-gray-400 cursor-not-allowed': !checkboxChecked }"
                                :style="'pointer-events:' + (checkboxChecked ? 'auto' : 'none')"
                            >
                                <img src="/images/icons/google.svg" alt="Google" class="h-5 w-5 mr-2 inline" />
                                <span class="ml-2">Register with Google</span>
                            </a>
                        </div>

                        <div class="text-center">
                            <p class="text-gray-800 text-md">
                                Already have an account? <a href="{{ route('login') }}" wire:navigate class="text-blue-500 italic hover:text-blue-700">Login</a>
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

    <!-- OTP Verification Modal -->
    @include('livewire.auth.otp-verification-modal')
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Handle validation errors - use the specific message from the PHP component
    window.addEventListener('validation-error', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: event.detail.message || 'Please check your input and try again.',
            timer: 4000,
            showConfirmButton: true,
        });
    });
    
    // Handle password mismatch specifically
    window.addEventListener('password-mismatch', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: event.detail.message || 'The passwords do not match. Please make sure both password fields are identical.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    // Handle password strength requirements
    window.addEventListener('password-strength-error', function (event) {
        Swal.fire({
            icon: 'warning',
            title: 'Password Requirements Not Met',
            text: event.detail.message || 'Password must contain at least one uppercase letter and one special character.',
            timer: 4000,
            showConfirmButton: true,
        });
    });
    
    // Handle registration errors
    window.addEventListener('registration-error', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: event.detail.message || 'An error occurred during registration. Please try again.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    // Handle duplicate email/phone errors
    window.addEventListener('duplicate-email', function (event) {
        Swal.fire({
            icon: 'warning',
            title: 'Email Already Exists',
            text: event.detail.message || 'This email address is already registered. Please use a different email or try logging in.',
            showConfirmButton: true,
        });
    });
    
    window.addEventListener('duplicate-phone', function (event) {
        Swal.fire({
            icon: 'warning',
            title: 'Phone Number Already Exists',
            text: event.detail.message || 'This phone number is already registered. Please use a different phone number.',
            showConfirmButton: true,
        });
    });
    
    // Handle OTP errors
    window.addEventListener('otp-error', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'OTP Error',
            text: event.detail.message || 'Invalid or expired verification code. Please try again.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    // Handle email sending errors
    window.addEventListener('email-error', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Email Error',
            text: event.detail.message || 'Failed to send verification email. Please try again.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    // Handle success messages
    window.addEventListener('registration-success', function (event) {
        Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: event.detail.message || 'Your account has been created successfully.',
            timer: 2000,
            showConfirmButton: false,
        });
    });
    
    window.addEventListener('otp-sent', function (event) {
        Swal.fire({
            icon: 'info',
            title: 'Verification Code Sent',
            text: event.detail.message || 'A verification code has been sent to your email.',
            timer: 2000,
            showConfirmButton: false,
        });
    });
    
    // Handle existing OTP found case
    window.addEventListener('existing-otp-found', function (event) {
        Swal.fire({
            icon: 'info',
            title: 'Verification Code Already Sent',
            text: event.detail.message || 'A verification code was already sent to your email.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
</script>
@endpush
