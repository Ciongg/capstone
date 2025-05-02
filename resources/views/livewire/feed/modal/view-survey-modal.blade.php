<div>
    <h1>test</h1>
    <div class="mb-2">
        <span class="font-bold">Title:</span> {{ $survey->title }}
    </div>
    <div class="mb-2">
        <span class="font-bold">Type:</span> {{ $survey->type ?? 'N/A' }}
    </div>
    <div class="mb-2">
        <span class="font-bold">Description:</span> {{ $survey->description }}
    </div>


        <a href="{{ route('surveys.answer', $survey->id) }}"
                wire:navigate
               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Answer
        </a>
</div>
