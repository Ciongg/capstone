<div class="p-2 sm:p-4">
    @if($showSuccess)
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-sm mb-4" role="alert">
            <p class="font-medium text-lg mb-2">Success!</p>
            <p>{{ $message }}</p>
            <div class="mt-6 flex justify-end">
                <button
                    wire:click="closeModal"
                    class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors shadow-sm"
                >
                    Close
                </button>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Submit Support Request</h3>
                <p class="text-gray-600">
                    Please fill out this form with details about your issue. Our support team will review and respond as soon as possible.
                </p>
            </div>
            
            <div class="p-6">
                <form wire:submit.prevent="submitRequest" class="space-y-6">
                    {{-- Request Type Selection --}}
                    <div class="form-group">
                        <label for="request_type" class="block text-sm font-medium text-gray-700 mb-2">Request Type</label>
                        <select 
                            id="request_type" 
                            wire:model.live="request_type"
                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                        >
                            <option value="">Select a request type</option>
                            <option value="survey_lock_appeal">Survey Lock Appeal</option>
                            <option value="report_appeal">Report Appeal</option>
                            <option value="account_issue">Account Issue</option>
                            <option value="survey_question">Survey Question</option>
                            <option value="other">Other</option>
                        </select>
                        @error('request_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Subject Field --}}
                    <div class="form-group">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input
                            type="text"
                            id="subject"
                            wire:model="subject"
                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                            placeholder="Brief title of your request"
                        >
                        @error('subject') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Description Field --}}
                    <div class="form-group">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="6"
                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                            placeholder="Please provide detailed information about your issue..."
                        ></textarea>
                        @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Related ID field can be shown conditionally if needed --}}
                    @if($request_type == 'survey_lock_appeal' || $request_type == 'report_appeal')
                    <div class="form-group mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <label for="related_id" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $request_type == 'survey_lock_appeal' ? 'Survey ID' : 'Report ID' }} (optional)
                        </label>
                        <input
                            type="text"
                            id="related_id"
                            wire:model="related_id"
                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 bg-white focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                            placeholder="{{ $request_type == 'survey_lock_appeal' ? 'Enter survey ID if known' : 'Enter report ID if known' }}"
                        >
                        <p class="mt-2 text-xs text-blue-700">Providing the ID will help us process your request faster.</p>
                    </div>
                    @endif
                </form>
            </div>

            <div class="p-6 bg-gray-50 rounded-b-xl border-t border-gray-100">
                <div class="flex items-center justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 shadow-sm hover:bg-gray-50 mr-3"
                        wire:click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="submitRequest"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Submit Request
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
