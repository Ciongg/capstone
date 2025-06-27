<div class="mt-2 flex items-center space-x-2">
    <label class="text-gray-600">Stars:</label>
    <select
        wire:model="ratingStars.{{ $question->id }}"
        wire:change="updateRatingStars({{ $question->id }})"
        class="border rounded px-2 py-1"
        style="width: auto;"
        wire:loading.attr="disabled"
        wire:target="updateRatingStars({{ $question->id }})"
    >
        @for($i = 2; $i <= 10; $i++)
            <option value="{{ $i }}">{{ $i }}</option>
        @endfor
    </select>
    <span class="ml-2 text-yellow-400 text-[2rem] flex items-center space-x-1">
        <span wire:loading.remove wire:target="updateRatingStars({{ $question->id }})">
            @for($i = 0; $i < ($ratingStars[$question->id] ?? 5); $i++)
                ★
            @endfor
        </span>
        <span wire:loading wire:target="updateRatingStars({{ $question->id }})" class="flex items-center space-x-1">
           <svg class="animate-spin h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
</svg>

            
        </span>
    </span>
</div>
