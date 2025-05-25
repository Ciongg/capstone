{{-- Filter Panel - Controlled by Livewire --}}
@if($showFilterPanel)
<div 
    class="mb-6 p-4 bg-white rounded-lg shadow-md border border-gray-100"
    wire:transition.origin.top.left
    x-data="{ 
        activeTagTab: 'general',
        updateActiveTab() {
            this.activeTagTab = $wire.tempInstitutionOnly ? 'institution' : 'general';
        }
    }"
    x-init="updateActiveTab()"
    x-effect="updateActiveTab()"
>

{{-- Survey Type Filters --}}
    <div class="mt-6 border-t border-gray-200 pt-4">
        <h3 class="font-medium text-gray-700 mb-3">Filter by Survey Type</h3>
        
        <div class="mb-4">
            <h4 class="font-semibold text-gray-600 mb-2">Survey Complexity</h4>
            <div class="flex space-x-3">
                <button 
                    wire:click="toggleTempSurveyType('basic')" 
                    type="button"
                    class="px-4 py-2 rounded-md text-sm transition-colors duration-150
                           {{ $tempSurveyType === 'basic' 
                               ? 'bg-[#03b8ff] text-white font-semibold shadow-md'  
                               : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                >
                    Basic
                </button>
                <button 
                    wire:click="toggleTempSurveyType('advanced')" 
                    type="button"
                    class="px-4 py-2 rounded-md text-sm transition-colors duration-150
                           {{ $tempSurveyType === 'advanced' 
                               ? 'bg-[#03b8ff] text-white font-semibold shadow-md' 
                               : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                >
                    Advanced
                </button>
                <button 
                    wire:click="clearTempSurveyType" 
                    type="button"
                    class="px-4 py-2 rounded-md text-sm transition-colors duration-150
                           {{ $tempSurveyType === null 
                               ? 'bg-[#03b8ff] text-white font-semibold shadow-md' 
                               : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                >
                    All Types
                </button>
            </div>
        </div>
        
        <div class="mt-6"> 
            <h4 class="font-semibold text-gray-600 mb-2">Survey Access</h4> 
            <div class="flex items-center">
                <label for="institution-only" class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input 
                            type="checkbox" 
                            id="institution-only" 
                            wire:model.live="tempInstitutionOnly"
                            wire:change="$set('hasUnsavedFilterChanges', true)"
                            class="sr-only"
                        >
                        <div class="w-10 h-5 bg-gray-300 rounded-full shadow-inner"></div>
                        <div class="dot absolute w-5 h-5 bg-white rounded-full shadow -left-1 -top-0 transition" 
                             :class="{ 'transform translate-x-5 bg-[#03b8ff]': $wire.tempInstitutionOnly }"></div> 
                    </div>
                    <div class="ml-3 text-sm font-medium text-gray-700">
                        Institution Only Surveys
                    </div>
                </label>
            </div>
        </div>
    </div>
    
    {{-- Tag Filters --}}
    <div class="mt-6 border-t border-gray-200 pt-4"> 
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-medium text-gray-700">
                <span x-show="activeTagTab === 'general'">Filter Surveys by Tag</span>
                <span x-show="activeTagTab === 'institution'">Filter by Institution Tag</span>
            </h3>
            {{-- exit button --}}
            <button wire:click="toggleFilterPanel" class="text-gray-400 hover:text-gray-600" title="Close panel">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        
        {{-- Tag selection info and clear button --}}
        <div x-show="activeTagTab === 'general'">
            @if(!empty($tempSelectedTagIds))
                <div class="mb-4 p-2 bg-blue-50 border border-blue-100 rounded-md">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-700">
                            {{ count($tempSelectedTagIds) }} tag(s) selected
                        </span>
                        <button wire:click="clearPanelTagFilter" class="text-xs text-blue-600 hover:text-blue-800 underline">
                            Clear selection
                        </button>
                    </div>
                </div>
            @endif
        </div>
        
        <div x-show="activeTagTab === 'institution'">
            @if(!empty($tempSelectedInstitutionTagIds))
                <div class="mb-4 p-2 bg-blue-50 border border-blue-100 rounded-md">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-700">
                            {{ count($tempSelectedInstitutionTagIds) }} tag(s) selected
                        </span>
                        <button wire:click="clearPanelInstitutionTagFilter" class="text-xs text-blue-600 hover:text-blue-800 underline">
                            Clear selection
                        </button>
                    </div>
                </div>
            @endif
        </div>
        
        {{-- General Tags Content --}}
        <div x-show="activeTagTab === 'general'" class="space-y-4 max-h-96 overflow-y-auto">
            @forelse($tagCategories as $category)
                <div wire:key="filter-category-{{ $category->id }}">
                    <h4 class="font-semibold text-gray-600 mb-2">{{ $category->name }}</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        @foreach($category->tags as $tag)
                            <button 
                                wire:click="togglePanelTagFilter({{ $tag->id }})"
                                wire:key="filter-tag-{{ $tag->id }}"
                                type="button"
                                class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors duration-150
                                       {{ in_array($tag->id, $tempSelectedTagIds) 
                                       ? 'bg-[#03b8ff] text-white font-semibold shadow-md'
                                       : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                            >
                                {{ $tag->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="p-3 text-center text-gray-500">No general tag categories available</div>
            @endforelse
        </div>
        
        {{-- Institution Tags Content --}}
        <div x-show="activeTagTab === 'institution'" class="space-y-4 max-h-96 overflow-y-auto">
            @php
                $institutionTagCategories = Auth::user()->institution_id
                    ? \App\Models\InstitutionTagCategory::where('institution_id', Auth::user()->institution_id)
                          ->with('tags')
                          ->get()
                    : collect([]);
            @endphp
            
            @if(!Auth::user()->institution_id)
                <div class="p-3 text-center text-gray-500">
                    You must belong to an institution to see institution-specific tags.
                </div>
            @else
                @forelse($institutionTagCategories as $category)
                    <div wire:key="filter-inst-category-{{ $category->id }}">
                        <h4 class="font-semibold text-gray-600 mb-2">{{ $category->name }}</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                            @foreach($category->tags as $tag)
                                <button 
                                    wire:click="togglePanelInstitutionTagFilter({{ $tag->id }})"
                                    wire:key="filter-inst-tag-{{ $tag->id }}"
                                    type="button"
                                    class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors duration-150
                                           {{ in_array($tag->id, $tempSelectedInstitutionTagIds ?? []) 
                                           ? 'bg-[#03b8ff] text-white font-semibold shadow-md'
                                           : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
                                >
                                    {{ $tag->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-gray-500">No institution tag categories available</div>
                @endforelse
            @endif
        </div>
    </div>
    
    {{-- Panel action buttons --}}
    <div class="mt-4 flex justify-end space-x-3">
        <button 
            wire:click="toggleFilterPanel"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
        >
            Cancel
        </button>
        <button 
            wire:click="applyPanelTagFilters"
            class="px-4 py-2 bg-[#03b8ff] text-white text-sm rounded-md hover:bg-[#0295d1] shadow-sm
                  {{ $hasUnsavedFilterChanges ? '' : 'opacity-50 cursor-not-allowed' }}"
            {{ $hasUnsavedFilterChanges ? '' : 'disabled' }}
        >
            Apply Filters
        </button>
    </div>
</div>
@endif