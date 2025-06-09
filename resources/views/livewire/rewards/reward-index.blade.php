<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Include level-up event listener -->
    @include('livewire.rewards.partials.level-up-listener')


    <!-- New Layout: User Stats and Points Display -->
    <div class="bg-white p-2"> 
        <div class="flex flex-col md:flex-row items-start justify-between md:space-x-8">
            <!-- Left Side: Header and Points Display -->
            <div class="md:w-1/2 lg:w-2/3">
                <h1 class="text-4xl font-bold text-black text-center md:text-left mb-4">Redeem Rewards</h1>
                
                <div class="flex flex-col justify-between md:items-start mb-6"> 
                    <div class="flex items-center">
                        <span class="text-7xl font-bold text-[#FFB349]">{{ $userPoints }}</span>
                        <svg class="w-16 h-16 text-[#FFB349] ml-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.0898,8.9999 L17.7848,4.8299 L20.5658,8.9999 L17.0898,8.9999 Z M16.8358,9.9999 L20.4068,9.9999 L13.6208,17.8579 L16.8358,9.9999 Z M7.1638,9.9999 L10.3788,17.8579 L3.5918,9.9999 L7.1638,9.9999 Z M6.9098,8.9999 L3.4338,8.9999 L6.2148,4.8299 L6.9098,8.9999 Z M7.8008,8.2649 L7.0898,3.9999 L10.9998,3.9999 L7.8008,8.2649 Z M12.9998,3.9999 L16.9098,3.9999 L16.1988,8.2649 L12.9998,3.9999 Z M8.4998,8.9999 L11.9998,4.3329 L15.4998,8.9999 L8.4998,8.9999 Z M15.7548,9.9999 L11.9998,19.1799 L8.2448,9.9999 L15.7548,9.9999 Z M21.9158,9.2229 L17.9158,3.2229 C17.9148,3.2209 17.9118,3.2199 17.9108,3.2179 C17.8688,3.1569 17.8138,3.1069 17.7478,3.0699 C17.7288,3.0589 17.7078,3.0559 17.6888,3.0469 C17.6528,3.0329 17.6208,3.0129 17.5818,3.0069 C17.5658,3.0039 17.5498,3.0099 17.5328,3.0079 C17.5218,3.0079 17.5118,2.9999 17.4998,2.9999 L6.4998,2.9999 C6.4878,2.9999 6.4778,3.0079 6.4658,3.0079 C6.4498,3.0099 6.4348,3.0039 6.4178,3.0069 C6.3788,3.0129 6.3468,3.0329 6.3118,3.0469 C6.2918,3.0559 6.2708,3.0589 6.2528,3.0699 C6.1868,3.1069 6.1308,3.1569 6.0898,3.2179 C6.0878,3.2199 6.0858,3.2209 6.0838,3.2229 L2.0838,9.2229 C1.9598,9.4099 1.9748,9.6569 2.1218,9.8269 L11.6218,20.8269 C11.6428,20.8519 11.6718,20.8629 11.6968,20.8829 C11.7188,20.8999 11.7378,20.9189 11.7628,20.9319 C11.9118,21.0139 12.0878,21.0139 12.2368,20.9319 C12.2618,20.9189 12.2808,20.8999 12.3028,20.8829 C12.3278,20.8629 12.3568,20.8519 12.3788,20.8269 L21.8788,9.8269 C22.0258,9.6569 22.0408,9.4099 21.9158,9.2229 L21.9158,9.2229 Z"/>
                        </svg>
                    </div>
                    <p class="text-xl text-gray-600 mt-2 text-center md:text-left">Available Points</p> 
                    <p class="text-gray-600 mt-4 text-center md:text-left">Earn more points by completing surveys!</p> 
                </div>
            </div>
            
            <!-- Right Side: User Profile Info - Aligned with header -->
            <div class="flex flex-col items-center md:items-end md:w-1/2 lg:w-1/3">
                <a href="{{ route('profile.index') }}" class="group flex flex-col items-center"> 
                    <!-- User Profile Picture -->
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-200 group-hover:border-blue-300 transition-all">
                        <img src="{{ $user?->profile_photo_url }}" alt="{{ $user?->name }}" class="w-full h-full object-cover">
                    </div>
                    
                    <!-- User Stats -->
                    <div class="mt-3 text-center"> 
                        <p class="text-lg font-semibold text-gray-800">{{ $user?->name }}</p>
                        
                        <!-- User Title -->
                        <p class="text-sm font-medium text-blue-600 mb-1">{{ ucfirst($user?->rank ?: 'silver') }}</p>
                        
                        <!-- XP Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1 dark:bg-gray-700">
                            <div class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ $levelProgress }}%"></div> 
                        </div>
                        
                        <!-- Level Display -->
                        <p class="text-xs text-gray-500 mb-2">Level {{ $userLevel }} • {{ $userExperience }}/{{ $xpForNextLevel }} XP</p>
                        
                        <!-- Trust Score -->
                        <div class="flex items-center justify-center mt-1"> 
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-gray-600">Trust: {{ $userTrustScore }}/100</span>
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
            <div class="flex -mb-px">
                <button 
                    wire:click="setActiveTab('system')"  
                    class="py-4 px-6 border-b-2 font-medium text-sm focus:outline-none
                          {{ $activeTab === 'system' ? 'border-[#03b8ff] text-[#03b8ff]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    System Rewards
                </button>
                <button 
                    wire:click="setActiveTab('voucher')" 
                    class="py-4 px-6 border-b-2 font-medium text-sm focus:outline-none
                          {{ $activeTab === 'voucher' ? 'border-[#03b8ff] text-[#03b8ff]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Gift Vouchers
                </button>
                
             
            </div>
        </div>

            <!-- Flash Messages for Redemption -->
        <div class="mb-4">
            @if(session()->has('redeem_success'))
                <div class="mt-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-md">
                    {{ session('redeem_success') }}
                </div>
            @endif
            @if(session()->has('redeem_error'))
                <div class="mt-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-md">
                    {{ session('redeem_error') }}
                </div>
            @endif
        </div>
    

        <!-- System Rewards Tab -->
        <div class="{{ $activeTab === 'system' ? '' : 'hidden' }} mt-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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



     @if(Auth::check())
            <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-md">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-semibold text-purple-800">Test Controls:</span>
                    
                    <!-- Add Points Buttons -->
                    <button wire:click="addPoints(1)" class="px-3 py-1 bg-green-500 text-white text-xs rounded-md hover:bg-green-600">
                        +1
                    </button>
                    <button wire:click="addPoints(10)" class="px-3 py-1 bg-green-500 text-white text-xs rounded-md hover:bg-green-600">
                        +10
                    </button>
                    <button wire:click="addPoints(100)" class="px-3 py-1 bg-green-500 text-white text-xs rounded-md hover:bg-green-600">
                        +100
                    </button>
                    <button wire:click="addPoints(1000)" class="px-3 py-1 bg-green-500 text-white text-xs rounded-md hover:bg-green-600">
                        +1000
                    </button>
                    
                    <!-- Subtract Points Buttons -->
                    <button wire:click="subtractPoints(1)" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                        -1
                    </button>
                    <button wire:click="subtractPoints(10)" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                        -10
                    </button>
                    <button wire:click="subtractPoints(100)" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                        -100
                    </button>
                    <button wire:click="subtractPoints(1000)" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                        -1000
                    </button>
                </div>
                
                <!-- XP Test Controls -->
                <div class="flex flex-wrap gap-2 items-center mt-2">
                    <span class="text-sm font-semibold text-purple-800">XP Controls:</span>
                    
                    <button wire:click="levelUp" class="px-3 py-1 bg-blue-500 text-white text-xs rounded-md hover:bg-blue-600">
                        Level Up
                    </button>
                    <button wire:click="resetLevel" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                        Reset Level
                    </button>

                    <!-- New XP Buttons -->
                    <button wire:click="addXp(1)" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 text-xs rounded-md">
                        +1 XP
                    </button>
                    
                    <button wire:click="addXp(10)" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 text-xs rounded-md">
                        +10 XP
                    </button>

                </div>
                
                <!-- Success message -->
                @if(session()->has('message'))
                    <div class="mt-2 text-sm text-green-700">
                        {{ session('message') }}
                    </div>
                @endif
            </div>
        @endif
</div>
