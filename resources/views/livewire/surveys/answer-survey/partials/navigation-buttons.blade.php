<div 
    x-data="{ isSubmitting: false }"
    x-init="
        window.addEventListener('scrollToTop', () => { 
            window.scrollTo({ top: 0, behavior: 'smooth' }); 
        });
        $wire.on('submissionStarted', () => { isSubmitting = true; });
        $wire.on('submissionEnded', () => { isSubmitting = false; });
    "
    class="flex justify-between mt-8"
>
    @if(!$isFirstPage)
    <button
        type="button"
        class="px-5 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 flex items-center justify-center min-w-[100px] disabled:opacity-50 disabled:cursor-not-allowed"
        wire:click="goToPreviousPage"
        x-on:click="$dispatch('scrollToTop')"
        :disabled="isSubmitting"
    >
        <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back
        </span>
    </button>
    @else
    <div></div>
    @endif

    <button
        type="button"
        x-on:click="isSubmitting = true; navAction = '{{ $isLastPage ? 'submit' : 'next' }}'; $wire.set('navAction', navAction); $wire.submit();" 
        class="{{ $isLastPage ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-500 hover:bg-blue-600' }} px-5 py-2 text-white rounded flex items-center justify-center min-w-[140px] disabled:opacity-50 disabled:cursor-not-allowed transition-opacity"
        :disabled="isSubmitting"
    >
        <template x-if="!isSubmitting">
            <span class="flex items-center">
                @if($isLastPage)
                    <i class="fas fa-check-circle text-lg mr-2"></i> Submit
                @else
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 ml-2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                @endif
            </span>
        </template>
        <template x-if="isSubmitting">
            <span class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ $isLastPage ? 'Submitting...' : 'Loading...' }}
            </span>
        </template>
    </button>
</div>
