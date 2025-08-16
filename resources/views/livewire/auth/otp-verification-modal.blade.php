<div>
    @if($showSuccess)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Verified!',
                    text: 'Your account has been created successfully.',
                    showConfirmButton: true,
                    confirmButtonText: 'Go to Feed',
                    confirmButtonColor: '#3B82F6',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('feed.index') }}';
                    }
                });
            });
        </script>
    @endif

    <x-modal name="otp-verification" title="Email Verification">
        <div class="flex flex-col justify-center items-center min-h-[400px]">
            <div class="w-full max-w-md">
                <div class="text-center">
                    <div class="mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Check Your Email</h3>
                        <p class="text-gray-600 mb-4">
                            We've sent a 6-digit verification code to<br>
                            <span class="font-medium text-gray-900">{{ $pendingEmail }}</span>
                        </p>
                    </div>

                    <form wire:submit.prevent="verifyOtp">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Enter Verification Code</label>
                            <input
                                type="text"
                                wire:model="otp_code"
                                maxlength="6"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="one-time-code"
                                class="w-full text-center text-lg font-semibold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors p-3 @error('otp_code') border-red-500 @enderror"
                                placeholder="Enter 6-digit code"
                                aria-label="Enter verification code"
                            >
                                                        @error('otp_code') 
                                <span class="text-red-600 text-sm">{{ $message }}</span> 
                            @enderror
                            @if(session()->has('success'))
                                <span class="text-green-600 text-sm">{{ session('success') }}</span>
                            @endif
                        </div>

                        <div class="flex flex-col space-y-3">
                            <button 
                                type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="verifyOtp">Verify Email</span>
                                <span wire:loading wire:target="verifyOtp">Verifying...</span>
                            </button>
                            
                                                        <button 
                                type="button" 
                                wire:click="resendOtp"
                                class="text-sm font-medium transition-colors"
                                :class="{ 'text-blue-600 hover:text-blue-700': !$wire.resendCooldown, 'text-gray-400 cursor-not-allowed': $wire.resendCooldown }"
                                :disabled="$wire.resendCooldown"
                                wire:loading.attr="disabled"
                                x-data
                                x-init="
                                    $wire.on('start-resend-cooldown', () => {
                                        const interval = setInterval(() => {
                                            $wire.decrementCooldown();
                                            if (!$wire.resendCooldown) {
                                                clearInterval(interval);
                                            }
                                        }, 1000);
                                    });
                                "
                            >
                                <span wire:loading.remove wire:target="resendOtp">
                                    <span x-show="!$wire.resendCooldown">Resend Code</span>
                                    <span x-show="$wire.resendCooldown" x-text="`Resend in ${$wire.resendCooldownSeconds}s`"></span>
                            </span>
                            <span wire:loading wire:target="resendOtp">Sending...</span>
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-xs text-gray-500">
                    <p>Didn't receive the email? Check your spam folder.</p>
                </div>
            </div>
        </div>
    </div>
</x-modal>
</div>