<div class="p-4 flex flex-col justify-center items-center h-full">
    @if ($step === 'type')
        <div class="w-full max-w-lg">
            <h2 class="text-2xl font-bold text-center mb-4">What Type of Survey</h2>
            <p class="mb-6 text-gray-600 text-center">Basic surveys may get faster responses than advanced ones, depending on the target and requirements.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-6">
                <!-- Basic Survey Card -->
                <button 
                    wire:click="selectSurveyType('basic')" 
                    class="flex flex-col min-h-[300px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 transition-all
                           {{ $surveyType === 'basic' ? 'ring-2 ring-offset-2 ring-blue-500' : '' }}"
                >
                    <div class="bg-[#d9f0ff] p-4 flex items-center justify-center">
                        <div class="bg-[#a7c7d9] w-full px-4 py-5 rounded-3xl flex items-center justify-center h-36 shadow-sm">
                            <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2"/>
                                <line x1="8" y1="11" x2="16" y2="11" stroke="currentColor" stroke-width="2"/>
                                <rect x="8" y1="15" width="3" height="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-[#d9f0ff] p-4 text-center flex-grow">
                        <h3 class="font-medium text-lg mb-2">Basic Survey</h3>
                        <p class="text-sm text-gray-600">Allow anyone to respond.</p>
                    </div>
                </button>
                
                <!-- Advanced Survey Card -->
                <button 
                    wire:click="selectSurveyType('advanced')" 
                    class="flex flex-col min-h-[300px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 transition-all
                           {{ $surveyType === 'advanced' ? 'ring-2 ring-offset-2 ring-blue-500' : '' }}"
                >
                    <div class="bg-[#d9f0ff] p-4 flex items-center justify-center">
                        <div class="bg-[#a7c7d9] w-full px-4 py-5 rounded-3xl flex items-center justify-center h-36 shadow-sm">
                            <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13 4H8.8C7.11984 4 6.27976 4 5.63803 4.32698C5.07354 4.6146 4.6146 5.07354 4.32698 5.63803C4 6.27976 4 7.11984 4 8.8V15.2C4 16.8802 4 17.7202 4.32698 18.362C4.6146 18.9265 5.07354 19.3854 5.63803 19.673C6.27976 20 7.11984 20 8.8 20H15.2C16.8802 20 17.7202 20 18.362 19.673C18.9265 19.3854 19.3854 18.9265 19.673 18.362C20 17.7202 20 16.8802 20 15.2V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18 8V4M18 4L16 6M18 4L20 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 11H8M16 15H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-[#d9f0ff] p-4 text-center flex-grow">
                        <h3 class="font-medium text-lg mb-2">Advanced Survey</h3>
                        <p class="text-sm text-gray-600">Allow only matched respondents to respond.</p>
                        <div class="flex justify-center mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-orange-400">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    @elseif ($step === 'method')
        <div class="w-full max-w-lg">
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
            
            <h2 class="text-2xl font-bold text-center mb-4">Create a Form</h2>
            <p class="mb-6 text-gray-600 text-center">Start collecting data for your {{ $surveyType }} survey with personalized forms or through our customizable templates.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-6">
                <button 
                    wire:click="selectCreationMethod('scratch')" 
                    class="flex flex-col min-h-[300px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 transition-all
                           {{ $creationMethod === 'scratch' ? 'ring-2 ring-offset-2 ring-blue-500' : '' }}"
                >
                    <div class="bg-[#d9f0ff] p-4 flex items-center justify-center">
                        <div class="bg-[#a7c7d9] w-full px-4 py-5 rounded-3xl flex items-center justify-center h-36 shadow-sm">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-[#d9f0ff] p-4 text-center flex-grow">
                        <h3 class="font-medium text-lg mb-2">Start from Scratch</h3>
                        <p class="text-sm text-gray-600">Create a blank survey and build it your way</p>
                    </div>
                </button>
                
                <button 
                    wire:click="selectCreationMethod('template')" 
                    class="flex flex-col min-h-[300px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 transition-all
                           {{ $creationMethod === 'template' ? 'ring-2 ring-offset-2 ring-blue-500' : '' }}"
                >
                    <div class="bg-[#d9f0ff] p-4 flex items-center justify-center">
                        <div class="bg-[#a7c7d9] w-full px-4 py-5 rounded-3xl flex items-center justify-center h-36 shadow-sm">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-[#d9f0ff] p-4 text-center flex-grow">
                        <h3 class="font-medium text-lg mb-2">Use a Template</h3>
                        <p class="text-sm text-gray-600">Start with a pre-designed survey template</p>
                    </div>
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
