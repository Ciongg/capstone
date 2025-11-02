<div>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Contact User</h3>
            <p class="text-gray-600 text-sm">
                Send a direct message to the selected user. This will create an inbox message for the recipient.
            </p>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <form wire:submit.prevent="sendMessage" class="space-y-4">
                <!-- Recipient context (only when opened from a selected user) -->
                @if(isset($userId) && $userId)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipient</label>
                    <input
                        type="text"
                        value="{{ isset($recipientLabel) && $recipientLabel ? $recipientLabel : ('User ID: ' . ($userId ?? 'N/A')) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 bg-gray-50"
                        disabled
                    >
                    <p class="text-xs text-gray-500 mt-1">You may override the recipient by email below if needed.</p>
                </div>
                @endif

                <!-- Recipient Email (required if no selected user) -->
                <div>
                    <label for="recipient_email" class="block text-sm font-medium text-gray-700 mb-1">
                        Recipient Email {{ (isset($userId) && $userId) ? '(Optional)' : '(Required)' }}
                    </label>
                    <input
                        type="email"
                        id="recipient_email"
                        wire:model="recipient_email"
                        @if(empty($userId)) required @endif
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="user@example.com"
                    >
                    @error('recipient_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if(empty($userId))
                        <p class="text-xs text-gray-500 mt-1">Enter the recipient’s email. This is required when no user is preselected.</p>
                    @endif
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input
                        type="text"
                        id="subject"
                        wire:model="subject"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Subject of your message"
                    >
                    @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Message Body -->
                <div>
                    <label for="messageBody" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea
                        id="messageBody"
                        wire:model="messageBody"
                        rows="6"
                        maxlength="4000"
                        class="w-full border-gray-300 rounded-md shadow-sm px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        placeholder="Write your message here..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Max 4000 characters.</p>
                    @error('messageBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2 flex justify-end">
                    <button
                        type="button"
                        x-data
                        x-on:click="Swal.fire({
                            title: 'Send message?',
                            text: 'This will create an inbox message for the recipient.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Yes, send it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.sendMessage();
                            }
                        })"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-[#03b8ff] hover:bg-[#0299d5] text-white font-bold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#03b8ff]"
                    >
                        <span wire:loading.inline wire:target="sendMessage">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                        <span wire:loading.remove wire:target="sendMessage">
                            Send Message
                        </span>
                    </button>
                </div>
            </form>
        </div>

        @if(session()->has('modal_message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4" role="alert">
                <p>{{ session('modal_message') }}</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
