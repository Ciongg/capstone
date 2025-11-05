<!-- filepath: d:\Projects\capstone\resources\views\livewire\profile\two-factor-authentication.blade.php -->
<div>
    <!-- 2FA Toggle Button -->
    <button
        wire:click="toggleTwoFactor"
        class="flex items-center justify-center space-x-2 py-2 px-4 text-white rounded-lg shadow-md transition-colors w-full md:w-40
               {{ $isEnabled ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
        </svg>
        <span class="font-semibold">{{ $isEnabled ? 'Disable 2FA' : 'Enable 2FA' }}</span>
    </button>

    <!-- Setup 2FA Modal -->
    <x-modal name="setup-2fa-modal" title="Setup Two-Factor Authentication">
        <div class="p-6">
            @if($showQrCode)
                <div class="text-center">
                    <p class="mb-4 text-gray-700">Scan this QR code with your authenticator app (Google Authenticator, Microsoft Authenticator, etc.)</p>
                    
                    <div class="flex justify-center mb-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code">
                    </div>

                    <p class="mb-2 text-sm text-gray-600">Or enter this code manually:</p>
                    <p class="mb-6 font-mono text-lg font-bold">{{ $secret }}</p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enter the 6-digit code from your app</label>
                        <input 
                            type="text" 
                            wire:model="verificationCode" 
                            maxlength="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-center text-2xl tracking-widest"
                            placeholder="000000"
                        >
                        @error('verificationCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <button 
                        wire:click="confirmSetup"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg"
                    >
                        Verify and Enable 2FA
                    </button>
                </div>
            @endif

            @if($showRecoveryCodes && count($recoveryCodes) > 0)
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h4 class="font-bold text-yellow-800 mb-2">⚠️ Save Your Recovery Codes</h4>
                    <p class="text-sm text-yellow-700 mb-3">Store these codes in a safe place. You can use them to access your account if you lose your device.</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($recoveryCodes as $code)
                            <code class="bg-white px-2 py-1 rounded text-sm">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-modal>

    <!-- Disable 2FA Modal -->
    <x-modal name="disable-2fa-modal" title="Disable Two-Factor Authentication">
        <div class="p-6">
            <p class="mb-4 text-gray-700">To disable 2FA, please enter your password:</p>
            
            <div class="mb-4">
                <input 
                    type="password" 
                    wire:model="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    placeholder="Enter your password"
                >
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-3">
                <button 
                    wire:click="disableTwoFactor"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg"
                >
                    Disable 2FA
                </button>
                <button 
                    x-on:click="$dispatch('close-modal', {name: 'disable-2fa-modal'})"
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg"
                >
                    Cancel
                </button>
            </div>
        </div>
    </x-modal>

    @script
    <script>
        $wire.on('confirm-disable-2fa', () => {
            Swal.fire({
                title: 'Disable Two-Factor Authentication?',
                text: 'This will make your account less secure. Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, disable it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $dispatch('open-modal', {name: 'disable-2fa-modal'});
                }
            });
        });

        $wire.on('show-success', (event) => {
            Swal.fire({
                title: 'Success!',
                text: event.message || 'Two-factor authentication has been updated successfully.',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'OK'
            });
        });

        $wire.on('show-error', (event) => {
            Swal.fire({
                title: 'Error',
                text: event.message || 'Something went wrong. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        });
    </script>
    @endscript
</div>