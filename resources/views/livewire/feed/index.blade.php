{{-- filepath: resources\views\livewire\feed\index.blade.php --}}
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Available Surveys</h1>
    @forelse($surveys as $survey)
        <div class="bg-white shadow rounded p-4 mb-4 flex justify-between items-center">
            <div>
                <div class="text-lg font-semibold">{{ $survey->title }}</div>
                <div class="text-gray-600">{{ $survey->description }}</div>
            </div>
            <a href="{{ route('surveys.answer', $survey->id) }}"
               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Answer
            </a>
        </div>
    @empty
        <div class="text-gray-500">No published surveys available.</div>
    @endforelse
</div>
