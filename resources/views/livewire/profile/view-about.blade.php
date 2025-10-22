<div class="space-y-8">
    <!-- Account Information -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">Account Information</h2>
        <div class="space-y-2">
            <div><span class="font-semibold">Name:</span> {{ $user->name ?? 'N/A' }}</div>
            <div><span class="font-semibold">UUID:</span> {{ $user->uuid ?? 'N/A' }}</div>
            <div><span class="font-semibold">Email:    @if($user->email_verified_at)
                    <span title="Verified" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-5 h-5 text-green-500 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                @else
                    <span x-data x-on:click="$dispatch('open-modal', {name: 'otp-verification'})" class="inline-block align-middle cursor-pointer italic text-red-500 ml-1" title="Click to verify email">
                        Not Verified (Click to verify)
                    </span>
                @endif </span> {{ $user->email ?? 'N/A' }}
             
            </div>
            <div><span class="font-semibold">Phone Number:</span> {{ $user->phone_number ?? 'N/A' }}</div>
            <div><span class="font-semibold">Type:</span> {{ ucfirst($user->type ?? 'Respondent') }}</div>
            <div><span class="font-semibold">Points:</span> {{ $user->points ?? 0 }}</div>
            {{-- Rank Field --}}
            <div>
                <span class="font-semibold">Rank:</span>
                <span class="ml-2">
                    {{ ucfirst($user?->rank ?: 'silver') }}
                    (
                    @php
                        $userLevel = $user->getLevel();
                        $userExperience = $user->experience_points;
                        $xpForNextLevel = $user->getXpRequiredForNextLevel();
                    @endphp
                    @if($userLevel && $userExperience !== null && $xpForNextLevel)
                        {{ $userExperience }}/{{ $xpForNextLevel }}
                    @else
                        N/A
                    @endif
                    )
                    
                </span>
            </div>
            <div class="flex items-center">
                <span class="font-semibold mr-1">Trust Score:</span> {{ $user->trust_score ?? 0 }}/100
                <!-- Question mark icon that triggers the trust score info modal -->
                <button 
                    x-data 
                    x-on:click="$dispatch('open-modal', {name: 'trust-score-info'})"
                    class="ml-2 text-blue-500 hover:text-blue-700"
                    title="Trust Score Information"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 cursor-pointer">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                    </svg>
                </button>
            </div>
        </div>   
    </div>

    <!-- Demographic Tags -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Demographic Tags</h2>
            @if($canUpdateDemographics)
                <span class="text-green-500 text-sm font-medium">Available for update</span>
            @else
                <span class="text-red-500 text-sm font-medium italic">{{ $timeUntilUpdateText }}</span>
            @endif
        </div>
        
        <form wire:submit.prevent="saveDemographicTags" class="space-y-4" x-data="{
            confirmDemographicSave() {
                Swal.fire({
                    title: 'Confirm Demographic Update',
                    html: '<div>Are you sure you want to update your demographic tags?<br><small>You will not be able to update them again for 4 months.</small></div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    focusConfirm: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.saveDemographicTags();
                    }
                });
            }
        }"
        x-on:no-changes-detected.window="
            if ($event.detail.type === 'demographic') {
                Swal.fire({
                    title: 'No Changes Detected',
                    text: 'You have not made any changes to your demographic information.',
                    icon: 'info',
                    confirmButtonColor: '#3085d6'
                });
            }
        ">
            @foreach($tagCategories as $category)
                <div>
                    <label class="block font-semibold mb-1">{{ $category->name }}</label>
                    <select wire:model.defer="selectedTags.{{ $category->id }}" 
                            class="w-full border rounded px-3 py-2 @if(!$canUpdateDemographics) bg-gray-100 @endif" 
                            {{ !$canUpdateDemographics ? 'disabled' : '' }}>
                        <option value="">Select {{ $category->name }}</option>
                        @foreach($category->tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <div class="mb-4">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 text-sm text-yellow-800 rounded">
                    <strong>Note:</strong> Once added or updated, you will not be able to change your demographic tags again for 4 months. This is to ensure data integrity.
                </div>
            </div>
            
            <button 
                type="button"
                class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center justify-center @if(!$canUpdateDemographics) opacity-50 cursor-not-allowed @endif"
                style="width: 180px;" 
                x-on:click="confirmDemographicSave()"
                wire:loading.attr="disabled"
                {{ !$canUpdateDemographics ? 'disabled' : '' }}
            >
                <span class="flex items-center">
                    <span wire:loading.remove wire:target="saveDemographicTags">Save Demographics</span>
                    <span wire:loading wire:target="saveDemographicTags" class="flex items-center ml-2">
                        <svg class="animate-spin h-5 w-5 text-white mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </span>
            </button>
        </form>
            
        <!-- Institution Demographic Tags - Only shown if user belongs to an institution -->
        @if(count($institutionTagCategories) > 0)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">{{ $user->institution->name ?? 'Institution' }} Demographics</h3>
                    @if($canUpdateInstitutionDemographics)
                        <span class="text-green-500 text-sm font-medium">Available for update</span>
                    @else
                        <span class="text-red-500 text-sm font-medium italic">{{ $timeUntilInstitutionUpdateText }}</span>
                    @endif
                </div>
                
                <form wire:submit.prevent="saveInstitutionDemographicTags" class="space-y-4" x-data="{
                    confirmInstitutionDemographicSave() {
                        Swal.fire({
                            title: 'Confirm Institution Demographic Update',
                            html: '<div>Are you sure you want to update your institution demographic tags?<br><small>You will not be able to update them again for 4 months.</small></div>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, update',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#6b7280',
                            reverseButtons: true,
                            focusConfirm: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.saveInstitutionDemographicTags();
                            }
                        });
                    }
                }"
                x-on:no-changes-detected.window="
                    if ($event.detail.type === 'institution') {
                        Swal.fire({
                            title: 'No Changes Detected',
                            text: 'You have not made any changes to your institution demographic information.',
                            icon: 'info',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                ">
                    @foreach($institutionTagCategories as $category)
                        <div class="mb-4">
                            <label class="block font-semibold mb-1">{{ $category->name }}</label>
                            <select wire:model.defer="selectedInstitutionTags.{{ $category->id }}" 
                                    class="w-full border rounded px-3 py-2 @if(!$canUpdateInstitutionDemographics) bg-gray-100 @endif"
                                    {{ !$canUpdateInstitutionDemographics ? 'disabled' : '' }}>
                                <option value="">Select {{ $category->name }}</option>
                                @foreach($category->tags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                
                    <div class="mb-4">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 text-sm text-yellow-800 rounded">
                            <strong>Note:</strong> Once added or updated, you will not be able to change your institution demographic tags again for 4 months. This is to ensure data integrity.
                        </div>
                    </div>
                    
                    <button 
                        type="button"
                        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center justify-center @if(!$canUpdateInstitutionDemographics) opacity-50 cursor-not-allowed @endif"
                        style="width: 240px;" 
                        x-on:click="confirmInstitutionDemographicSave()"
                        wire:loading.attr="disabled"
                        {{ !$canUpdateInstitutionDemographics ? 'disabled' : '' }}
                    >
                        <span class="flex items-center">
                            <span wire:loading.remove wire:target="saveInstitutionDemographicTags">Save Institution Demographics</span>
                            <span wire:loading wire:target="saveInstitutionDemographicTags" class="flex items-center ml-2">
                                <svg class="animate-spin h-5 w-5 text-white mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                        </span>
                    </button>
                </form>
            </div>
        @endif

        @if (session()->has('tags_saved'))
            <div class="mt-4 bg-green-50 border-l-4 border-green-400 p-3 text-sm text-green-800 rounded">
                {{ session('tags_saved') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-3 text-sm text-red-800 rounded">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Trust Score Info Modal -->
    <x-modal name="trust-score-info" title="Trust Score Information">
        <div class="p-4">
            <div class="flex items-center mb-6">
                <div class="mr-3">
                    <span class="text-gray-600">Current Score:</span>
                    <strong class="text-2xl text-blue-600">{{ $user->trust_score }}</strong>
                </div>
                <div class="flex-grow">
                    <div class="h-2 w-full bg-gray-200 rounded-full">
                        <div class="h-2 bg-blue-600 rounded-full" style="width: {{ min($user->trust_score, 100) }}%"></div>
                    </div>
                </div>
            </div>
            
            <!-- False Reports Section - Only for Researchers -->
            @if($user->type === 'researcher')
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                    False Reports (as Reporter)
                </h3>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <span class="text-gray-600">False reports:</span>
                            <span class="font-medium">{{ $falseReportCount }}</span>
                            <span class="text-gray-400 mx-1">/</span>
                            <span class="text-gray-600">Total reports:</span>
                            <span class="font-medium">{{ $totalReportCount }}</span>
                        </div>
                        <span class="px-2 py-1 rounded-md text-xs font-medium {{ $falseReportPercentage > 10 ? 'bg-red-100 text-red-800' : ($falseReportPercentage > 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                            {{ $falseReportPercentage }}%
                        </span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4">
                        <div class="h-1.5 rounded-full {{ $falseReportPercentage > 10 ? 'bg-red-500' : ($falseReportPercentage > 5 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                             style="width: {{ min($falseReportPercentage, 100) }}%">
                        </div>
                    </div>
                    
                    @if($falseReportThresholdMet)
                        <div class="bg-red-50 border-l-4 border-red-500 p-3 text-sm">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <strong>Warning:</strong> You have exceeded the threshold of 2 false reports. Each additional false report will result in a <strong class="text-red-700">{{ $falseReportPenalty }} point</strong> deduction from your trust score.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 text-sm">
                            <div class="flex">
                                <svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    You have {{ $falseReportCount }} false {{ $falseReportCount == 1 ? 'report' : 'reports' }}. Users with more than 2 false reports will receive trust score penalties.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Reported Responses Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Reported Responses (as Respondent)
                </h3>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <span class="text-gray-600">Reported responses:</span>
                            <span class="font-medium">{{ $reportedResponseCount}}</span>
                            <span class="text-gray-400 mx-1">/</span>
                            <span class="text-gray-600">Valid responses:</span>
                            <span class="font-medium">{{ $validResponseCount }}</span>
                        </div>
                        <span class="px-2 py-1 rounded-md text-xs font-medium {{ $reportedResponsePercentage > 10 ? 'bg-red-100 text-red-800' : ($reportedResponsePercentage > 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                            {{ $reportedResponsePercentage }}%
                        </span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4">
                        <div class="h-1.5 rounded-full {{ $reportedResponsePercentage > 10 ? 'bg-red-500' : ($reportedResponsePercentage > 5 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                             style="width: {{ min($reportedResponsePercentage, 100) }}%">
                        </div>
                    </div>
                    
                    @if($reportedResponseThresholdMet)
                        <div class="bg-red-50 border-l-4 border-red-500 p-3 text-sm">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <strong>Warning:</strong> You have exceeded the threshold of 2 reported responses. Each additional report against you will result in a <strong class="text-red-700">{{ $reportedResponseDeduction }} point</strong> deduction from your trust score.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 text-sm">
                            <div class="flex">
                                <svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    You have {{ $reportedResponseCount }} reported {{ $reportedResponseCount == 1 ? 'response' : 'responses' }}. Users with more than 2 reported responses will receive trust score penalties.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Footer information -->
            <div class="text-xs text-gray-500 mt-4 flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>The percentages shown are calculated as (value1 / value2) × 100%. Keeping these percentages low helps maintain a high trust score.</span>
            </div>
        </div>
    </x-modal>
    {{-- OTP Verification Modal --}}
    @include('livewire.auth.otp-verification-modal')
</div>
