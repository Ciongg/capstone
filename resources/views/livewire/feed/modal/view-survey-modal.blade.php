<div class="flex space-x-6 p-4 h-full">

    <!-- Left Column -->
    <div class="flex flex-col space-y-4 w-1/2 h-full">
        <!-- Top: User Info -->
        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg shadow-sm">
            {{-- Use the profile photo URL --}}
            <img src="{{ $survey->user->profile_photo_url }}" alt="{{ $survey->user->name ?? 'Unknown User' }}" class="w-12 h-12 rounded-full object-cover">
            <div>
                <div class="font-semibold text-gray-800">{{ $survey->user->name ?? 'Unknown User' }}</div>
                <div class="text-xs text-gray-500">
                    Created {{ $survey->created_at ? $survey->created_at->diffForHumans() : 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Bottom: Survey Image -->
        <div class="flex-1 flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden shadow-sm min-h-0">
            @php
                $imageUrl = $survey->image_path ? asset('storage/' . $survey->image_path) : 'https://placehold.co/400x300?text=Survey+Image';
            @endphp
            <img src="{{ $imageUrl }}" alt="Survey Image" class="object-contain max-w-full max-h-full">
        </div>
    </div>

    <!-- Right Column -->
    <div class="flex flex-col space-y-4 w-1/2 h-full">
        <!-- Top: Type, Participants & Points -->
        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg shadow-sm">
            {{-- Survey Type --}}
            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $survey->type === 'advanced' ? 'bg-purple-200 text-purple-800' : 'bg-blue-200 text-blue-800' }}">
                {{ ucfirst($survey->type ?? 'Basic') }}
            </span>

            {{-- Participant Count --}}
            <div class="flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>{{ $survey->responses()->count() }}/{{ $survey->target_respondents ?? '∞' }}</span>
            </div>

            {{-- Points Allocated --}}
            <div class="flex items-center bg-gradient-to-r from-red-600 via-orange-400 to-yellow-300 px-3 py-1 rounded-full text-white">
                <span class="font-extrabold drop-shadow">{{ $survey->points_allocated ?? 0 }}</span>
                <svg class="w-5 h-5 text-white ml-1" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                </svg>
            </div>
        </div>

        <!-- Middle 1: Tags -->
        <div class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg shadow-sm min-h-[40px]">
            @php
                // Get the IDs of the tags associated with the currently logged-in user
                // Ensure user is logged in and tags relationship is loaded or accessible
                $userTagIds = auth()->check() ? auth()->user()->tags()->pluck('tags.id')->toArray() : [];
                // Get institution tag IDs for the user
                $userInstitutionTagIds = auth()->check() ? auth()->user()->institutionTags()->pluck('institution_tags.id')->toArray() : [];
            @endphp

            {{-- Regular Tags --}}
            @forelse ($survey->tags->take(5) as $tag)
                @php
                    // Check if the current survey tag ID exists in the user's tag IDs
                    $matchesUserTag = in_array($tag->id, $userTagIds);
                @endphp
                 {{-- Conditionally change background and text color based on match --}}
                 <span @class([
                    'px-3 py-1 text-xs font-medium rounded-full',
                    'bg-green-200 text-green-800' => $matchesUserTag, // Green if matches
                    'bg-gray-200 text-gray-700' => !$matchesUserTag, // Gray if not
                 ])>
                    {{ $tag->name }}
                 </span>
            @empty
                @if($survey->institutionTags->isEmpty())
                    <span class="text-xs text-gray-400 italic">No tags specified</span>
                @endif
            @endforelse

            {{-- Institution Tags - updated to match regular tags design exactly --}}
            @foreach ($survey->institutionTags as $tag)
                @php
                    // Check if the current institution tag ID exists in the user's institution tag IDs
                    $matchesUserInstitutionTag = in_array($tag->id, $userInstitutionTagIds);
                @endphp
                 {{-- Match the style of regular tags exactly --}}
                 <span @class([
                    'px-3 py-1 text-xs font-medium rounded-full',
                    'bg-green-200 text-green-800' => $matchesUserInstitutionTag, // Green if matches
                    'bg-gray-200 text-gray-700' => !$matchesUserInstitutionTag, // Gray if not
                 ])>
                    {{ $tag->name }}
                 </span>
            @endforeach
        </div>

        <!-- Middle 2: Title & Description -->
        <div class="flex flex-col space-y-2 p-3 bg-gray-50 rounded-lg shadow-sm flex-grow min-h-0">
            <h3 class="text-lg font-bold text-gray-900 flex-shrink-0">{{ $survey->title }}</h3>
            <p class="text-sm text-gray-600 flex-grow overflow-y-auto whitespace-pre-wrap">{{ $survey->description }}</p>
        </div>

        <!-- Bottom: Answer Button -->
        <div class="flex justify-end flex-shrink-0">
            @if($survey->is_demographic_locked ?? false)
                <!-- Locked due to demographics -->
                <button 
                    class="flex items-center px-6 py-2 bg-gray-300 text-gray-600 font-semibold rounded-lg cursor-not-allowed"
                    disabled
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Demographic Mismatch
                </button>
            @elseif($survey->is_institution_locked ?? false)
                <!-- Locked due to institution mismatch -->
                <button 
                    class="flex items-center px-6 py-2 bg-gray-300 text-gray-600 font-semibold rounded-lg cursor-not-allowed"
                    disabled
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Institution Restricted
                </button>
            @else
                <!-- Unlocked survey -->
                <a href="{{ route('surveys.answer', $survey->id) }}"
                  
                   class="px-6 py-2 bg-[#03b8ff] text-white font-bold rounded-lg hover:bg-[#0295d1] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Answer Survey
                </a>
            @endif
        </div>
    </div>

</div>
