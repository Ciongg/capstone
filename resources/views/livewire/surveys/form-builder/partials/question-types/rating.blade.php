<div class="mt-2 flex items-center space-x-2">
    <label class="text-gray-600">Stars:</label>
    <select
        wire:model="ratingStars.{{ $question->id }}"
        wire:change="updateRatingStars({{ $question->id }})"
        class="border rounded px-2 py-1"
        style="width: auto;"
    >
        @for($i = 2; $i <= 10; $i++)
            <option value="{{ $i }}">{{ $i }}</option>
        @endfor
    </select>
    <span class="ml-2 text-yellow-400 text-[2rem]">
        @for($i = 0; $i < ($ratingStars[$question->id] ?? 5); $i++)
            ★
        @endfor
    </span>
</div>
