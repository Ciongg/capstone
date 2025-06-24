<div x-data="{ tab: 'info' }" class="space-y-4 p-4">

    <!-- Tab Buttons - Modified for mobile responsiveness -->
    <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 mb-4">
        <button
            type="button"
            :class="tab === 'info' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded font-semibold focus:outline-none text-sm sm:text-base w-full sm:w-auto"
            @click="tab = 'info'"
        >
            Survey Information
        </button>
        <button
            type="button"
            :class="{
                'bg-blue-500 text-white': tab === 'demographics',
                'bg-gray-200 text-gray-700': tab !== 'demographics',
                'opacity-50 cursor-not-allowed': isInstitutionOnly
            }"
            class="px-4 py-2 rounded font-semibold focus:outline-none text-sm sm:text-base w-full sm:w-auto"
            @click="!isInstitutionOnly && (tab = 'demographics')"
            x-bind:disabled="isInstitutionOnly"
            x-data="{ isInstitutionOnly: @js($isInstitutionOnly) }"
            title="Standard demographics are only available for public surveys"
        >
            Survey Demographics
        </button>
        <button
            type="button"
            :class="{
                'bg-blue-500 text-white': tab === 'institution_demographics',
                'bg-gray-200 text-gray-700': tab !== 'institution_demographics',
                'opacity-50 cursor-not-allowed': !isInstitutionOnly
            }"
            class="px-4 py-2 rounded font-semibold focus:outline-none text-sm sm:text-base w-full sm:w-auto"
            @click="isInstitutionOnly && (tab = 'institution_demographics')"
            x-bind:disabled="!isInstitutionOnly"
            x-data="{ isInstitutionOnly: @js($isInstitutionOnly) }"
            title="Institution demographics are only available for institution-only surveys"
        >
            Institution Demographics
        </button>
    </div>

    <!-- Survey Information Tab -->
    <div x-show="tab === 'info'" x-cloak>
        <form wire:submit.prevent="saveSurveyInformation" class="space-y-4" x-data="{ fileName: '' }">
            
            <!-- Institution-Only Checkbox -->
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg w-full">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="institution-only-{{ $survey->id }}" 
                        wire:model.defer="isInstitutionOnly"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                    >
                    <label for="institution-only-{{ $survey->id }}" class="ml-2 text-sm font-medium text-gray-900">
                        Make this survey institution-only
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-600">
                    Institution-only surveys are only visible to members of your institution.
                </p>
            </div>

            <!-- Points Allocated Section - Reorganized for mobile -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex items-center"> {{-- Changed: ensure label and badge are always in a row --}}
                    <span class="block font-semibold mr-3">Points Allocated:</span> {{-- Changed: adjusted margin --}}
                    <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full self-start sm:self-auto">
                        <span class="font-bold text-white drop-shadow">{{ $points_allocated ?? 0 }}</span>
                        <svg class="w-5 h-5 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </svg>
                    </div>
                </div>
                <button 
                    type="button" 
                    x-on:click="$dispatch('open-modal', {name : 'survey-boost-modal-{{ $survey->id }}'})"
                    class="px-4 py-2 bg-[#03b8ff] hover:bg-[#0295d1] text-white font-medium rounded transition duration-200 text-sm w-full sm:w-auto"
                >
                    Allocate Points
                </button>
            </div>
            
            <div class="text-xs text-gray-500 mb-4">
                <span class="font-medium">Fixed rate:</span> Basic surveys earn 10 points, Advanced surveys earn 20 points.
            </div>

            <!-- Hidden field to store points_allocated value -->
            <input type="hidden" wire:model.defer="points_allocated">

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

            <!-- Survey Topic -->
            <div>
                <label for="survey-topic-{{ $survey->id }}" class="block font-semibold mb-1">Survey Topic</label>
                <select id="survey-topic-{{ $survey->id }}" wire:model.defer="survey_topic_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select a Topic</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                    @endforeach
                </select>
                @error('survey_topic_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1">Target Respondents</label>
                <input type="number" wire:model.defer="target_respondents" class="w-full border rounded px-3 py-2" min="1" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Start Date</label>
                <input type="datetime-local" wire:model.defer="start_date" class="w-full border rounded px-3 py-2" />
                @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block font-semibold mb-1">End Date</label>
                <input type="datetime-local" wire:model.defer="end_date" class="w-full border rounded px-3 py-2" />
                @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            {{-- Save Button for Information --}}
            <button type="submit" class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                <span wire:loading.remove wire:target="saveSurveyInformation">Save Information</span>
                <span wire:loading wire:target="saveSurveyInformation">Saving...</span>
            </button>
        </form>
    </div>

    <!-- Survey Demographics Tab -->
    <div x-show="tab === 'demographics'" x-cloak>
        <form wire:submit.prevent="saveSurveyTags" class="space-y-6">
            @foreach($tagCategories as $category)
                <div wire:key="survey-tag-category-{{ $category->id }}" class="border rounded-lg p-4 bg-gray-50">
                    <label class="block font-semibold mb-3 text-lg">{{ $category->name }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($category->tags as $tag)
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="survey-tag-{{ $tag->id }}" 
                                    value="{{ $tag->id }}" 
                                    wire:model.live="selectedSurveyTags.{{ $category->id }}" 
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                >
                                <label for="survey-tag-{{ $tag->id }}" class="ml-2 text-sm font-medium text-gray-700">
                                    {{ $tag->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <button type="submit" class="mt-6 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 font-medium">
                <span wire:loading.remove wire:target="saveSurveyTags">Save Demographics</span>
                <span wire:loading wire:target="saveSurveyTags">Saving...</span>
            </button>
        </form>
    </div>

    <!-- Institution Demographics Tab -->
    <div x-show="tab === 'institution_demographics'" x-cloak>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Institution Demographics</h3>
            <p class="text-sm text-gray-500">Select the institutional demographic tags to target specific groups within your institution.</p>
        </div>
        
        <form wire:submit.prevent="saveInstitutionTags" class="space-y-6">
            @if(empty($institutionTagCategories) || count($institutionTagCategories) == 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            @if(Auth::user()->isInstitutionAdmin())
                                <p class="text-sm text-yellow-700">
                                    No institutional demographics have been set up yet. 
                                    <a href="{{ route('profile.index') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                        Set up institution demographics
                                    </a>
                                </p>
                            @elseif(Auth::user()->isResearcher())
                                <p class="text-sm text-yellow-700">
                                    No institutional demographics have been set up by your organization. 
                                    <span class="font-medium underline text-yellow-700">
                                        Notify them if this is wrong
                                    </span>
                                </p>
                            @else 
                                <p class="text-sm text-yellow-700">
                                    Institutional demographics are not yet available.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                @foreach($institutionTagCategories as $category)
                    <div wire:key="institution-tag-category-{{ $category->id }}" class="border rounded-lg p-4 bg-gray-50">
                        <label class="block font-semibold mb-3 text-lg">{{ $category->name }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($category->tags as $tag)
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        id="institution-tag-{{ $tag->id }}" 
                                        value="{{ $tag->id }}" 
                                        wire:model.live="selectedInstitutionTags.{{ $category->id }}" 
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                    <label for="institution-tag-{{ $tag->id }}" class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $tag->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                
                <button type="submit" class="mt-6 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 font-medium">
                    <span wire:loading.remove wire:target="saveInstitutionTags">Save Institution Demographics</span>
                    <span wire:loading wire:target="saveInstitutionTags">Saving...</span>
                </button>
            @endif
        </form>
    </div>

    <!-- Institution-Only Checkbox handler - Add this script to update tab behavior -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('updated', (event) => {
                if (typeof event.isInstitutionOnly !== 'undefined') {
                    // Update the isInstitutionOnly property in all Alpine.js components that use it
                    Alpine.store('isInstitutionOnly', event.isInstitutionOnly);
                    
                    // If tabs are now disabled, switch back to info tab
                    if (event.isInstitutionOnly && Alpine.$data(document.querySelector('[x-data*="tab"]')).tab === 'demographics') {
                        Alpine.$data(document.querySelector('[x-data*="tab"]')).tab = 'info';
                    } 
                    else if (!event.isInstitutionOnly && Alpine.$data(document.querySelector('[x-data*="tab"]')).tab === 'institution_demographics') {
                        Alpine.$data(document.querySelector('[x-data*="tab"]')).tab = 'info';
                    }
                }
            });
        });
    </script>

    <!-- Survey Boost Modal -->
    <x-modal name="survey-boost-modal-{{ $survey->id }}" title="Survey Boost Allocation">
        @livewire('surveys.form-builder.modal.survey-boost-modal', ['survey' => $survey], key('survey-boost-modal-' . $survey->id))
    </x-modal>
</div>
