<div class="mt-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- New Layout: User Stats and Points Display -->
    <div class="bg-white p-6"> 
        <div class="flex flex-col md:flex-row items-center md:items-start justify-between md:space-x-8">
            <!-- Left Side: Header and Points Display -->
            <div class="md:w-1/2 lg:w-2/3 flex flex-col items-center md:items-start">
                <h1 class="text-4xl font-bold text-gray-600 text-center md:text-left mb-4">Redeem Rewards</h1>
                
                <div class="flex flex-col justify-between items-center md:items-start mb-6"> 
                    <div class="flex items-center">
                        <span class="text-7xl font-bold text-[#FFB349]">{{ $userPoints }}</span>
                        <svg class="w-16 h-16 text-[#FFB349] ml-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </svg>
                    </div>
                    <p class="text-xl text-gray-600 mt-2 text-center">Available Points</p> 
                    <p class="text-gray-600 mt-4 text-center md:text-left">Earn more points by completing surveys!</p> 
                </div>
            </div>
            
            <!-- Right Side: User Profile Info - Aligned with header -->
            <div class="flex flex-col items-center md:items-end md:w-1/2 lg:w-1/3 mb-6 md:mb-0">
                <a href="{{ route('profile.index') }}" class="group flex flex-col items-center"> 
                    <!-- User Profile Picture -->
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-200 group-hover:border-blue-300 transition-all">
                        <img src="{{ $user?->profile_photo_url }}" alt="{{ $user?->name }}" class="w-full h-full object-cover">
                    </div>
                    
                    <!-- User Stats -->
                    <div class="mt-3 text-center"> 
                        <p class="text-lg font-semibold text-gray-600">{{ $user?->name }}</p>
                        
                        <!-- XP Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1 dark:bg-gray-700">
                            <div class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ $levelProgress }}%"></div> 
                        </div>
                        
                        <!-- Level Display -->
                        @if($userLevel >= 30)
                            <p class="text-xs text-gray-500 mb-2">Level {{ $userLevel }} Maxed • {{ $userExperience }} XP</p>
                        @else
                            <p class="text-xs text-gray-500 mb-2">Level {{ $userLevel }} • {{ $userExperience }}/{{ $xpForNextLevel }} XP</p>
                        @endif
                        <!-- User Rank Badge with inline styles based on rank -->
                        <div class="inline-block mb-2">
                            @php
                                $rankStyles = [
                                    'silver' => [
                                        'bg' => 'linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 50%, #A8A8A8 100%)',
                                        'text' => '#333333',
                                    ],
                                    'gold' => [
                                        'bg' => 'linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%)',
                                        'text' => '#ffffff',
                                    ],
                                    'diamond' => [
                                        'bg' => 'linear-gradient(135deg, #A1FFFF 0%, #3AA8C1 50%, #0078A8 100%)',
                                        'text' => '#ffffff',
                                    ]
                                ];
                                $userRank = strtolower($user?->rank ?: 'silver');
                                $currentRankStyle = $rankStyles[$userRank] ?? $rankStyles['silver'];
                            @endphp
                            <div class="text-sm font-medium py-1 px-3 rounded-full" 
                                 style="background: {{ $currentRankStyle['bg'] }}; color: {{ $currentRankStyle['text'] }}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);">
                                {{ ucfirst($user?->rank ?: 'silver') }}
                            </div>
                        </div>
                        
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 py-2 border-gray-300"></div>

    <!-- Tabs Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <div class="flex -mb-px w-full">
                <button 
                    wire:click="setActiveTab('system')"  
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition
                          {{ $activeTab === 'system' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300' }}"
                >
                    System Rewards
                </button>
                <button 
                    wire:click="setActiveTab('voucher')" 
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition
                          {{ $activeTab === 'voucher' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300' }}"
                >
                    Gift Vouchers
                </button>
             
            </div>
        </div>

        <!-- Flash Messages for Redemption -->
        <div class="mb-4">
            <!-- We'll now handle these with SweetAlert -->
        </div>
    

        <!-- System Rewards Tab -->
        <div class="{{ $activeTab === 'system' ? '' : 'hidden' }} mt-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-2 sm:px-4">
                @forelse($systemRewards as $reward)
                    @include('livewire.rewards.partials.reward-card', ['reward' => $reward])
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No system rewards available at this time.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Vouchers Tab -->
        <div class="{{ $activeTab === 'voucher' ? '' : 'hidden' }} mt-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-2 sm:px-4">
                @forelse($voucherRewards as $reward)
                    @include('livewire.rewards.partials.reward-card', ['reward' => $reward])
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No voucher rewards available at this time.</p>
                    </div>
                @endforelse
            </div>
        </div>

       
    </div>

    <!-- Reward Redemption Modal -->
    <x-modal name="reward-redeem-modal" title="Redeem Reward">
        @if($selectedReward)
            @livewire('rewards.modal.reward-redeem-modal', ['reward' => $selectedReward], key('reward-redeem-modal-' . $selectedReward->id))
        @else
            <!-- Simple Loading Indicator -->
            <div class="flex flex-col justify-center items-center h-full p-6">
                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600 text-lg">Loading reward details...</p>
            </div>
        @endif
    </x-modal>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Add event listeners for redemption success and error
    window.addEventListener('redemption-success', function (event) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: event.detail || 'Reward redeemed successfully!',
            timer: 1800,
            showConfirmButton: false,
        });
    });
    
    window.addEventListener('redemptionError', function (event) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: event.detail || 'An error occurred during redemption.',
            timer: 3000,
            showConfirmButton: true,
        });
    });
    
    window.addEventListener('redeem_success', function (event) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: event.detail || 'Reward purchased successfully!',
            timer: 1800,
            showConfirmButton: false,
        });
    });
    
    // Keep the existing level-up event listener if you have one
</script>
@endpush
