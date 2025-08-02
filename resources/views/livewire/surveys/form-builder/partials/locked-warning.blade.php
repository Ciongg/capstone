@if(isset($survey) && $survey->is_locked)
<div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-lg font-medium text-red-800">This survey has been locked by an administrator</h3>
            <div class="mt-1 text-sm text-red-700">
                <p class="font-bold">Reason: {{ $survey->lock_reason ?? 'No specific reason provided.' }}</p>
                <p class="mt-2">
                    When a survey is locked, you cannot edit its content, add questions, or make any changes.
                    You can still preview the survey and view responses if available.
                </p>
                <p class="mt-2">If you believe this is incorrect, please submit a support ticket of request type survey lock appeal, using Survey UUID: <span class="font-bold">{{$survey->uuid}} </span></p>
            </div>
        </div>
    </div>
</div>
@endif