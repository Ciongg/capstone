<div class="bg-white shadow-md rounded-lg p-8">
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Survey Title -->
    <h1 class="text-3xl font-bold mb-4 text-justify">{{ $survey->title }}</h1>

    <!-- Survey Description -->
    <p class="text-gray-600 text-base leading-relaxed tracking-wide text-justify mb-8">
        {{ $survey->description }}
    </p>



