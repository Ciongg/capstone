@extends('components.layouts.app')

@section('content')
<main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-gray-100 rounded-xl p-8 mb-4 shadow-md">
        <h1 class="text-4xl font-semibold text-gray-800 mb-6 text-center">Privacy Policy</h1>
        <p class="text-gray-700 mb-6 text-center">
            This Privacy Policy explains how Formigo collects, uses, and protects your personal information when you use our platform.
        </p>
        <div class="space-y-6">
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Information We Collect</h2>
                <p class="text-gray-600">
                    We collect information you provide during registration, survey participation, and when you interact with our services. This may include your name, email address, institution, and survey responses.
                </p>
            </section>
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">How We Use Your Information</h2>
                <p class="text-gray-600">
                    Your information is used to provide and improve our services, personalize your experience, and ensure the integrity of survey results. We do not sell your personal data to third parties.
                </p>
            </section>
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Data Security</h2>
                <p class="text-gray-600">
                    We implement industry-standard security measures to protect your data. Access to your information is restricted to authorized personnel only.
                </p>
            </section>
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Your Rights</h2>
                <p class="text-gray-600">
                    You may request access to, correction of, or deletion of your personal information at any time by contacting support.
                </p>
            </section>
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Contact Us</h2>
                <p class="text-gray-600">
                    For questions about this Privacy Policy, please contact us at support@formigo.com.
                </p>
            </section>
        </div>
    </div>
</main>
@endsection
