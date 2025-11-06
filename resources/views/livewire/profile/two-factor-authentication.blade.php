<!-- filepath: d:\Projects\capstone\resources\views\livewire\profile\two-factor-authentication.blade.php -->
<div class="w-full md:w-auto">

    <!-- Setup 2FA Modal -->
    <x-modal 
        name="setup-2fa-modal" 
        title="Setup Two-Factor Authentication"
    >
        <div class="p-6 min-h-[400px] flex items-center justify-center" wire:init="@if($isGeneratingQrCode) generateQrCode @endif">
            @if($isGeneratingQrCode)
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg class="animate-spin h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600">Generating your QR code...</p>
                </div>
            @elseif($showQrCode)
                <div class="text-center w-full">
                    <p class="mb-3 text-gray-700 text-sm sm:text-base">Scan this QR code with your authenticator app</p>
                    
                    <div class="flex justify-center mb-4">
                        <img 
                            src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($qrCodeUrl) }}"
                            alt="2FA QR Code"
                            class="w-48 h-48 sm:w-56 sm:h-56"
                        >
                    </div>

                    <!-- Setup Key row with copy button -->
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
    <x-modal name="disable-2fa-modal" title="Disable Two-Factor Authentication" x-on:open-modal.window="if ($event.detail.name === 'disable-2fa-modal') { $wire.call('handleModalOpen') }">
        <div class="p-6 flex flex-col items-center w-full">
            @if($disableStep === 'method')
                <div class="w-full max-w-md text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Verify your identity</h3>
                    <p class="text-gray-600 mb-6">
                        Enter the 6-digit code from your authenticator app to disable Two-Factor Authentication.
                    </p>
                    <form wire:submit.prevent="verifyAuthenticatorForDisable" class="space-y-4">
                        <input
                            type="text"
                            wire:model="disableAuthCode"
                            maxlength="6"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="one-time-code"
                            class="w-full text-center text-lg font-semibold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors p-3"
                            placeholder="Enter 6-digit code"
                            required
                        >
                        <button
                            type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="verifyAuthenticatorForDisable">Verify & disable 2FA</span>
                            <span wire:loading wire:target="verifyAuthenticatorForDisable">Verifying...</span>
                        </button>
                    </form>
                    <button
                        type="button"
                        wire:click="switchToEmailRecovery"
                        class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-700 underline"
                    >
                        Lost your 2FA device?
                    </button>
               
                </div>
            @endif

            @if($disableStep === 'email-intro')
                <div class="w-full max-w-md text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Recover access with email</h3>
                    <p class="text-gray-600 mb-6">
                        We will send a one-time recovery code to <span class="font-medium text-gray-900">{{ $user->email }}</span> to disable Two-Factor Authentication.
                    </p>
                    <button
                        type="button"
                        wire:click="sendDisableOtp"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="sendDisableOtp">Send recovery code</span>
                        <span wire:loading wire:target="sendDisableOtp">Sending...</span>
                    </button>
                    <button
                        type="button"
                        wire:click="switchBackToAuthenticator"
                        class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-700 underline"
                    >
                        Back to authenticator code
                    </button>
                 
                </div>
            @endif

            @if($disableStep === 'otp')
                <div class="w-full max-w-md text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Check your email</h3>
                    <p class="text-gray-600 mb-6">
                        Enter the 6-digit code sent to <span class="font-medium text-gray-900">{{ $user->email }}</span>.
                    </p>
                    <form wire:submit.prevent="verifyDisableOtp" class="space-y-4">
                        <input
                            type="text"
                            wire:model="disableOtpCode"
                            maxlength="6"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="one-time-code"
                            class="w-full text-center text-lg font-semibold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors p-3"
                            placeholder="Enter 6-digit code"
                            required
                        >
                        <button
                            type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="verifyDisableOtp">Verify code & disable 2FA</span>
                            <span wire:loading wire:target="verifyDisableOtp">Verifying...</span>
                        </button>
                    </form>
                    <button
                        type="button"
                        wire:click="resendDisableOtp"
                        class="mt-4 text-sm font-medium transition-colors"
                        x-data
                        x-bind:class="$wire.disableResendCooldown ? 'text-gray-400 cursor-not-allowed' : 'text-blue-600 hover:text-blue-700'"
                        x-bind:disabled="$wire.disableResendCooldown"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="resendDisableOtp">
                            <span x-show="!$wire.disableResendCooldown">Resend code</span>
                            <span x-show="$wire.disableResendCooldown" x-text="`Resend in ${$wire.disableResendCooldownSeconds}s`"></span>
                        </span>
                        <span wire:loading wire:target="resendDisableOtp">Sending...</span>
                    </button>
                    
                    <div class="mt-4 text-xs text-gray-500">
                        <p>Didn't receive the email? Check your spam folder.</p>
                    </div>
                </div>

                    <button
                        type="button"
                        wire:click="switchBackToAuthenticator"
                        class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-700 underline"
                    >
                        Back to authenticator code
                    </button>
            @endif

            @if($disableStep === 'success')
                <div class="w-full max-w-md text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Two-Factor Authentication disabled</h3>
                    <p class="text-gray-600 mb-6">
                        You can re-enable Two-Factor Authentication at any time from this page.
                    </p>
                    <button
                        type="button"
                        wire:click="closeDisableModal"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                    >
                        Close
                    </button>
                </div>
            @endif
        </div>
    </x-modal>

@script
<script>
    // Handle success messages
    window.addEventListener('show-success', function (event) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: event.detail.message || 'Two-factor authentication has been updated successfully.',
            confirmButtonColor: '#10b981',
            confirmButtonText: 'OK',
            showConfirmButton: true,
        });
    });

    // Handle error messages
    window.addEventListener('show-error', function (event) {
        Swal.fire({
            icon: 'info',
            title: 'Verification Code Already Sent',
            text: event.detail.message || 'Something went wrong. Please try again.',
            confirmButtonText: 'OK',
            showConfirmButton: true,
        });
    });

    // Handle OTP sent (for resend)
    window.addEventListener('twofactor-otp-sent', function (event) {
        Swal.fire({
            icon: 'info',
            title: 'Verification Code Sent',
            text: event.detail.message || 'A new verification code has been sent to your email.',
            timer: 2000,
            showConfirmButton: false,
        });
    });
    
     let twoFactorCooldownInterval;
     $wire.on('twofactor-start-resend-cooldown', () => {
         if (twoFactorCooldownInterval) {
             clearInterval(twoFactorCooldownInterval);
        }
        twoFactorCooldownInterval = setInterval(() => {
            $wire.decrementDisableCooldown();
            if (!$wire.disableResendCooldown) {
                clearInterval(twoFactorCooldownInterval);
                twoFactorCooldownInterval = null;
            }
        }, 1000);
    });
</script>
@endscript
</div>