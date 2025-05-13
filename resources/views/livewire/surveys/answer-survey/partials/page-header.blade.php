@if($page->title)
    <h2 class="text-2xl font-semibold mb-2">{{ $page->title }}</h2>
@endif
@if($page->subtitle)
    <div class="text-gray-500 mb-4">{{ $page->subtitle }}</div>
@endif
<hr class="mb-6 border-gray-300">
