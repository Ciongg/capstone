<div class="space-y-8">
    <!-- Personal Information -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">Personal Information</h2>
        <div class="space-y-2">
            <div><span class="font-semibold">Name:</span> {{ $user->name }}</div>
            <div><span class="font-semibold">Email:</span> {{ $user->email }}</div>
            <div><span class="font-semibold">Type:</span> {{ ucfirst($user->type ?? 'User') }}</div>
            <div><span class="font-semibold">Points:</span> {{ $user->points ?? 0 }}</div>
        </div>
    </div>

    <!-- Demographic Tags -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">Demographic Tags</h2>
        <form wire:submit.prevent="saveTags" class="space-y-4">
            @foreach($tagCategories as $category)
                <div>
                    <label class="block font-semibold mb-1">{{ $category->name }}</label>
                    <select wire:model.defer="selectedTags.{{ $category->id }}" class="w-full border rounded px-3 py-2">
                        <option value="">Select {{ $category->name }}</option>
                        @foreach($category->tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Demographics</button>
        </form>
        @if (session()->has('tags_saved'))
            <div class="text-green-600 mt-2">{{ session('tags_saved') }}</div>
        @endif
    </div>
</div>
