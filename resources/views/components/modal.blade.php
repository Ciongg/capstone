@props(['title', 'name'])

<div
    x-data="{ show: false, name: '{{$name}}' }"
    x-show="show"
    x-on:open-modal.window="if ($event.detail.name === name) show = true"
    {{-- Listen for a specific close event for this modal instance if needed from external Alpine components --}}
    x-on:close-modal-{{ Str::slug($name) }}.window="show = false; $dispatch('close');"
    {{-- Handle internal close actions --}}
    x-on:keydown.escape.window="if (show) { show = false; $dispatch('close'); }"
    style="display: none"
    x-transition
    class="fixed z-50 inset-0">

    {{-- Backdrop --}}
    <div x-on:click="show = false; $dispatch('close');" class="fixed inset-0 bg-gray-900 opacity-20"></div>

    {{-- Modal Panel --}}
    <div class="bg-white rounded-lg m-auto fixed inset-0 max-w-3xl max-h-[600px] p-2 flex flex-col">
        @if(isset($title))
            <div class="p-4 border-b"> {{-- Added padding and border for better separation --}}
                <h1 class="text-2xl font-bold">{{$title}}</h1>
            </div>
        @endif

        <div class="flex-1 overflow-y-auto p-4"> {{-- Added padding to slot --}}
            {{ $slot }}
        </div>

        {{-- Optional: Add a close button inside the panel for accessibility/usability --}}
        <div class="p-4 border-t text-right">
            <button
                type="button"
                @click="show = false; $dispatch('close');"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
            >
                Close
            </button>
        </div>
    </div>
</div>