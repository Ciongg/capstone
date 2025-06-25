<div class="p-4 flex flex-col justify-center items-center h-full">
    @if ($step === 'type')
        <div class="w-full max-w-lg">
            <h2 class="text-2xl font-bold text-center mb-4">What Type of Survey</h2>
            <p class="mb-6 text-gray-600 text-center">Basic surveys may get faster responses than advanced ones, depending on the target and requirements.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6"> {{-- Reduced gap --}}
                <!-- Basic Survey Card - Adjusted sizes -->
                <button 
                    wire:click="selectSurveyType('basic')" 
                    class="flex flex-col min-h-[260px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-[#03b8ff] transition-all" {{-- Reduced min-h --}}
                >
                    <div class="bg-gray-100 p-4 flex items-center justify-center">
                        <div class="bg-gray-200 w-full px-4 py-5 rounded-3xl flex items-center justify-center h-28 shadow-sm"> {{-- Reduced h --}}
                            <svg class="w-12 h-12 text-[#03b8ff]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> {{-- Reduced icon size --}}
                                <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2"/>
                                <line x1="8" y1="11" x2="16" y2="11" stroke="currentColor" stroke-width="2"/>
                                <rect x="8" y1="15" width="3" height="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-4 text-center flex-grow">
                        <h3 class="font-medium text-base mb-2">Basic Survey</h3> {{-- Reduced font size --}}
                        <p class="text-xs text-gray-600">Allow anyone to respond.</p> {{-- Reduced font size --}}
                    </div>
                </button>
                
                <!-- Advanced Survey Card - Adjusted sizes -->
                <button 
                    wire:click="selectSurveyType('advanced')" 
                    class="flex flex-col min-h-[260px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-[#03b8ff] transition-all" {{-- Reduced min-h --}}
                >
                    <div class="bg-gray-100 p-4 flex items-center justify-center">
                        <div class="bg-gray-200 w-full px-4 py-5 rounded-3xl flex items-center justify-center h-28 shadow-sm"> {{-- Reduced h --}}
                            <svg class="w-12 h-12 text-[#03b8ff]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> {{-- Reduced icon size --}}
                                <path d="M13 4H8.8C7.11984 4 6.27976 4 5.63803 4.32698C5.07354 4.6146 4.6146 5.07354 4.32698 5.63803C4 6.27976 4 7.11984 4 8.8V15.2C4 16.8802 4 17.7202 4.32698 18.362C4.6146 18.9265 5.07354 19.3854 5.63803 19.673C6.27976 20 7.11984 20 8.8 20H15.2C16.8802 20 17.7202 20 18.362 19.673C18.9265 19.3854 19.3854 18.9265 19.673 18.362C20 17.7202 20 16.8802 20 15.2V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18 8V4M18 4L16 6M18 4L20 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 11H8M16 15H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-4 text-center flex-grow">
                        <h3 class="font-medium text-base mb-2">Advanced Survey</h3> {{-- Reduced font size --}}
                        <p class="text-xs text-gray-600">Allow only matched respondents to respond.</p> {{-- Reduced font size --}}
                        <div class="flex justify-center mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-[#03b8ff]"> {{-- Reduced icon size --}}
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    @elseif ($step === 'method')
        <div class="w-full max-w-lg">
            <!-- Removed back button from here -->
            
            <h2 class="text-2xl font-bold text-center mb-4">Creating a {{ $surveyType }} survey</h2>
            <p class="mb-6 text-gray-600 text-center">Start collecting data for your {{ $surveyType }} survey with personalized forms or through our customizable templates.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6"> {{-- Reduced gap --}}
                <button 
                    wire:click="selectCreationMethod('scratch')" 
                    class="flex flex-col min-h-[260px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-[#03b8ff] transition-all
                           {{ $creationMethod === 'scratch' ? 'ring-2 ring-offset-2 ring-[#03b8ff]' : '' }}" {{-- Reduced min-h --}}
                >
                    <div class="bg-gray-100 p-4 flex items-center justify-center">
                        <div class="bg-gray-200 w-full px-4 py-5 rounded-3xl flex items-center justify-center h-28 shadow-sm"> {{-- Reduced h --}}
                            <svg class="w-12 h-12 text-[#03b8ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> {{-- Reduced icon size --}}
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-4 text-center flex-grow">
                        <h3 class="font-medium text-base mb-2">Start from Scratch</h3> {{-- Reduced font size --}}
                        <p class="text-xs text-gray-600">Create a blank survey and build it your way</p> {{-- Reduced font size --}}
                    </div>
                </button>
                
                <button 
                    wire:click="selectCreationMethod('template')" 
                    class="flex flex-col min-h-[260px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-[#03b8ff] transition-all
                           {{ $creationMethod === 'template' ? 'ring-2 ring-offset-2 ring-[#03b8ff]' : '' }}" {{-- Reduced min-h --}}
                >
                    <div class="bg-gray-100 p-4 flex items-center justify-center">
                        <div class="bg-gray-200 w-full px-4 py-5 rounded-3xl flex items-center justify-center h-28 shadow-sm"> {{-- Reduced h --}}
                            <svg class="w-12 h-12 text-[#03b8ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> {{-- Reduced icon size --}}
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-4 text-center flex-grow">
                        <h3 class="font-medium text-base mb-2">Use a Template</h3> {{-- Reduced font size --}}
                        <p class="text-xs text-gray-600">Start with a pre-designed survey template</p> {{-- Reduced font size --}}
                    </div>
                </button>
            </div>
            
            <!-- Modified container with both buttons -->
            <div class="flex justify-between items-center mt-6">
                <!-- Back button repositioned here -->
                <button 
                    wire:click="goBack" 
                    class="flex items-center text-gray-600 hover:text-[#03b8ff]"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </button>
                
                <!-- Continue button remains -->
                <button 
                    wire:click="proceedToCreateSurvey"
                    class="px-6 py-2 bg-[#03b8ff] text-white font-medium rounded-lg hover:bg-[#0296d1] disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(!$creationMethod) disabled @endif
                >
                    Continue
                </button>
            </div>
        </div>
    @elseif ($step === 'template')
        <div class="w-full max-w-lg">
            <h2 class="text-2xl font-bold text-center mb-4">Choose a Template</h2>
            <p class="mb-6 text-gray-600 text-center">Select from our pre-designed templates to get started quickly with your {{ $surveyType }} survey.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <!-- ISO 25010 Template Card -->
                <button 
                    wire:click="selectTemplate('iso25010')" 
                    class="flex flex-col min-h-[260px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-[#03b8ff] transition-all
                           {{ $selectedTemplate === 'iso25010' ? 'ring-2 ring-offset-2 ring-[#03b8ff]' : '' }}"
                >
                    <div class="bg-gray-100 p-4 flex items-center justify-center">
                        <div class="bg-gray-200 w-full px-4 py-5 rounded-3xl flex items-center justify-center h-28 shadow-sm">
                            <svg class="w-12 h-12 text-[#03b8ff]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 2v3m0 14v3m10-10h-3M5 12H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-4 text-center flex-grow">
                        <h3 class="font-medium text-base mb-2">ISO 25010 Template</h3>
                        <p class="text-xs text-gray-600">Software quality evaluation based on ISO 25010 standard metrics</p>
                    </div>
                </button>
                
                <!-- Academic Research Template Card -->
                <button 
                    wire:click="selectTemplate('academic')" 
                    class="flex flex-col min-h-[260px] rounded-lg overflow-hidden shadow-xl hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-[#03b8ff] transition-all
                           {{ $selectedTemplate === 'academic' ? 'ring-2 ring-offset-2 ring-[#03b8ff]' : '' }}"
                >
                    <div class="bg-gray-100 p-4 flex items-center justify-center">
                        <div class="bg-gray-200 w-full px-4 py-5 rounded-3xl flex items-center justify-center h-28 shadow-sm">
                            <svg class="w-12 h-12 text-[#03b8ff]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 14l9-5-9-5-9 5 9 5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-4 text-center flex-grow">
                        <h3 class="font-medium text-base mb-2">Academic Research Template</h3>
                        <p class="text-xs text-gray-600">Structured survey template for academic research studies</p>
                    </div>
                </button>
            </div>
            
            <!-- Navigation buttons -->
            <div class="flex justify-between items-center mt-6">
                <button 
                    wire:click="goBackToMethod" 
                    class="flex items-center text-gray-600 hover:text-[#03b8ff]"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </button>
                
                <button 
                    wire:click="proceedToCreateSurvey"
                    class="px-6 py-2 bg-[#03b8ff] text-white font-medium rounded-lg hover:bg-[#0296d1] disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(!$selectedTemplate) disabled @endif
                >
                    Continue
                </button>
            </div>
        </div>
    @endif
</div>
