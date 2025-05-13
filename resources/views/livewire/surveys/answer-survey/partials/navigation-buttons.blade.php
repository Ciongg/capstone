<div class="flex justify-between mt-8">
    @if ($isFirstPage)
        <span></span>
    @else
        <button
            type="button"
            class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
            wire:click="$set('currentPage', {{ $currentPage - 1 }})"
        >Previous</button>
    @endif

    @if ($isLastPage)
        <button
            type="submit"
            class="cursor-pointer px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition ml-auto"
            wire:click="$set('navAction', 'submit')"
        >Submit</button>
    @else
        <button
            type="submit"
            class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition ml-auto"
            wire:click="$set('navAction', 'next')"
        >Next</button>
    @endif
</div>
