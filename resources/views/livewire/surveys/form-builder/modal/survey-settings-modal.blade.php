<div x-data="{ tab: 'info' }" class="space-y-4 p-4">


    <!-- Tab Buttons -->
    <div class="flex space-x-2 mb-4">
        <button
            type="button"
            :class="tab === 'info' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded font-semibold focus:outline-none"
            @click="tab = 'info'"
        >
            Survey Information
        </button>
        <button
            type="button"
            :class="tab === 'demographics' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded font-semibold focus:outline-none"
            @click="tab = 'demographics'"
        >
            Survey Demographics
        </button>
    </div>

    <!-- Survey Information Tab -->
    <div x-show="tab === 'info'" x-cloak>
        <form wire:submit.prevent="saveSurveyInformation" class="space-y-4" x-data="{ fileName: '' }">
            <!-- Banner Image Upload -->
            <div>
                <label class="block font-semibold mb-2 text-center">Survey Banner Image</label>
                <div class="flex flex-col items-center justify-center w-full">
                    {{-- Custom styled label acting as the input area --}}
                    <label for="banner-{{ $survey->id }}" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            {{-- Icon --}}
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            {{-- Text --}}
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 2MB)</p>
                            {{-- Display selected file name or "No file chosen" --}}
                            <p x-text="fileName ? fileName : 'No file chosen'" class="text-xs text-gray-600 mt-2"></p>
                        </div>
                        {{-- Hidden actual file input --}}
                        <input id="banner-{{ $survey->id }}"
                               type="file"
                               class="hidden"
                               wire:model.defer="banner_image"
                               accept="image/*"
                               @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" />
                    </label>

                    {{-- Loading Indicator --}}
                    <div wire:loading wire:target="banner_image" class="text-sm text-gray-500 mt-1">Uploading...</div>

                    {{-- Image Preview --}}
                    @if ($banner_image) {{-- Show preview of NEW image --}}
                         <div class="mt-4">
                            <span class="block text-sm font-medium text-gray-700 mb-1">Uploaded Survey Banner Preview:</span>
                            <img src="{{ $banner_image->temporaryUrl() }}" alt="New Banner Preview" class="max-h-40 rounded shadow">
                        </div>
                    @elseif ($survey->image_path) {{-- Show CURRENT saved image if no new one is selected --}}
                        <div class="mt-4">
                             <span class="block text-sm font-medium text-gray-700 mb-1">Current Survey Banner:</span>
                            <img src="{{ asset('storage/' . $survey->image_path) }}" alt="Survey Banner" class="max-h-40 rounded shadow" />
                        </div>
                    @endif
                    @error('banner_image') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Survey Title -->
            <div>
                <label for="survey-title-{{ $survey->id }}" class="block font-semibold mb-1">Survey Title</label>
                <input type="text" id="survey-title-{{ $survey->id }}" wire:model.defer="title" class="w-full border rounded px-3 py-2" />
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Survey Description -->
            <div>
                <label for="survey-description-{{ $survey->id }}" class="block font-semibold mb-1">Survey Description</label>
                <textarea id="survey-description-{{ $survey->id }}" wire:model.defer="description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Survey Type -->
            <div>
                <label for="survey-type-{{ $survey->id }}" class="block font-semibold mb-1">Survey Type</label>
                <select id="survey-type-{{ $survey->id }}" wire:model.defer="type" class="w-full border rounded px-3 py-2">
                    <option value="basic">Basic Survey</option>
                    <option value="advanced">Advanced Survey</option>
                </select>
                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1">Target Respondents</label>
                <input type="number" wire:model.defer="target_respondents" class="w-full border rounded px-3 py-2" min="1" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Start Date</label>
                <input type="date" wire:model.defer="start_date" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1">End Date</label>
                <input type="date" wire:model.defer="end_date" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Points Allocated</label>
                <div class="relative">
                    <input 
                        type="number" 
                        wire:model.defer="points_allocated" 
                        class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                        readonly 
                        disabled
                    />
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="font-medium">Fixed rate:</span> Basic surveys earn 10 points, Advanced surveys earn 30 points.
                    </div>
                </div>
            </div>
            
            {{-- Save Button for Information --}}
            <button type="submit" class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                <span wire:loading.remove wire:target="saveSurveyInformation">Save Information</span>
                <span wire:loading wire:target="saveSurveyInformation">Saving...</span>
            </button>
        </form>
    </div>

    <!-- Survey Demographics Tab -->
    <div<!-- Survey Demographics Tab -->
<div x-show="tab === 'demographics'" x-cloak>
    <form wire:submit.prevent="saveSurveyTags" class="space-y-4">
        @foreach($tagCategories as $category)
            <div wire:key="survey-tag-category-{{ $category->id }}">
                <label class="block font-semibold mb-1">{{ $category->name }}</label>
                <select wire:model.live="selectedSurveyTags.{{ $category->id }}" class="w-full border rounded px-3 py-2">
                    <option value="">Select {{ $category->name }}</option>
                    @foreach($category->tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            <span wire:loading.remove wire:target="saveSurveyTags">Save Demographics</span>
            <span wire:loading wire:target="saveSurveyTags">Saving...</span>
        </button>
    </form>
</div>

</div>
