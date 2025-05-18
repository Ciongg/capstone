@props(['title', 'name'])

<div
    x-data="{ show: false, name: '{{$name}}' }"
    x-show="show"
    x-on:open-modal.window="if ($event.detail.name === name) show = true"
    {{-- Listen for both specific and generic close events --}}
    x-on:close-modal-{{ Str::slug($name) }}.window="show = false; $dispatch('close');"
    x-on:close-modal.window="if ($event.detail.name === name) { show = false; $dispatch('close'); }"
    {{-- Handle internal close actions --}}
    x-on:keydown.escape.window="if (show) { show = false; $dispatch('close'); }"
    style="display: none"
    x-transition
    class="fixed z-50 inset-0">

    {{-- Backdrop --}}
    <div x-on:click="show = false; $dispatch('close');" class="fixed inset-0 bg-gray-900 opacity-20"></div>

    {{-- Modal Panel --}}
    <div class="bg-white rounded-lg m-auto fixed inset-0 max-w-3xl max-h-[650px] p-2 flex flex-col">
        <div class="flex justify-between items-center p-4 border-b"> {{-- Container for title and close button --}}
            @if(isset($title))
                <h1 class="text-2xl font-bold">{{$title}}</h1>
            @else
                <div></div> {{-- Placeholder to keep close button to the right if no title --}}
            @endif
            {{-- Close button moved to top right --}}
            <button
                type="button"
                @click="show = false; $dispatch('close');"
                class="text-gray-400 hover:text-gray-600 transition"
                title="Close modal"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4"> {{-- Added padding to slot --}}
            {{ $slot }}
        </div>

      
    </div>
</div>