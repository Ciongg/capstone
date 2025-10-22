<x-layouts.app>
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto text-center px-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Success Icon -->
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                Survey Already Completed
            </h1>

            <!-- Message -->
            <p class="text-gray-600 mb-6">
                You have already submitted a response to <strong>"{{ $survey->title }}"</strong>. 
                Thank you for your participation!
            </p>

            <!-- Response Date -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Submitted:</span> {{ $existingResponse->created_at->format('M d, Y \a\t g:i A') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('feed.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Back to Feed
                </a>
                
                <a href="{{ route('profile.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 text-xs text-gray-500">
                <p>Each user can only submit one response per survey to maintain data integrity.</p>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>