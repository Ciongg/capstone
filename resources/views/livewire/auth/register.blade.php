<div>
    <div class="min-h-screen flex items-center justify-center -mt-10">
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
                        <h1 class="text-2xl font-bold text-[#03b8ff] mb-2">Create Your Account</h1>
                        <p class="text-gray-800">Fill in your information to get started</p>
                    </header>

                    <form wire:submit.prevent="registerUser" x-data="{ checkboxChecked: false }">
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
                                        required
                                    >
                                </div>
                                @error('first_name') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
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
                                        required
                                    >
                                </div>
                                @error('last_name') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
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
                                    required
                                >
                            </div>
                            @error('email') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
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
                                    required
                                >
                            </div>
                            @error('phone_number') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
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
                                    </div>
                                    <input 
                                        type="password" 
                                        wire:model.defer="password"
                                        id="passwordReg" 
                                        class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('password') border-red-400 @enderror"
                                        placeholder="Create password"
                                        required
                                    >
                                </div>
                                @error('password') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-800 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </div>
                                    <input 
                                        type="password" 
                                        wire:model.defer="password_confirmation"
                                        id="password_confirmation" 
                                        class="w-full pl-10 border border-white/50 bg-white text-gray-700 rounded-lg p-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                        placeholder="Confirm password"
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
                                    class="rounded border-gray-300 text-[#03b8ff] shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('terms') border-red-500 @enderror"
                                >
                                <span class="ml-2 text-sm text-gray-800">I agree to the <a href="#" class="text-[#03b8ff] underline">Terms and Conditions</a>.</span>
                            </label>
                            @error('terms') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Button -->
                        <div class="mb-4">
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

                        <div class="text-center">
                            <p class="text-gray-800 text-sm">
                                Already have an account? <a href="{{ route('login') }}" wire:navigate class="text-[#03b8ff] underline font-semibold">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
