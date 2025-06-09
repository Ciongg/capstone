<textarea 
    wire:model="answers.{{ $question->id }}"
    class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm"
    rows="4"
></textarea>
@error('answers.' . $question->id) <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror