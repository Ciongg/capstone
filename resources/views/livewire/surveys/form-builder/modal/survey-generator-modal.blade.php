<div class="p-4 space-y-4" 
    x-data="{ surveyType: @entangle('generationType').defer }">

    <div class="mb-6">
        <div class="flex items-center mb-4">
            <svg class="w-6 h-6 mr-2 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            <h2 class="text-xl font-bold text-gray-800">AI Survey Generator</h2>
        </div>
        <p class="text-sm text-gray-600">
            Describe your survey in detail and our AI will generate questions for you. The more specific your description, the better the results.
        </p>
    </div>

    <form wire:submit.prevent="generateSurvey">
        <!-- Abstract/Description -->
        <div class="mb-4">
            <label for="abstract" class="block font-semibold mb-1 text-gray-700">Survey Description/Abstract</label>
            <textarea
                id="abstract"
                wire:model.defer="abstract"
                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                rows="5"
                placeholder="Describe your survey purpose and the kind of information you want to collect..."
                required
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">Maximum 200 words</p>
            @error('abstract') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Generation Type -->
        <div class="mb-6">
            <label class="block font-semibold mb-2 text-gray-700">Survey Type</label>
            <div class="flex flex-col space-y-2">
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        name="generationType"
                        x-model="surveyType"
                        wire:model.live="generationType"
                        value="normal"
                        class="form-radio text-purple-600 focus:ring-purple-500"
                    />
                    <span class="ml-2">
                        <span class="font-medium">Normal</span>
                        <span class="block text-sm text-gray-500">Standard survey with various question types</span>
                    </span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        name="generationType"
                        x-model="surveyType"
                        wire:model.live="generationType"
                        value="iso"
                        class="form-radio text-purple-600 focus:ring-purple-500"
                    />
                    <span class="ml-2">
                        <span class="font-medium">ISO Format</span>
                        <span class="block text-sm text-gray-500">Structured according to ISO25010 standard for software quality assessment</span>
                    </span>
                </label>
            </div>
            @error('generationType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Normal Survey Options -->
        <div x-show="surveyType === 'normal'" class="mb-6 border-t border-gray-200 pt-4">
            <h3 class="font-medium text-lg mb-3 text-gray-800">Normal Survey Settings</h3>
            <div>
                <label for="maxPages" class="block font-semibold mb-1 text-gray-700">Number of Pages</label>
                <input
                    type="number"
                    id="maxPages"
                    wire:model.defer="maxPages"
                    class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    min="1"
                    max="10"
                    required
                >
                @error('maxPages') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 mt-1">Limit: 1-10 pages</p>
            </div>
            <!-- Removed maxQuestionsPerPage input field -->
        </div>

        <!-- ISO Survey Options -->
        <div x-show="surveyType === 'iso'" class="mb-6 border-t border-gray-200 pt-4">
            <h3 class="font-medium text-lg mb-3 text-gray-800">ISO25010 Settings</h3>
            <!-- ISO Categories -->
            <div class="mb-6">
                <label class="block font-semibold mb-2 text-gray-700">ISO25010 Categories</label>
                <p class="text-sm text-gray-500 mb-3">Select which categories to include in your survey</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($isoCategories as $key => $category)
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="iso-category-{{ $key }}"
                            wire:model.live="selectedIsoCategories.{{ $key }}"
                            class="form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500"
                        >
                        <label for="iso-category-{{ $key }}" class="ml-2 text-gray-700">{{ $category['title'] }}</label>
                    </div>
                    @endforeach
                </div>
                @error('selectedIsoCategories') <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> @enderror
            </div>

            <!-- Likert Scale Points -->
            <div class="mb-6">
                <label class="block font-semibold mb-2 text-gray-700">Likert Scale Points</label>
                <div class="flex flex-col space-y-3">
                    <label class="inline-flex items-center">
                        <input
                            type="radio"
                            name="likertPoints"
                            wire:model.defer="likertPoints"
                            value="3"
                            class="form-radio text-purple-600 focus:ring-purple-500"
                        />
                        <span class="ml-2">
                            3-point (Disagree, Neutral, Agree)
                        </span>
                    </label>
                    <label class="inline-flex items-center">
                        <input
                            type="radio"
                            name="likertPoints"
                            wire:model.defer="likertPoints"
                            value="4"
                            class="form-radio text-purple-600 focus:ring-purple-500"
                        />
                        <span class="ml-2">
                            4-point (Strongly Disagree, Disagree, Agree, Strongly Agree)
                        </span>
                    </label>
                    <label class="inline-flex items-center">
                        <input
                            type="radio"
                            name="likertPoints"
                            wire:model.defer="likertPoints"
                            value="5"
                            class="form-radio text-purple-600 focus:ring-purple-500"
                        />
                        <span class="ml-2">
                            5-point (Strongly Disagree, Disagree, Neutral, Agree, Strongly Agree)
                        </span>
                    </label>
                </div>
                @error('likertPoints') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <!-- Removed maxQuestionsPerPage input for ISO -->
        </div>

        <!-- Warning message -->
        <div class="p-4 mb-6 bg-yellow-50 border border-yellow-300 rounded-md text-yellow-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="font-medium">Warning</h3>
                    <div class="mt-1 text-sm">
                        <p>This will <strong>delete all existing questions and pages</strong> in your survey and replace them with AI-generated content.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button
                type="button"
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md mr-2 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400"
                x-on:click="$dispatch('close-modal', {name: 'survey-generator-modal-{{ $survey->id }}'})"
                wire:loading.attr="disabled"
            >
                Cancel
            </button>
            <button
                type="submit"
                class="px-4 py-2 text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 flex items-center"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="generateSurvey">Generate Survey</span>
                <span wire:loading wire:target="generateSurvey">Generating...</span>
            </button>
        </div>
    </form>
</div>
