<div>
    @if($showSuccess)
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
            <div class="mt-4 flex justify-end">
                <button
                    wire:click="closeModal"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >
                    Close
                </button>
            </div>
        </div>
    @else
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-2">Submit Support Request</h3>
                <p class="text-gray-600 text-sm">
                    Please fill out this form with details about your issue. Our support team will review and respond as soon as possible.
                </p>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <form wire:submit.prevent="submitRequest" class="space-y-4">
                    {{-- Request Type Selection --}}
                    <div>
                        <label for="request_type" class="block text-sm font-medium text-gray-700 mb-1">Request Type</label>
                        <select 
                            id="request_type" 
                            wire:model.live="request_type"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <option value="">Select a request type</option>
                            <option value="survey_lock_appeal">Survey Lock Appeal</option>
                            <option value="report_appeal">Report Appeal</option>
                            <option value="account_issue">Account Issue</option>
                            <option value="survey_question">Survey Question</option>
                            <option value="other">Other</option>
                        </select>
                        @error('request_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Subject Field --}}
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input
                            type="text"
                            id="subject"
                            wire:model="subject"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="Brief title of your request"
                        >
                        @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Description Field --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="5"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="Please provide detailed information about your issue..."
                        ></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Related ID field can be shown conditionally if needed --}}
                    @if($request_type == 'survey_lock_appeal' || $request_type == 'report_appeal')
                    <div>
                        <label for="related_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $request_type == 'survey_lock_appeal' ? 'Survey ID' : 'Report ID' }} (optional)
                        </label>
                        <input
                            type="text"
                            id="related_id"
                            wire:model="related_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="{{ $request_type == 'survey_lock_appeal' ? 'Enter survey ID if known' : 'Enter report ID if known' }}"
                        >
                    </div>
                    @endif
                    
                    <div class="pt-4">
                        <button
                            type="submit"
                            class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
