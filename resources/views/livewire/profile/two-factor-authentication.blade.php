<!-- filepath: d:\Projects\capstone\resources\views\livewire\profile\two-factor-authentication.blade.php -->
<div class="w-full md:w-auto">
    <div class="p-6 flex flex-col items-center">
        <div class="text-center mb-6 max-w-md">
            <p class="text-gray-700 mb-4">
                Two-Factor Authentication adds an extra layer of security to your account.
            </p>
            <p class="text-sm text-gray-600">
                Status: <span class="font-semibold {{ $isEnabled ? 'text-green-600' : 'text-gray-600' }}">
                    {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                </span>
            </p>
        </div>

        <!-- 2FA Toggle Button -->
        <button
            wire:click="toggleTwoFactor"
            class="flex items-center justify-center space-x-2 py-2.5 px-4 text-white rounded-lg shadow-md transition-colors w-full max-w-xs
                   {{ $isEnabled ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <span class="font-semibold">{{ $isEnabled ? 'Disable 2FA' : 'Enable 2FA' }}</span>
        </button>
    </div>

    <!-- Setup 2FA Modal -->
    <x-modal name="setup-2fa-modal" title="Setup Two-Factor Authentication">
        <div class="p-6">
            @if($showQrCode)
                <div class="text-center">
                    <p class="mb-3 text-gray-700 text-sm sm:text-base">Scan this QR code with your authenticator app</p>
                    
                    <div class="flex justify-center mb-4">
                        <img 
                            src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($qrCodeUrl) }}"
                            alt="2FA QR Code"
                            class="w-48 h-48 sm:w-56 sm:h-56"
                        >
                    </div>

                    <!-- Setup Key row with copy button (same pattern as view-all-responses modal) -->
                    <div class="mb-5">
                        <div class="flex items-start sm:items-center gap-2 sm:gap-3 justify-center">
                            <div class="text-xs text-gray-600 mt-1 sm:mt-0">Setup Key</div>
                            <div class="flex items-center gap-2 max-w-full">
                                <div class="font-mono text-xl sm:text-2xl font-semibold text-gray-800 break-all select-all">
                                    {{ $secret }}
                                </div>
                                <button 
                                    class="shrink-0 inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition"
                                    x-data="{ copied: false }"
                                    x-on:click="
                                        navigator.clipboard.writeText('{{ $secret }}')
                                            .then(() => { copied = true; setTimeout(() => copied = false, 1200); })
                                            .catch(() => {
                                                // If clipboard API fails, still show error (optional)
                                                Swal.fire({toast:true,position:'top-end',timer:1800,showConfirmButton:false,icon:'error',title:'Copy failed'});
                                            });
                                    "
                                    title="Copy to clipboard"
                                    type="button"
                                    aria-label="Copy setup key"
                                >
                                    <template x-if="!copied">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25" />
                                        </svg>
                                    </template>
                                    <template x-if="copied">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round
                                            " stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enter the 6-digit code from your app</label>
                        <input 
                            type="text" 
                            wire:model="verificationCode" 
                            maxlength="6"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-center text-2xl tracking-widest"
                            placeholder="000000"
                        >
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
                    <h4 class="font-bold text-yellow-800 mb-2">Save Your Recovery Codes</h4>
                    <p class="text-sm text-yellow-700 mb-3">
                        Store these codes in a secure place. You will need them if you lose access to your authenticator app. These codes wont be accessible from this system again.
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($recoveryCodes as $code)
                            <code class="bg-white px-2 py-1 rounded text-sm">{{ $code }}</code>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col sm:flex-row gap-3"
                         x-data="{ copied: false }">
                        <button
                            type="button"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg"
                            x-on:click="
                                navigator.clipboard.writeText(`{{ implode('\n', $recoveryCodes) }}`)
                                    .then(() => { copied = true; setTimeout(() => copied = false, 1400); })
                            ">
                            <span x-show="!copied">Copy all codes</span>
                            <span x-show="copied" class="text-green-700">Copied!</span>
                        </button>
                        <button
                            type="button"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg"
                            x-on:click="$dispatch('close-modal', {name: 'setup-2fa-modal'})">
                            I’ve stored them safely
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </x-modal>

    <!-- Disable 2FA Modal -->
    <x-modal name="disable-2fa-modal" title="Disable Two-Factor Authentication">
        <div class="p-6 flex flex-col items-center">
            <p class="mb-4 text-gray-700 text-center max-w-md">To disable 2FA, please enter your password:</p>
            
            <div class="mb-4 w-full max-w-md">
                <input 
                    type="password" 
                    wire:model="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    placeholder="Enter your password"
                >
            </div>

            <div class="flex gap-3 w-full max-w-md">
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
			text: (event && event.message) ? event.message : 'Two-factor authentication has been updated successfully.',
			icon: 'success',
			confirmButtonColor: '#10b981',
			confirmButtonText: 'OK'
		});
	});

	$wire.on('show-error', (event) => {
		Swal.fire({
			title: 'Error',
			text: (event && event.message) ? event.message : 'Something went wrong. Please try again.',
			icon: 'error',
			confirmButtonColor: '#ef4444',
			confirmButtonText: 'OK'
		});
	});
</script>
@endscript
</div>