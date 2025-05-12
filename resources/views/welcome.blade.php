@extends('components.layouts.app')

@section('content')
<main class="container mx-auto py-12 px-4 text-center">
    <h1 class="text-4xl font-bold text-gray-800 mb-4">Powering Research<br>Rewarding Participants</h1>
    <a href="{{ route('register') }}" class="mt-4 inline-block bg-[#00BBFF] hover:bg-[#00A3DC] text-white font-semibold px-6 py-2 rounded">
        Register
    </a>



    <section class="mt-16 bg-gray-100 py-12 px-4">
        <h2 class="text-2xl font-semibold mb-8">Getting Started is Easy</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-gray-700">
            <div>
                <div class="text-5xl text-[#00BBFF] mb-2">👤</div>
                <h3 class="text-lg font-semibold">Login</h3>
                <p class="text-sm mt-2">If you don’t have an account, create one. Use school email for educational access.</p>
                <a href="{{ route('register') }}" class="text-[#00BBFF] text-sm mt-1 inline-block">CREATE ACCOUNT</a>
            </div>
            <div>
                <div class="text-5xl text-[#00BBFF] mb-2">📋</div>
                <h3 class="text-lg font-semibold">Answer Applicable Survey</h3>
                <p class="text-sm mt-2">Choose a survey that matches your profile and eligibility requirements.</p>
            </div>
            <div>
                <div class="text-5xl text-[#00BBFF] mb-2">🎁</div>
                <h3 class="text-lg font-semibold">Earn points</h3>
                <p class="text-sm mt-2">Rack up points by answering surveys and redeem them for rewards.</p>
                <a href="/rewards" class="text-[#00BBFF] text-sm mt-1 inline-block">VIEW REWARDS</a>
            </div>
        </div>
    </section>
</main>

@endsection
