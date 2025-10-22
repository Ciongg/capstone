{{-- Individual Survey Card --}}
<div wire:key="survey-card-{{ $survey->id }}" class="relative bg-white shadow-2xl rounded-xl p-0 flex flex-col min-h-[500px]">
    {{-- Header --}}
    <div class="w-full px-3 sm:px-4 py-3 rounded-t-xl bg-gray-50 border-b border-gray-200 flex-shrink-0 mb-2 shadow-[inset_0_0px_4px_0_rgba(0,0,0,0.1)]">
        <div class="flex items-center mb-2">
            <img src="{{ $survey->user?->profile_photo_url }}" alt="{{ $survey->user?->name ?? 'User' }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover mr-2 sm:mr-3">
            <div class="flex flex-col flex-grow min-w-0">
                <span class="text-sm sm:text-base font-semibold text-gray-800 truncate">{{ $survey->user?->name ?? 'User' }}</span>
                <span class="text-xs text-gray-500 truncate">{{ $survey->user?->institution?->name }}</span>
            </div>
            <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-2 sm:px-3 py-1 rounded-full ml-1">
                <span class="font-bold text-white drop-shadow text-sm">{{ $survey->points_allocated ?? 0 }}</span>
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                </svg>
            </div>
        </div>
        <div class="w-full">
           <span class="text-sm font-semibold text-left truncate block" title="{{ $survey->title ?: 'Untitled Survey' }}">
                {{ $survey->title ?: 'Untitled Survey' }}
            </span>
        </div>
    </div>
    
    {{-- Image --}}
    <div class="w-full flex-grow mt-1 flex items-center justify-center mb-2 relative px-3 sm:px-4 min-h-0">
        @if($survey->image_path)

            @php $imageUrl = asset('storage/' . ($survey->image_path)); @endphp

            <button @click="fullscreenImageSrc = '{{ $imageUrl }}'" class="cursor-pointer w-full h-full flex items-center justify-center">
                <img src="{{ $imageUrl }}" alt="Survey image for {{ $survey->title }}" class="rounded-lg object-contain max-w-full max-h-[340px]" />
            </button>
        @else
            <div class="w-full h-full max-h-[340px] bg-gray-200 flex items-center justify-center rounded-lg"><span class="text-gray-500 text-sm">no image</span></div>
        @endif
    </div>

    {{-- Tags Section --}}
    <div class="w-full px-3 sm:px-4 mb-3 flex-shrink-0">
        <div class="flex flex-wrap gap-2 justify-center min-h-[36px] items-center">
            @php
                // Get the user's tag IDs to check for matches
                $userTagIds = auth()->check() ? auth()->user()->tags()->pluck('tags.id')->toArray() : [];
                $userInstitutionTagIds = auth()->check() ? auth()->user()->institutionTags()->pluck('institution_tags.id')->toArray() : [];
                
                // Determine which tags to use based on survey type
                $isInstitutionSurvey = $survey->is_institution_only;
                $tagCollection = $isInstitutionSurvey ? $survey->institutionTags : $survey->tags;
                $userTagCollection = $isInstitutionSurvey ? $userInstitutionTagIds : $userTagIds;
                $tagPrefix = $isInstitutionSurvey ? 'inst-tag' : 'tag';
                
                // Sort tags - matching tags first
                $displayTags = $tagCollection->isEmpty() ? collect([]) : $tagCollection->sortByDesc(function($tag) use ($userTagCollection) {
                    return in_array($tag->id, $userTagCollection) ? 1 : 0;
                })->take(3);
            @endphp
            
            @if($displayTags->isEmpty())
                {{-- Show placeholders if no tags --}}
               {{-- <span class="italic font-sm text-gray-300"> no target tags</span> --}}
                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
            @else
                {{-- Display tags --}}
                @foreach($displayTags as $tag)
                    @php
                        $matchesUserTag = in_array($tag->id, $userTagCollection);
                    @endphp
                    <span
                        wire:key="survey-{{ $survey->id }}-{{ $tagPrefix }}-{{ $tag->id }}"
                        class="px-3 py-2 text-xs font-semibold rounded-full shadow-md overflow-hidden whitespace-nowrap max-w-[100px] text-ellipsis
                            bg-gray-100 text-gray-800 {{ $matchesUserTag ? 'border-2 border-blue-400' : '' }}"
                    >
                        {{ $tag->name }}
                    </span>
                @endforeach
                
                {{-- Add placeholders if needed if 3 is not reached --}}
                {{-- @if($displayTags->count() < 3)
                    @for($i = $displayTags->count(); $i < 3; $i++)
                        <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                    @endfor
                @endif --}}
            @endif
        </div>
    </div>

    {{-- Survey Info Section Survey Topic Repsondents Days--}}
    <div class="w-full px-3 sm:px-4 mb-3 flex-shrink-0">
        <div class="flex flex-wrap gap-2 justify-between items-center">
            {{-- Survey Topic (Moved here from bottom) --}}
            <div class="flex items-center">
                @if($survey->topic)
                    <span class="px-3 py-1 text-xs font-semibold rounded-full text-gray-800">
                        {{ $survey->topic->name }}
                    </span>
                @else
                    {{-- Optional: Placeholder if no topic --}}
                    <span class="px-3 py-1 text-xs font-semibold text-gray-400 italic">No Topic</span>
                @endif
            </div>

            {{-- Respondents Needed --}}
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-xs text-gray-700">{{ $survey->responses()->count() }}/{{ $survey->target_respondents ?: '∞' }}</span>
            </div>

            {{-- Days Until Closing --}}
            @php
                $daysLeft = null;
                $minutesLeft = null;
                $timeDisplay = null;
                
                $now = \App\Services\TestTimeService::now();
                
                // Check if survey hasn't started yet
                if($survey->start_date && $now->lt($survey->start_date)) {
                    $startDate = \Carbon\Carbon::parse($survey->start_date);
                    $daysUntilStart = round($now->diffInDays($startDate, false));
                    
                    // If it starts today (0 days), show minutes until start
                    if($daysUntilStart == 0) {
                        $minutesUntilStart = $now->diffInMinutes($startDate, false);
                        
                        if($minutesUntilStart > 60) {
                            $hoursUntilStart = floor($minutesUntilStart / 60);
                            $remainingMinutes = $minutesUntilStart % 60;
                            $timeDisplay = 'Opens in ' . $hoursUntilStart . 'h ' . $remainingMinutes . 'm';
                        } else {
                            $roundedMinutes = round($minutesUntilStart);
                            $timeDisplay = 'Opens in ' . $roundedMinutes . ' minutes';
                        }
                    } else {
                        // Standard day display for future days
                        $timeDisplay = 'Opens in ' . $daysUntilStart . ' ' . ($daysUntilStart == 1 ? 'day' : 'days');
                    }
                }
                // If survey has started, show end date info if available
                elseif($survey->end_date) {
                    $endDate = \Carbon\Carbon::parse($survey->end_date);
                    $daysLeft = round($now->diffInDays($endDate, false));
                    
                    // Check if end date is in the past
                    if ($now->gt($endDate)) {
                        $timeDisplay = 'Ended';
                    }
                    // If it ends today (0 days), show minutes remaining
                    elseif ($daysLeft == 0) {
                        $minutesLeft = $now->diffInMinutes($endDate, false);
                        
                        if ($minutesLeft > 60) {
                            $hoursLeft = floor($minutesLeft / 60);
                            $remainingMinutes = $minutesLeft % 60;
                            $timeDisplay = $hoursLeft . 'h ' . $remainingMinutes . 'm left';
                        } elseif ($minutesLeft > 0) {
                            // Round the minutes to nearest whole number
                            $roundedMinutes = round($minutesLeft);
                            $timeDisplay = $roundedMinutes . ' minutes left';
                        } else {
                            $timeDisplay = 'Ended';
                        }
                    } else {
                        // Standard day display for future days
                        $timeDisplay = $daysLeft == 1 ? '1 day left' : $daysLeft.' days left';
                    }
                }
            @endphp
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                @if($timeDisplay !== null)
                    <span class="text-xs {{ 
                        (strpos($timeDisplay, 'Opens') !== false) ? 'text-blue-600 font-semibold' : 
                        ((($daysLeft !== null && $daysLeft < 3 && $daysLeft >= 0) || 
                         ($minutesLeft !== null && $minutesLeft < 60) || 
                         $timeDisplay === 'Ended') ? 
                            'text-red-600 font-semibold' : 'text-gray-700')
                    }}">
                        {{ $timeDisplay }}
                    </span>
                @else
                    <span class="text-xs text-gray-700">Open-ended</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Read More Button, Institution Indicator & Survey Type --}}
    <div class="w-full flex items-center mt-auto mb-4 px-3 sm:px-4 flex-shrink-0">
        {{-- Read More Button --}}
        <div>
            @if(($survey->is_expired_locked ?? false) || ($survey->is_response_limit_locked ?? false) || ($survey->is_demographic_locked ?? false) || ($survey->is_institution_locked ?? false))
                <!-- Locked survey - different button style -->
                <button
                    class="px-4 py-1 rounded-full font-bold text-white cursor-pointer transition
                           bg-gray-400 hover:bg-gray-500 focus:outline-none min-w-[100px] h-8 flex items-center justify-center"
                           
                    type="button"
                    x-data="{ loading: false }"
                    x-on:click="
                        loading = true;
                        $wire.set('modalSurveyId', null).then(() => {
                            $wire.set('modalSurveyId', {{ $survey->id }});
                            $nextTick(() => {
                                $dispatch('open-modal', { name: 'surveyDetailModal' });
                                loading = false;
                            });
                        }).catch(() => {
                            loading = false;
                        });
                    "
                    
                >
                    <span x-show="!loading">View Details</span>
                    <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            @else
                <!-- Unlocked survey - normal button style -->
                <button
                    class="px-4 py-1 rounded-full font-bold text-white cursor-pointer transition
                           bg-[#03b8ff] hover:bg-[#0295d1] hover:shadow-lg focus:outline-none min-w-[100px] h-8 flex items-center justify-center"
                           
                    type="button"
                    x-data="{ loading: false }"
                    x-on:click="
                        loading = true;
                        $wire.set('modalSurveyId', null).then(() => {
                            $wire.set('modalSurveyId', {{ $survey->id }});
                            $nextTick(() => {
                                $dispatch('open-modal', { name: 'surveyDetailModal' });
                                loading = false;
                            });
                        }).catch(() => {
                            loading = false;
                        });
                    "
                    
                >
                    <span x-show="!loading">Read More</span>
                    <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Institution Only Indicator & Lock Status (Centered) --}}
        <div class="flex-grow text-center">
            @if($survey->is_response_limit_locked ?? false)
                <span class="text-xs font-bold rounded-full px-3 py-1 bg-orange-500 text-white">Max Responses</span>
            @elseif($survey->is_expired_locked ?? false)
                <span class="text-xs font-bold rounded-full px-3 py-1 bg-red-500 text-white">Expired</span>
            @elseif($survey->is_demographic_locked ?? false)
                <span class="text-xs font-bold rounded-full px-3 py-1 bg-gray-500 text-white">Locked</span>
            @elseif($survey->is_institution_locked ?? false)
                <span class="text-xs font-bold rounded-full px-3 py-1 bg-gray-500 text-white">Institution Only</span>
            @elseif($survey->is_institution_only)
                <span class="text-xs font-bold rounded-full px-3 py-1 bg-yellow-500 text-white">Institution Only</span>
            @endif
        </div>
        
        {{-- Survey Type (Moved here from Info Section) --}}
        <div class="ml-auto">
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $survey->type === 'advanced' ? 'bg-purple-200 text-purple-800' : 'bg-blue-200 text-blue-800' }}">
                {{ ucfirst($survey->type ?? 'Basic') }}
            </span>
        </div>
    </div>

    
</div>