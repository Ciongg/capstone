{{-- filepath: resources/views/livewire/auth/login.blade.php --}}
<div>
    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
            <header class="mb-10 text-center">
                <h1 class="text-4xl font-bold text-[#00BBFF]">Formigo</h1>
                <p class="text-gray-500 mt-2">Welcome Back! Please login to continue.</p>
            </header>

            <form wire:submit.prevent="attemptLogin">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="emailLogin" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        wire:model.defer="email"
                        id="emailLogin" 
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                        required
                    >
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="passwordLogin" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input 
                        type="password" 
                        wire:model.defer="password"
                        id="passwordLogin" 
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                        required
                    >
                    @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Button -->
                <div class="flex justify-center">
                    <button 
                        type="submit" 
                        class="w-full py-2 px-6 rounded-lg transition bg-[#00BBFF] hover:bg-blue-400 text-white cursor-pointer"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="attemptLogin">Login</span>
                        <span wire:loading wire:target="attemptLogin">Logging in...</span>
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account? <a href="{{ route('register') }}" wire:navigate class="text-blue-600 hover:underline">Register here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
