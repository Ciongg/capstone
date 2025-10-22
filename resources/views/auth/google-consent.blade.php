<x-layouts.app>
<main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl p-8 shadow-md max-w-lg mx-auto" x-data="{ agreed: false }">
        <h1 class="text-3xl font-bold text-center mb-6">Sign in to Formigo</h1>
        <div class="flex flex-col items-center mb-8">
            <img src="{{ $googleUser->avatar }}" alt="Profile" class="w-20 h-20 rounded-full mb-2 shadow">
            <div class="text-lg font-semibold">{{ $googleUser->name }}</div>
            <div class="text-gray-600">{{ $googleUser->email }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
            <p class="text-gray-700 text-center mb-4">
                <span class="font-semibold">Google will allow Formigo to access:</span>
            </p>
            <div class="flex flex-col items-center gap-2 mb-4">
                <div class="flex items-center gap-2">
                   
                    <span>Name</span>
                </div>
                <div class="flex items-center gap-2">
                  
                    <span>Email address</span>
                </div>
            </div>
            <p class="text-gray-600 text-center">
                Please review our 
                <a href="{{ route('privacy-policy') }}" class="text-[#03b8ff] underline" target="_blank">Privacy Policy</a>
                and
                <a href="{{ route('terms-of-use') }}" class="text-[#03b8ff] underline" target="_blank">Terms of Use</a>
                to understand how Formigo will process and protect your data.
            </p>
        </div>
        <form method="POST" action="{{ route('google.consent') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $googleUser->email }}">
            <input type="hidden" name="name" value="{{ $googleUser->name }}">
            <input type="hidden" name="given_name" value="{{ $googleUser->user['given_name'] ?? '' }}">
            <input type="hidden" name="family_name" value="{{ $googleUser->user['family_name'] ?? '' }}">
            <input type="hidden" name="avatar" value="{{ $googleUser->avatar }}">
            <div class="flex items-center mb-4 bg-gray-100 rounded-lg px-4 py-3 border border-gray-200">
                <input type="checkbox" id="agree" x-model="agreed" class="mr-2 h-4 w-4 text-[#03b8ff] border-gray-300 rounded focus:ring-[#03b8ff]">
                <label for="agree" class="text-gray-700 text-sm">
                    I agree to the <a href="{{ route('privacy-policy') }}" target="_blank" class="text-[#03b8ff] underline">Privacy Policy</a> and <a href="{{ route('terms-of-use') }}" target="_blank" class="text-[#03b8ff] underline">Terms of Use</a>
                </label>
            </div>
            <button 
                type="submit" 
                class="w-full py-3 rounded-lg bg-[#03b8ff] hover:bg-blue-500 text-white font-bold text-lg mt-2 transition"
                :disabled="!agreed"
                :class="{ 'opacity-50 cursor-not-allowed': !agreed }"
            >
                Continue
            </button>
        </form>
        <div class="mt-6 text-center text-sm text-gray-500">
            To make changes at any time, go to your Google Account.<br>
            <a href="https://support.google.com/accounts/answer/112802?hl=en" target="_blank" class="underline">Learn more about Sign in with Google</a>
        </div>
    </div>
</main>
</x-layouts.app>
