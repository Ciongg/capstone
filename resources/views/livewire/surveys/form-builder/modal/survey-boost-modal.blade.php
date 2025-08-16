<div class="space-y-4">
    <div class="flex items-center justify-center mb-6">
        <div class="text-center">
            <div class="flex justify-center mb-3">
                <div class="bg-blue-200 p-3 rounded-full">
                    <img src="{{ asset('images/icons/rocket.svg') }}" alt="Rocket" class="w-8 h-8">
                </div>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Survey Boost Allocation</h2>
            <p class="text-sm text-gray-600">Use your survey boosts to increase visibility and attract more respondents</p>
        </div>
    </div>

    <!-- Available Survey Boosts -->
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-blue-200 px-2 py-2 rounded-full mr-3">
                    <img src="{{ asset('images/icons/rocket.svg') }}" alt="Rocket" class="w-4 h-4">
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Available Survey Boosts</span>
                    <p class="text-xs text-gray-500">Boost your survey's visibility</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-2xl font-bold text-orange-600">{{ $availableBoosts }}</span>
                <p class="text-xs text-gray-500">boosts</p>
            </div>
        </div>
    </div>

    <!-- Allocation Form -->
    <form wire:submit.prevent="allocateBoosts" class="space-y-4">
        <!-- Quantity Input -->
        <div>
            <label for="boost-quantity" class="block text-sm font-medium text-gray-700 mb-2">
                Number of boosts to allocate
            </label>
            <div>
                <input 
                    type="number" 
                    id="boost-quantity"
                    wire:model.defer="boostQuantity"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    min="1"
                    max="{{ min($availableBoosts, 4 - ($survey->boost_count ?? 0)) }}"
                    placeholder="Enter quantity"
                    @if(($survey->boost_count ?? 0) >= 4) disabled @endif
                />
                <div class="mt-1 text-right">
                    <span class="text-gray-500 text-sm">{{ $availableBoosts }} boosts available</span>
                </div>
            </div>
            @error('boostQuantity') 
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Boost Effect Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-800">Survey Boost Benefits:</p>
                    <ul class="text-xs text-blue-700 mt-1 space-y-1">
                        <li>• Each boost adds +5 points to your survey</li>
                        <li>• Increased visibility in survey listings</li>
                        <li>• Priority placement in search results</li>
                        <li>• Higher chance of attracting quality respondents</li>
                        <li>• Maximum 4 boosts per survey allowed</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Survey Boost Status -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Survey Boost Status:</span>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">{{ $survey->boost_count ?? 0 }} / 4 boosts applied</span>
                    <div class="flex space-x-1">
                        @for($i = 1; $i <= 4; $i++)
                            <div class="w-3 h-3 rounded-full {{ ($survey->boost_count ?? 0) >= $i ? 'bg-orange-400' : 'bg-gray-300' }}"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button 
                type="button"
                x-on:click="$dispatch('close-modal', {name: 'survey-boost-modal-{{ $survey->id }}'})"
                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
            >
                Cancel
            </button>
            <button 
                type="submit"
                class="flex-1 px-4 py-2 bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 text-white font-medium rounded-md hover:from-red-700 hover:via-orange-500 hover:to-yellow-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                @if(($survey->boost_count ?? 0) >= 4 || $availableBoosts <= 0) disabled @endif
            >
                <span wire:loading.remove wire:target="allocateBoosts">
                    @if(($survey->boost_count ?? 0) >= 4)
                        Max Boosts Reached
                    @elseif($availableBoosts <= 0)
                        No Boosts Available
                    @else
                        Allocate Boosts (+{{ (int)$boostQuantity * 5 }} points)
                    @endif
                </span>
                <span wire:loading wire:target="allocateBoosts">Allocating...</span>
            </button>
        </div>
    </form>

    <!-- Current Survey Boosts Applied -->
    @if($currentSurveyBoosts > 0)
        <div class="mt-6 p-3 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-green-800">
                    This survey has {{ $currentSurveyBoosts }} boost{{ $currentSurveyBoosts > 1 ? 's' : '' }} applied 
                    (+{{ $currentSurveyBoosts * 5 }} bonus points)
                </span>
            </div>
        </div>
    @endif
</div>
