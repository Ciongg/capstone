@if($isPreview && auth()->user()->type !== 'super_admin')
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded shadow-sm">
        <div class="flex justify-between items-center">
            <div>
                <span class="font-semibold text-blue-800">Preview Mode</span>
                <p class="text-sm text-blue-600">This is a preview of your survey. Responses will not be recorded.</p>
            </div>
            <a href="{{ route('surveys.create', $survey->uuid) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded"
               onclick="event.preventDefault(); window.location.href='{{ route('surveys.create', $survey->uuid) }}';">
                &larr; Back to Editor
            </a>
        </div>
    </div>
@elseif($isPreview && auth()->user()->type === 'super_admin')
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded shadow-sm">
        <div class="flex justify-between items-center">
            <div>
                <span class="font-semibold text-blue-800">Admin Preview Mode</span>
                <p class="text-sm text-blue-600">This is a preview of the survey. Responses will not be recorded.</p>
            </div>

             <a href="{{ route('admin.surveys.index') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded"
               onclick="event.preventDefault(); window.location.href='{{ route('admin.surveys.index') }}';">
                &larr; Back to Surveys
            </a>
        </div>
    </div>
@endif
