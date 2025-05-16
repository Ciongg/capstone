<div class="flex justify-between items-center mb-2">
    <div>
        @if($page->title)
            <h2 class="text-2xl font-semibold">{{ $page->title }}</h2>
        @endif
    </div>
    
    {{-- Translate button positioned to the right of the title --}}
    <x-translate-button />
</div>

@if($page->subtitle)
    <div class="text-gray-600 mb-4">{{ $page->subtitle }}</div>
@endif
<hr class="mb-6 border-gray-300">