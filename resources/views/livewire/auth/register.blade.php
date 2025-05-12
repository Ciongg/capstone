{{-- filepath: resources/views/livewire/auth/register.blade.php --}}
<div>
    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md overflow-y-auto max-h-[90vh]">
            <header class="mb-6 text-center">
                <h1 class="text-4xl font-bold text-[#00BBFF]">Formigo</h1>
                <p class="text-gray-500 mt-2">Create your account to get started.</p>
            </header>

            <form wire:submit.prevent="registerUser" x-data="{ checkboxChecked: false }">
                @csrf

                <!-- Name Fields - Side by Side -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input 
                            type="text" 
                            wire:model.defer="first_name"
                            id="first_name" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror"
                            required
                        >
                        @error('first_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input 
                            type="text" 
                            wire:model.defer="last_name"
                            id="last_name" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror"
                            required
                        >
                        @error('last_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="emailReg" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input 
                        type="email" 
                        wire:model.defer="email"
                        id="emailReg" 
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                        required
                    >
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Phone Number -->
                <div class="mb-4">
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input 
                        type="text" 
                        wire:model.defer="phone_number"
                        id="phone_number" 
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('phone_number') border-red-500 @enderror"
                        required
                    >
                    @error('phone_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Password Fields - Side by Side -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Password -->
                    <div>
                        <label for="passwordReg" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input 
                            type="password" 
                            wire:model.defer="password"
                            id="passwordReg" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                            required
                        >
                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input 
                            type="password" 
                            wire:model.defer="password_confirmation"
                            id="password_confirmation" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input 
                            type="checkbox"
                            x-model="checkboxChecked" 
                            wire:model="terms"
                            id="terms"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('terms') border-red-500 @enderror"
                        >
                        <span class="ml-2 text-sm text-gray-600">I agree to the <a href="#" class="text-blue-600 hover:underline">Terms and Conditions</a>.</span>
                    </label>
                    @error('terms') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- For debugging - Remove after fixing -->
                <div class="text-xs text-gray-500 mb-2 text-center">
                    Terms accepted: <span x-text="checkboxChecked ? 'Yes' : 'No'"></span>
                </div>

                <!-- Button -->
                <div class="flex justify-center">
                    <button 
                        type="submit" 
                        class="w-full py-2 px-6 rounded-lg transition"
                        :class="{ 'bg-[#00BBFF] hover:bg-blue-400 text-white cursor-pointer': checkboxChecked, 'bg-gray-300 text-gray-500 cursor-not-allowed': !checkboxChecked }"
                        :disabled="!checkboxChecked"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="registerUser">Register</span>
                        <span wire:loading wire:target="registerUser">Registering...</span>
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? <a href="{{ route('login') }}" wire:navigate class="text-blue-600 hover:underline">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
