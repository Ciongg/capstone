<div x-data="{ tab: 'info', isDisabled: @js(in_array($survey->status, ['ongoing', 'finished'])) }" class="space-y-4 p-4">

    <!-- Tab Buttons -->
    <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 mb-4 w-full">
        <button
            type="button"
            :class="tab === 'info' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded font-semibold focus:outline-none w-full sm:w-auto"
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
            class="px-4 py-2 rounded font-semibold focus:outline-none w-full sm:w-auto"
            @click="!isInstitutionOnly && (tab = 'demographics')"
            x-bind:disabled="isInstitutionOnly"
            x-data="{ isInstitutionOnly: @js($isInstitutionOnly) }"
            title="Standard demographics are only available for public surveys"
        >
        Survey Demographics
    </button>
    @if(!auth()->user()->isSuperAdmin())
        <button
            type="button"
            :class="{
                'bg-blue-500 text-white': tab === 'institution_demographics',
                'bg-gray-200 text-gray-700': tab !== 'institution_demographics',
                'opacity-50 cursor-not-allowed': !isInstitutionOnly
            }"
            class="px-4 py-2 rounded font-semibold focus:outline-none w-full sm:w-auto"
            @click="isInstitutionOnly && (tab = 'institution_demographics')"
            x-bind:disabled="!isInstitutionOnly"
            x-data="{ isInstitutionOnly: @js($isInstitutionOnly) }"
            title="Institution demographics are only available for institution-only surveys"
        >
            Institution Demographics
        </button>
        @endif
        <!-- Collaborators Tab Button -->
        <button
            type="button"
            :class="tab === 'collaborators' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded font-semibold focus:outline-none w-full sm:w-auto"
            @click="tab = 'collaborators'"
        >
            Collaborators
        </button>
    </div>




















    <!-- Survey Information Tab -->
    <div x-show="tab === 'info'" x-cloak>
        <form wire:submit.prevent="saveSurveyInformation" class="space-y-4" x-data="{ fileName: '' }">
            
            <!-- Institution-Only Checkbox -->
            @if(!auth()->user()->isSuperAdmin())
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg w-full">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="institution-only-{{ $survey->id }}" 
                        wire:model.defer="isInstitutionOnly"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                        x-bind:disabled="isDisabled"
                    >
                    <label for="institution-only-{{ $survey->id }}" class="ml-2 text-sm font-medium text-gray-900">
                        Make this survey institution-only
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-600">
                    Institution-only surveys are only visible to members of your institution.
                </p>
            </div>
            @endif
            
            <!-- Announce on Publish Checkbox (Blue) - Only for researcher, institution admin, or super admin -->
            @if(auth()->user()->isInstitutionAdmin() || auth()->user()->isSuperAdmin())
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg w-full">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="announce-on-publish-{{ $survey->id }}" 
                        wire:model.defer="isAnnounced"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                        x-bind:disabled="isDisabled"
                    >
                    <label for="announce-on-publish-{{ $survey->id }}" class="ml-2 text-sm font-medium text-blue-900">
                        Create an announcement on publish
                    </label>
                </div>
                <p class="mt-1 text-xs text-blue-600">
                    If checked, an announcement will be created when this survey is published.
                </p>
            </div>
            @endif








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
                {{-- Only show Allocate Points button if not ongoing or finished --}}
                <template x-if="!isDisabled">
                    <button 
                        type="button" 
                        x-on:click="$dispatch('open-modal', {name : 'survey-boost-modal-{{ $survey->id }}'})"
                        class="px-4 py-2 bg-[#03b8ff] hover:bg-[#0295d1] text-white font-medium rounded transition duration-200 text-sm w-full sm:w-auto"
                    >
                        Allocate Points
                    </button>
                </template>
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
                    <label for="banner-{{ $survey->id }}" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100"
                          x-bind:class="{ 'opacity-50 cursor-not-allowed': isDisabled }">
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
                               wire:model="banner_image"
                               accept="image/*"
                               @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                               x-bind:disabled="isDisabled" />
                    </label>

                    {{-- Loading Indicator --}}
                    <div wire:loading wire:target="banner_image" class="text-sm text-gray-500 mt-1">Uploading...</div>

                    {{-- Image Preview --}}
                    @if ($banner_image) {{-- Show preview of NEW image --}}
                        <div class="mt-4 relative">
                            <span class="block text-sm font-medium text-gray-700 mb-1">Uploaded Survey Banner Preview:</span>
                            <div class="relative">
                                <img src="{{ $banner_image->temporaryUrl() }}" alt="New Banner Preview" class="max-h-40 rounded shadow">
                                <button 
                                    type="button" 
                                    wire:click="removeBannerImagePreview" 
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    title="Remove image"
                                    x-bind:disabled="isDisabled"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @elseif ($survey->image_path) {{-- Show CURRENT saved image if no new one is selected --}}
                        <div class="mt-4 relative">
                            <span class="block text-sm font-medium text-gray-700 mb-1">Current Survey Banner:</span>
                            <div class="relative">
                                <img src="{{ asset('storage/' . $survey->image_path) }}" alt="Survey Banner" class="max-h-40 rounded shadow" />
                                <button 
                                    type="button" 
                                    wire:click="deleteCurrentBannerImage" 
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    title="Delete image"
                                    x-bind:disabled="isDisabled"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                    @error('banner_image') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            </div>






























            <!-- Survey Title -->
            <div>
                <label for="survey-title-{{ $survey->id }}" class="block font-semibold mb-1"   >Survey Title</label>
                <input type="text" id="survey-title-{{ $survey->id }}" wire:model.defer="title" class="w-full border rounded px-3 py-2" x-bind:disabled="isDisabled" maxlength="256"/>
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Survey Description -->
            <div>
                <label for="survey-description-{{ $survey->id }}" class="block font-semibold mb-1" >Survey Description</label>
                <textarea id="survey-description-{{ $survey->id }}" wire:model.defer="description" class="w-full border rounded px-3 py-2" rows="3" x-bind:disabled="isDisabled" maxlength="2046" ></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Survey Type -->
            <div>
                <label for="survey-type-{{ $survey->id }}" class="block font-semibold mb-1">Survey Type</label>
                <select id="survey-type-{{ $survey->id }}" wire:model.defer="type" class="w-full border rounded px-3 py-2" x-bind:disabled="isDisabled">
                    <option value="basic">Basic Survey</option>
                    <option value="advanced">Advanced Survey</option>
                </select>
                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Survey Target Number of Respondents -->
            <div>
                <label class="block font-semibold mb-1">Target Respondents</label>
                <input 
                    type="number" 
                    wire:model.defer="target_respondents" 
                    class="w-full border rounded px-3 py-2" 
                    min="10" 
                    max="1000" 
                    step="1"
                    x-bind:disabled="isDisabled" 
                />
                @error('target_respondents') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <!-- Survey Topic -->
            <div>
                <label for="survey-topic-{{ $survey->id }}" class="block font-semibold mb-1">Survey Topic</label>
                <select id="survey-topic-{{ $survey->id }}" wire:model.defer="survey_topic_id" class="w-full border rounded px-3 py-2" x-bind:disabled="isDisabled">
                    <option value="">Select a Topic</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                    @endforeach
                </select>
                @error('survey_topic_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Date Inputs -->
            <div x-data="{
                currentTime: new Date().toISOString().slice(0, 16),
                updateEndDateConstraints() {
                    const endDateInput = this.$refs.endDateInput;
                    const startDateInput = this.$refs.startDateInput;
                    if (endDateInput && startDateInput) {
                        const minDate = startDateInput.value || this.currentTime;
                        endDateInput.min = minDate;
                        endDateInput.setAttribute('min', minDate);
                        
                        // Clear end date if it's now before the new start date
                        if (startDateInput.value && endDateInput.value && endDateInput.value < startDateInput.value) {
                            endDateInput.value = '';
                            // Trigger Livewire update
                            endDateInput.dispatchEvent(new Event('input'));
                        }
                    }
                }
            }" x-init="$nextTick(() => updateEndDateConstraints())">
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Start Date <span class="text-sm text-gray-500 italic">(Make sure all settings are complete before setting this. Once saved, the survey will auto-open on the start date.)</span></label> 
                    <div class="flex flex-col space-y-2">
                        <input 
                            type="datetime-local" 
                            wire:model.defer="start_date"
                            x-ref="startDateInput"
                            class="w-full border rounded px-3 py-2"
                            x-init="$el.min = currentTime; $el.setAttribute('min', currentTime);"
                            :min="currentTime"
                            @input="updateEndDateConstraints()"
                            @change="updateEndDateConstraints()"
                            x-bind:disabled="isDisabled || @js($survey->status === 'published' || $survey->status === 'ongoing')"
                        />
                        @if($survey->status === 'pending')
                        <div class="text-amber-600 text-xs flex items-start">
                            <span>
                               The survey will auto-publish on the start date.
                               Must have 1 page, 6 required questions, and valid demographics for advanced surveys to enable saving.
                            </span>
                        </div>
                        @endif
                        @if($survey->status === 'published' || $survey->status === 'ongoing')
                            <div class="text-blue-600 text-xs">
                                Start date cannot be modified for published surveys.
                            </div>
                        @endif
                    </div>
                    @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-4">
                    <label class="block font-semibold mb-1">End Date</label>
                    <input 
                        type="datetime-local" 
                        wire:model.defer="end_date"
                        x-ref="endDateInput"
                        class="w-full border rounded px-3 py-2"
                        x-init="$nextTick(() => { 
                            const startInput = $refs.startDateInput;
                            $el.min = (startInput && startInput.value) ? startInput.value : currentTime; 
                        })"
                        x-bind:disabled="isDisabled"
                    />
                    @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            
            {{-- Save Button for Information - Only show when not ongoing or finished --}}
            <div x-show="!isDisabled">
                <button type="submit" class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    <span wire:loading.remove wire:target="saveSurveyInformation">Save Information</span>
                    <span wire:loading wire:target="saveSurveyInformation">Saving...</span>
                </button>
            </div>
            


            {{-- Read-only message for ongoing/finished surveys --}}
            <div x-show="isDisabled" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700">
                    This survey is {{ $survey->status }} and cannot be edited. You can view the settings but not modify them.
                </p>
            </div>
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
                                    x-bind:disabled="isDisabled"
                                >
                                <label for="survey-tag-{{ $tag->id }}" class="ml-2 text-sm font-medium text-gray-700">
                                    {{ $tag->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            
            {{-- Save Button for Demographics - Only show when not ongoing or finished --}}
            <div x-show="!isDisabled">
                <button type="submit" class="mt-6 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 font-medium">
                    <span wire:loading.remove wire:target="saveSurveyTags">Save Demographics</span>
                    <span wire:loading wire:target="saveSurveyTags">Saving...</span>
                </button>
            </div>
            
            {{-- Read-only message for ongoing/finished surveys --}}
            <div x-show="isDisabled" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700">
                    This survey is {{ $survey->status }} and cannot be edited. You can view the demographic settings but not modify them.
                </p>
            </div>
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
                                        x-bind:disabled="isDisabled"
                                    >
                                    <label for="institution-tag-{{ $tag->id }}" class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $tag->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                
                {{-- Save Button for Institution Demographics - Only show when not ongoing or finished --}}
                <div x-show="!isDisabled">
                    <button type="submit" class="mt-6 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 font-medium">
                        <span wire:loading.remove wire:target="saveInstitutionTags">Save Institution Demographics</span>
                        <span wire:loading wire:target="saveInstitutionTags">Saving...</span>
                    </button>
                </div>
                
                {{-- Read-only message for ongoing/finished surveys --}}
                <div x-show="isDisabled" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-700">
                        This survey is {{ $survey->status }} and cannot be edited. You can view the institution demographic settings but not modify them.
                    </p>
                </div>
            @endif
        </form>
    </div>

    <!-- Collaborators Tab -->
    <div x-show="tab === 'collaborators'" x-cloak>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Survey Collaborators</h3>
            <p class="text-sm text-gray-500">Add collaborators who can edit and manage this survey by entering their user UUID.</p>
        </div>
        
        <form wire:submit.prevent="addCollaborator" class="mb-6" x-data="{ 
            checkExistingCollaborators() {
                const inputValue = $refs.collaboratorInput.value.trim();
                
                // Check if input is empty
                if (!inputValue) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a UUID. The field cannot be empty.',
                        confirmButtonColor: '#e3342f',
                    });
                    return false;
                }
                
                // Basic UUID format validation (8-4-4-4-12 pattern)
                const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                if (!uuidPattern.test(inputValue)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid UUID Format',
                        text: 'Please enter a valid UUID in format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                        confirmButtonColor: '#e3342f',
                    });
                    return false;
                }
                
                // Check if the UUID is already in the collaborators list
                // (Note: Existence of the UUID in the database is checked server-side)
                const existingCollaborators = @js($collaborators);
                const isExisting = existingCollaborators.some(collab => collab.uuid === inputValue);
                
                if (isExisting) {
                    // Show validation error using SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'This user is already a collaborator on this survey.',
                        confirmButtonColor: '#e3342f',
                    });
                    return false;
                }
                
                return true;
            }
        }">
            <div class="flex flex-col md:flex-row gap-2">
                <div class="flex-1">
                    <input 
                        type="text" 
                        wire:model.defer="newCollaboratorUuid" 
                        class="w-full border rounded px-3 py-2" 
                        placeholder="Enter user UUID (e.g. c32636cb-610c-4d02-ac31-8b3dab30e075)"
                        maxlength="36"
                        x-bind:disabled="isDisabled"
                        x-ref="collaboratorInput"
                    />
                </div>
                
                <button 
                    type="button" 
                    class="px-4 py-3 md:py-[9px] bg-green-500 text-white rounded hover:bg-green-600 flex items-center justify-center"
                    x-bind:disabled="isDisabled"
                    @click.prevent="if(checkExistingCollaborators()) $wire.addCollaborator()"
                >
                    <span wire:loading.remove wire:target="addCollaborator">Add Collaborator</span>
                    <span wire:loading wire:target="addCollaborator">Adding...</span>
                </button>
            </div>
        </form>
        
        <div class="mt-6">
            <h4 class="font-semibold mb-2">Current Collaborators</h4>
            
            @if(count($collaborators) > 0)
                <div class="space-y-3">
                    @foreach($collaborators as $collaborator)
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
                            <div>
                                <div class="font-medium text-gray-800">{{ $collaborator['name'] }}</div>
                                <div class="text-sm text-gray-500">UUID: {{ $collaborator['uuid'] }}</div>
                            </div>
                            @if(Auth::id() === $survey->user_id)
                            <button 
                                type="button"
                                wire:click="removeCollaborator('{{ $collaborator['uuid'] }}')"
                                class="text-red-500 hover:text-red-700"
                                title="Remove collaborator"
                                x-bind:disabled="isDisabled"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center p-4 bg-gray-50 rounded-lg border">
                    <p class="text-gray-500">No collaborators added yet</p>
                </div>
            @endif
        </div>
        
        <!-- Read-only message for ongoing/finished surveys -->
        <div x-show="isDisabled" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-700">
                This survey is {{ $survey->status }} and collaborator settings cannot be modified.
            </p>
        </div>
    </div>
    <x-modal name="survey-boost-modal-{{ $survey->id }}" title="Survey Boost Allocation">
        @livewire('surveys.form-builder.modal.survey-boost-modal', ['survey' => $survey], key('survey-boost-modal-' . $survey->id))
    </x-modal>
    <!-- Institution-Only Checkbox handler - Update the script -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('updated', (event) => {
                if (typeof event.isInstitutionOnly !== 'undefined') {

                    // Get the Alpine component for the modal
                    const modalComponent = Alpine.$data(document.querySelector('[x-data*="tab"]'));
                
                } 
                else if (!event.isInstitutionOnly && Alpine.$data(document.querySelector('[x-data*="tab"]')).tab === 'institution_demographics') {
                        Alpine.$data(document.querySelector('[x-data*="tab"]')).tab = 'info';
                }
                }
            )});


        document.addEventListener('livewire:initialized', () => {
            Livewire.on('showErrorAlert', (data) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: data.message || 'An error occurred.',
                    confirmButtonColor: '#e3342f',
                });
            });

            Livewire.on('showSuccessAlert', (data) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Upload successful!',
                    confirmButtonColor: '#22c55e',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Updated event handler for validation errors to ensure the message is correctly displayed
            Livewire.on('validation-error', (event) => {
                console.log('Raw validation error event:', event); // Debug log
                
                let errorMessage = 'Please check your input and try again.';
                
                // Handle different event data structures
                if (event && typeof event === 'object') {
                    if (event.message) {
                        errorMessage = event.message;
                    } else if (event[0] && event[0].message) {
                        errorMessage = event[0].message;
                    } else if (Array.isArray(event) && event.length > 0) {
                        // Handle array of event data
                        const firstEvent = event[0];
                        if (firstEvent && firstEvent.message) {
                            errorMessage = firstEvent.message;
                        }
                    }
                }
                
                console.log('Final error message:', errorMessage); // Debug log
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessage,
                    confirmButtonColor: '#e3342f',
                });
            });
        });

    </script>
</div>

