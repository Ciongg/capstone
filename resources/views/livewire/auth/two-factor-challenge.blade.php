<!-- filepath: d:\Projects\capstone\resources\views\livewire\auth\two-factor-challenge.blade.php -->
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Two-Factor Authentication</h2>
            <p class="text-gray-600 mt-2">
                @if($useRecoveryCode)
                    Enter one of your recovery codes
                @else
                    Enter the 6-digit code from your authenticator app
                @endif
            </p>
        </div>

        <form wire:submit.prevent="verify">
            @if(!$useRecoveryCode)
                <div class="mb-6">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                    <input 
                        type="text" 
                        id="code"
                        wire:model="code" 
                        maxlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center text-2xl tracking-widest focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="000000"
                        autofocus
                    >
                    @error('code') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>
            @else
                <div class="mb-6">
                    <label for="recovery" class="block text-sm font-medium text-gray-700 mb-2">Recovery Code</label>
                    <input 
                        type="text" 
                        id="recovery"
                        wire:model="recoveryCode" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter recovery code"
                        autofocus
                    >
                    @error('recoveryCode') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>
            @endif

            <button 
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors"
            >
                Verify
            </button>
        </form>

        <div class="mt-4 text-center">
            <button 
                wire:click="toggleRecoveryMode"
                class="text-sm text-blue-600 hover:text-blue-800"
            >
                @if($useRecoveryCode)
                    Use authenticator app instead
                @else
                    Use recovery code instead
                @endif
            </button>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800">
                ← Back to login
            </a>
        </div>
    </div>

    @script
    <script>
        // Session expired error
        $wire.on('2fa-session-expired', () => {
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. Please login again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Back to Login'
            }).then(() => {
                window.location.href = '{{ route('login') }}';
            });
        });

        // Invalid verification code
        $wire.on('2fa-error-code', () => {
            Swal.fire({
                title: 'Invalid Code',
                text: 'The verification code you entered is incorrect. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Try Again'
            });
        });

        // Invalid recovery code
        $wire.on('2fa-error-recovery', () => {
            Swal.fire({
                title: 'Invalid Recovery Code',
                text: 'The recovery code you entered is incorrect or has already been used.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Try Again'
            });
        });

        // Success message
        $wire.on('2fa-success', () => {
            Swal.fire({
                title: 'Verification Successful!',
                text: 'You have been authenticated successfully.',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Continue',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });

        // Redirect after showing success
        $wire.on('redirect-after-success', () => {
            setTimeout(() => {
                window.location.href = '{{ route('feed.index') }}';
            }, 2000);
        });
    </script>
    @endscript
</div>