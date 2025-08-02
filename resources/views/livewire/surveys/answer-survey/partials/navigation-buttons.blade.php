<div class="flex justify-between mt-8">
    @if(!$isFirstPage)
    <button
        type="button"
        class="px-5 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 flex items-center justify-center min-w-[100px]"
        wire:click="goToPreviousPage"
        wire:loading.attr="disabled"
        wire:target="goToPreviousPage"
    >
        <span wire:loading.remove wire:target="goToPreviousPage" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back
        </span>
        <svg wire:loading wire:target="goToPreviousPage" class="animate-spin h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </button>
    @else
    <div></div> <!-- Empty div for spacing when there's no back button -->
    @endif

    <button
        type="button"
        x-on:click="navAction = '{{ $isLastPage ? 'submit' : 'next' }}'; $wire.set('navAction', navAction); $wire.submit();"
        class="{{ $isLastPage ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-500 hover:bg-blue-600' }} px-5 py-2 text-white rounded flex items-center justify-center min-w-[100px]"
        wire:loading.attr="disabled"
        wire:target="submit"
    >
        <span wire:loading.remove wire:target="submit" class="flex items-center">
            @if($isLastPage)
                <i class="fas fa-check-circle text-lg mr-2"></i> Submit
            @else
                Next
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 ml-2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            @endif
        </span>
        <svg wire:loading wire:target="submit" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </button>
</div>
