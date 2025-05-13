{{-- Individual Survey Card --}}
<div wire:key="survey-card-{{ $survey->id }}" class="relative bg-white shadow-lg rounded-xl p-0 flex flex-col min-h-[500px]">
    {{-- Header --}}
    <div class="w-full px-4 py-3 rounded-t-xl bg-gray-100 border-b border-gray-100 flex-shrink-0">
        <div class="flex items-center mb-2">
            <img src="{{ $survey->user->profile_photo_url }}" alt="{{ $survey->user->name ?? 'User' }}" class="w-10 h-10 rounded-full object-cover mr-3">
            <span class="text-base font-semibold text-gray-800 truncate mr-4">{{ $survey->user->name ?? 'User' }}</span>
            <div class="flex-1"></div>
            <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full">
                <span class="font-bold text-white drop-shadow">{{ $survey->points_allocated ?? 0 }}</span>
                <svg class="w-6 h-6 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                </svg>
            </div>
        </div>
        <div class="w-full">
            <span class="text-sm font-semibold text-left truncate block">{{ $survey->title }}</span>
        </div>
    </div>
    
    {{-- Image --}}
    <div class="w-full flex-grow mt-4 flex items-center justify-center mb-2 relative px-4 min-h-0">
        @if($survey->image_path)
            @php $imageUrl = asset('storage/' . $survey->image_path); @endphp
            <button @click="fullscreenImageSrc = '{{ $imageUrl }}'" class="cursor-pointer w-full h-full flex items-center justify-center">
                <img src="{{ $imageUrl }}" alt="Survey image for {{ $survey->title }}" class="rounded-lg object-contain max-w-full max-h-[340px]" />
            </button>
        @else
            <div class="w-full h-full max-h-[340px] bg-gray-200 flex items-center justify-center rounded-lg"><span class="text-gray-500 text-sm">no image</span></div>
        @endif
    </div>

    {{-- Tags Section --}}
    <div class="w-full px-4 mb-3 flex-shrink-0">
        <div class="flex flex-wrap gap-2 justify-center min-h-[36px] items-center">
            @if($survey->tags->isEmpty())
                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
            @else
                @php $tagsToShow = $survey->tags->take(3); @endphp
                @foreach($tagsToShow as $tag)
                    <button
                        wire:click="filterByTag({{ $tag->id }})"
                        wire:key="survey-{{ $survey->id }}-tag-{{ $tag->id }}"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        class="px-3 py-2 text-xs font-semibold rounded-full shadow-md overflow-hidden whitespace-nowrap max-w-[100px] text-ellipsis transition-all
                               {{ in_array($tag->id, $activeFilters['tags']) ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}"
                    >
                        {{ $tag->name }}
                    </button>
                @endforeach
                @if($tagsToShow->count() < 3)
                    @for($i = $tagsToShow->count(); $i < 3; $i++)
                        <span class="block w-24 h-[36px] bg-gray-100 rounded-full shadow-md">&nbsp;</span>
                    @endfor
                @endif
            @endif
        </div>
    </div>

    {{-- Read More Button & Topic Badge --}}
    <div class="w-full flex justify-between items-center mt-auto mb-4 px-4 flex-shrink-0">
        <button
            @click="
                $wire.set('modalSurveyId', null).then(() => {
                    $wire.set('modalSurveyId', {{ $survey->id }});
                    $nextTick(() => $dispatch('open-modal', { name: 'surveyDetailModal' }));
                })
            "
            class="px-4 py-1 rounded-full font-bold text-white cursor-pointer transition
                   bg-[#03b8ff] hover:bg-[#0295d1] hover:shadow-lg focus:outline-none"
            type="button"
        >
            Read More
        </button>
        @if($survey->topic)
            <span class="text-xs text-gray-500">{{ $survey->topic->name }}</span>
        @endif
    </div>
</div>