<div>
    @if($showSuccess)
        <div class="{{ $isError ? 'bg-red-100 border-l-4 border-red-500 text-red-700' : 'bg-green-100 border-l-4 border-green-500 text-green-700' }} p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
            <div class="mt-4 flex justify-end">
                <button
                    wire:click="closeModal"
                    class="{{ $isError ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} px-4 py-2 text-white rounded"
                >
                    Close
                </button>
            </div>
        </div>
    @elseif($showConfirmation)
        {{-- Confirmation Screen --}}
        <div class="space-y-6">
            {{-- Warning Symbol --}}
            <div class="flex justify-center">
                <div class="bg-red-100 p-4 rounded-full">
                    <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            
            <div class="text-center">
                <h3 class="text-xl font-bold text-red-600">Confirm Report Submission</h3>
                <p class="text-gray-600 mt-2">Please review the report details before final submission</p>
            </div>
            
            {{-- Report Details --}}
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-3">Report Information:</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex">
                        <span class="font-medium w-32">Respondent:</span>
                        <span>{{ $response->user_id ?? 'ID ' . $response->id }}</span>
                    </div>
                    @if($questionId && $selectedQuestionText)
                    <div class="flex">
                        <span class="font-medium w-32">Question:</span>
                        <span>{{ $selectedQuestionText }}</span>
                    </div>
                    @endif
                    <div class="flex">
                        <span class="font-medium w-32">Reason:</span>
                        <span>{{ $reportReasons[$reason] }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-medium mb-1">Details:</span>
                        <div class="bg-white p-2 border rounded text-gray-700 whitespace-normal break-words max-h-40 overflow-y-auto">{{ $details }}</div>
                    </div>
                </div>
            </div>
            
            {{-- Warning Message --}}
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 text-sm">
                <p class="font-semibold text-yellow-700">Important:</p>
                <ul class="list-disc ml-5 mt-1 space-y-1 text-yellow-700">
                    <li>This will <span class="text-red-600 font-semibold">flag</span> the response in your results.</li>
                    <li>Reported users can appeal this decision.</li>
                    <li>False reports may result in penalties to your account.</li>
                </ul>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex justify-between pt-4">
                <button
                    wire:click="cancelConfirmation"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Go Back
                </button>
                <button
                    wire:click="confirmReport"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700"
                >
                    Confirm Report
                </button>
            </div>
        </div>
    @else
        {{-- Initial Report Form --}}
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-2">Report Response</h3>
                <p class="text-gray-600 text-sm">
                    Use this form to report inappropriate or suspicious responses. 
                    Our team will review your report and take appropriate action.
                </p>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <form wire:submit.prevent="submitReport" class="space-y-4">
                    {{-- Question Selection Field (New) --}}
                    <div>
                        <label for="questionId" class="block text-sm font-medium text-gray-700 mb-1">Question (Optional)</label>
                        <select 
                            id="questionId" 
                            wire:model="questionId"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <option value="">Select a specific question (optional)</option>
                            @foreach($questions as $question)
                                <option value="{{ $question['id'] }}">{{ $question['display'] }}</option>
                            @endforeach
                        </select>
                        @error('questionId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Reason Selection Field --}}
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Report</label>
                        <select 
                            id="reason" 
                            wire:model="reason"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <option value="">Select a reason</option>
                            @foreach($reportReasons as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Details Field --}}
                    <div>
                        <label for="details" class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                        <textarea
                            id="details"
                            wire:model="details"
                            rows="5"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                            placeholder="Please provide specific details about why you're reporting this response..."
                        ></textarea>
                        @error('details') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="pt-4 flex items-center justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
