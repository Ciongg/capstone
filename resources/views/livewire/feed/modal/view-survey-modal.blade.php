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
                <svg class="w-5 h-5 text-white mr-1" fill="white" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polygon points="12 2 22 9 16 22 8 22 2 9 12 2" />
                    <line x1="12" y1="2" x2="12" y2="22" />
                    <line x1="2" y1="9" x2="22" y2="9" />
                    <line x1="8" y1="22" x2="16" y2="22" />
                </svg>
                <span class="font-extrabold drop-shadow">{{ $survey->points_allocated ?? 0 }}</span>
            </div>
        </div>

        <!-- Middle 1: Tags -->
        <div class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg shadow-sm min-h-[40px]">
            @php
                // Get the IDs of the tags associated with the currently logged-in user
                // Ensure user is logged in and tags relationship is loaded or accessible
                $userTagIds = auth()->check() ? auth()->user()->tags()->pluck('tags.id')->toArray() : [];
            @endphp

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
                 <span class="text-xs text-gray-400 italic">No tags specified</span>
            @endforelse
        </div>

        <!-- Middle 2: Title & Description -->
        <div class="flex flex-col space-y-2 p-3 bg-gray-50 rounded-lg shadow-sm flex-grow min-h-0">
            <h3 class="text-lg font-bold text-gray-900 flex-shrink-0">{{ $survey->title }}</h3>
            <p class="text-sm text-gray-600 flex-grow overflow-y-auto whitespace-pre-wrap">{{ $survey->description }}</p>
        </div>

        <!-- Bottom: Answer Button -->
        <div class="flex justify-end flex-shrink-0">
            <a href="{{ route('surveys.answer', $survey->id) }}"
               wire:navigate
               class="px-6 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Answer Survey
            </a>
        </div>
    </div>

</div>
