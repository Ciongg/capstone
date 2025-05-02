<div class="max-w-3xl mx-auto py-10" x-data="{ tab: 'about' }">
    <!-- Profile Header -->
    <div class="flex flex-col items-center bg-white rounded-xl shadow p-8 mb-8">
        <!-- Profile Image (soon editable) -->
        <div class="relative mb-4">
            <span class="w-28 h-28 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-5xl font-bold overflow-hidden">
                <!-- Placeholder profile image -->
                <svg class="w-20 h-20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="8" />
                    <path d="M16 22c0-2.21-3.58-6-8-6s-8 3.79-8 6" />
                </svg>
            </span>
            <!-- Edit icon (future) -->
            <span class="absolute bottom-2 right-2 bg-blue-500 text-white rounded-full p-1 cursor-pointer" title="Edit Photo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6v2H7v-2z"/>
                </svg>
            </span>
        </div>
        <!-- User Name -->
        <div class="text-2xl font-bold mb-1">{{ $user?->name ?? 'Unknown User' }}</div>
        <!-- User Type -->
        <div class="text-blue-500 font-semibold mb-2 capitalize">{{ $user?->type ?? 'User' }}</div>
    </div>

    <!-- Profile Navigation Tabs -->
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex justify-center space-x-8 mb-6">
            <button
                class="px-4 py-2 font-semibold rounded transition"
                :class="tab === 'about' ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-600'"
                @click="tab = 'about'"
            >
                About
            </button>
            <button
                class="px-4 py-2 font-semibold rounded transition"
                :class="tab === 'surveys' ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-600'"
                @click="tab = 'surveys'"
            >
                My Surveys
            </button>
            <button
                class="px-4 py-2 font-semibold rounded transition"
                :class="tab === 'history' ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-600'"
                @click="tab = 'history'"
            >
                Survey History
            </button>
        </div>
        <!-- Dynamic Content Container -->
        <div class="min-h-[150px]">
            <div x-show="tab === 'about'">
                <livewire:profile.view-about :user="$user" />
            </div>
            <div x-show="tab === 'surveys'">
                <livewire:surveys.form-index :user="$user" />
            </div>
            <div x-show="tab === 'history'">
                <livewire:profile.view-history :user="$user" />
            </div>
        </div>
    </div>
</div>
