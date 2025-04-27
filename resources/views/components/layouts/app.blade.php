<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Survey Builder' }}</title>

    <!-- Include Alpine.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>

    @livewireStyles  <!-- Required Livewire styles -->
    
    <!-- Add your CSS (e.g., Tailwind or custom styles) -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-blue-500 p-4 text-white">
        <div class="container mx-auto">
            <a href="/" class="font-bold text-xl">Formigo</a>
            <a href="/" class="font-bold text-xl">Feed</a>
            <a href="/" class="font-bold text-xl">Create Survey</a>
            <a href="/" class="font-bold text-xl">Rewards</a>
            <!-- Add other links or navbar items -->
        </div>
    </nav>

    <!-- Main Content (Dynamic Content goes here) -->
    <main class="container mx-auto p-6">
        @yield('content') <!-- This is where the content will be injected -->
    </main>

  

    @livewireScripts  <!-- Required Livewire scripts -->
</body>
</html>
