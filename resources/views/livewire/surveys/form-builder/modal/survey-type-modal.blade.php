<div class="p-4">
    @if ($step === 'type')
        <div>
            <p class="mb-6 text-gray-600">Choose the type of survey you want to create:</p>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <button 
                    wire:click="selectSurveyType('basic')" 
                    class="flex flex-col items-center justify-center p-6 border-2 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all
                           {{ $surveyType === 'basic' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                >
                    <svg class="w-12 h-12 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="font-bold text-lg">Basic Survey</h3>
                    <p class="text-sm text-gray-500 text-center mt-2">Simple forms with standard question types</p>
                </button>
                
                <button 
                    wire:click="selectSurveyType('advanced')" 
                    class="flex flex-col items-center justify-center p-6 border-2 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all
                           {{ $surveyType === 'advanced' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}"
                >
                    <svg class="w-12 h-12 text-purple-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="font-bold text-lg">Advanced Survey</h3>
                    <p class="text-sm text-gray-500 text-center mt-2">Complex forms with logic jumps and advanced features</p>
                </button>
            </div>
        </div>
    @elseif ($step === 'method')
        <div>
            <!-- Back button -->
            <button 
                wire:click="goBack" 
                class="flex items-center text-gray-600 hover:text-blue-500 mb-4"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </button>
            
            <p class="mb-6 text-gray-600">
                Creating a {{ $surveyType }} survey.
                How would you like to start?
            </p>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <button 
                    wire:click="selectCreationMethod('scratch')" 
                    class="flex flex-col items-center justify-center p-6 border-2 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all
                           {{ $creationMethod === 'scratch' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                >
                    <svg class="w-12 h-12 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <h3 class="font-bold text-lg">Start from Scratch</h3>
                    <p class="text-sm text-gray-500 text-center mt-2">Create a blank survey and build it your way</p>
                </button>
                
                <button 
                    wire:click="selectCreationMethod('template')" 
                    class="flex flex-col items-center justify-center p-6 border-2 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all
                           {{ $creationMethod === 'template' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}"
                >
                    <svg class="w-12 h-12 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                    </svg>
                    <h3 class="font-bold text-lg">Use a Template</h3>
                    <p class="text-sm text-gray-500 text-center mt-2">Start with a pre-designed survey template</p>
                </button>
            </div>
            
            <div class="flex justify-end mt-6">
                <button 
                    wire:click="proceedToCreateSurvey"
                    class="px-6 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(!$creationMethod) disabled @endif
                >
                    Continue
                </button>
            </div>
        </div>
    @endif
</div>
