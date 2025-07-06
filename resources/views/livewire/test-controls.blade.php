<div>
    <!-- User Stats Display -->
    <div class="bg-gray-100 p-3 rounded-md mb-3">
        <!-- Points Display -->
        <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-green-600">Points: {{ $userPoints }}</span>
        </div>
        
        <!-- Level & XP Info -->
        <div class="flex items-center justify-between mb-2">
            <span class="font-semibold">Level {{ $userLevel }}</span>
            @if($userLevel >= 30)
                <span class="text-sm text-gray-600">{{ $userExperience }} XP (Maxed)</span>
            @else
                <span class="text-sm text-gray-600">{{ $userExperience }}/{{ $xpForNextLevel }} XP</span>
            @endif
        </div>
        
        <!-- XP Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $levelProgress }}%"></div>
        </div>
        
        <!-- Rank Display -->
        <div class="text-center text-sm text-blue-700 font-medium">
            {{ ucfirst($rank) }} Rank
            @if($userLevel >= 30)
                <span class="text-purple-600 font-bold">(MAX)</span>
            @endif
        </div>
    </div>
    
    <!-- Points Controls -->
    <div class="mb-3">
        <button 
            wire:click="togglePointsControls"
            class="w-full flex items-center justify-between p-2 bg-green-100 hover:bg-green-200 rounded-md text-xs font-semibold text-green-800"
        >
            <span>Points Controls</span>
            <svg class="w-4 h-4 transform transition-transform {{ $showPointsControls ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        @if($showPointsControls)
            <div class="mt-2 p-2 bg-green-50 rounded-md">
                <div class="grid grid-cols-3 gap-1">
                    <button wire:click="addPoints(1)" class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600">
                        +1
                    </button>
                    <button wire:click="addPoints(10)" class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600">
                        +10
                    </button>
                    <button wire:click="addPoints(1000)" class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600">
                        +1000
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-1 mt-1">
                    <button wire:click="subtractPoints(1)" class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">
                        -1
                    </button>
                    <button wire:click="subtractPoints(10)" class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">
                        -10
                    </button>
                    <button wire:click="subtractPoints(1000)" class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">
                        -1000
                    </button>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Level Controls -->
    <div class="mb-3">
        <button 
            wire:click="toggleLevelControls"
            class="w-full flex items-center justify-between p-2 bg-blue-100 hover:bg-blue-200 rounded-md text-xs font-semibold text-blue-800"
        >
            <span>Level Controls</span>
            <svg class="w-4 h-4 transform transition-transform {{ $showLevelControls ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        @if($showLevelControls)
            <div class="mt-2 p-2 bg-blue-50 rounded-md">
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="levelUp" class="px-3 py-1 bg-blue-500 text-white text-xs rounded-md hover:bg-blue-600">
                        Level Up
                    </button>
                    <button wire:click="resetLevel" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                        Reset Level
                    </button>
                </div>
            </div>
        @endif
    </div>
    
    <!-- XP Controls -->
    <div class="mb-3">
        <button 
            wire:click="toggleXpControls"
            class="w-full flex items-center justify-between p-2 bg-purple-100 hover:bg-purple-200 rounded-md text-xs font-semibold text-purple-800"
        >
            <span>XP Controls</span>
            <svg class="w-4 h-4 transform transition-transform {{ $showXpControls ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        @if($showXpControls)
            <div class="mt-2 p-2 bg-purple-50 rounded-md">
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="addXp(1)" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 text-xs rounded-md">
                        +1 XP
                    </button>
                    <button wire:click="addXp(10)" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 text-xs rounded-md">
                        +10 XP
                    </button>
                    <button wire:click="addXp(50)" class="bg-purple-700 hover:bg-purple-800 text-white px-3 py-1 text-xs rounded-md">
                        +50 XP
                    </button>
                    <button wire:click="addXp(100)" class="bg-purple-800 hover:bg-purple-900 text-white px-3 py-1 text-xs rounded-md">
                        +100 XP
                    </button>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Inbox Test Controls -->
    <div class="mb-3">
        <button 
            wire:click="toggleInboxControls"
            class="w-full flex items-center justify-between p-2 bg-green-100 hover:bg-green-200 rounded-md text-xs font-semibold text-green-800"
        >
            <span>Inbox Test Controls</span>
            <svg class="w-4 h-4 transform transition-transform {{ $showInboxControls ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        @if($showInboxControls)
            <div class="mt-2 p-2 bg-green-50 rounded-md">
                <div class="mb-2">
                    <input 
                        type="text" 
                        wire:model="inboxSubject"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                        placeholder="Message Subject"
                    >
                </div>
                <div class="mb-2">
                    <textarea 
                        wire:model="inboxMessage"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                        rows="2"
                        placeholder="Message Content"
                    ></textarea>
                </div>
                <div class="mb-2">
                    <input 
                        type="text" 
                        wire:model="inboxUrl"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" 
                        placeholder="Related URL (optional)"
                    >
                </div>
                <button 
                    wire:click="sendTestInboxMessage" 
                    class="w-full bg-green-500 hover:bg-green-600 text-white px-3 py-1 text-xs rounded-md"
                >
                    Send Test Inbox Message
                </button>
            </div>
        @endif
    </div>
    
    <!-- Date/Time Test Controls -->
    <div class="mb-3">
        <button 
            wire:click="toggleDateTimeControls"
            class="w-full flex items-center justify-between p-2 bg-orange-100 hover:bg-orange-200 rounded-md text-xs font-semibold text-orange-800"
        >
            <span>Date/Time Test Controls</span>
            <svg class="w-4 h-4 transform transition-transform {{ $showDateTimeControls ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        @if($showDateTimeControls)
            <div class="mt-2 p-2 bg-orange-50 rounded-md">
                <!-- Current Time Status -->
                <div class="bg-gray-50 p-2 rounded-md mb-2 text-xs">
                    @if($isTestModeActive)
                        <div class="text-orange-600 font-semibold">TEST MODE ACTIVE</div>
                        <div class="text-gray-700">Test Time: {{ $currentTestTime }}</div>
                    @else
                        <div class="text-green-600">Real Time Mode</div>
                        <div class="text-gray-700">Current: {{ now()->format('Y-m-d H:i:s') }}</div>
                    @endif
                </div>
                
                <!-- Set Test Time -->
                <div class="mb-2">
                    <input 
                        type="datetime-local" 
                        wire:model="testDateTime"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-orange-500"
                    >
                </div>
                
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <button 
                        wire:click="setTestTime" 
                        class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 text-xs rounded-md"
                    >
                        Set Test Time
                    </button>
                    <button 
                        wire:click="resetTestTime" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 text-xs rounded-md"
                    >
                        Reset to Real Time
                    </button>
                </div>
                
                <!-- Time Manipulation (only when test mode is active) -->
                @if($isTestModeActive)
                    <div class="text-xs text-orange-700 mb-1">Quick Time Adjustments:</div>
                    <div class="grid grid-cols-4 gap-1 mb-1">
                        <button wire:click="addHours(1)" class="bg-orange-400 hover:bg-orange-500 text-white px-2 py-1 text-xs rounded">
                            +1h
                        </button>
                        <button wire:click="addHours(6)" class="bg-orange-400 hover:bg-orange-500 text-white px-2 py-1 text-xs rounded">
                            +6h
                        </button>
                        <button wire:click="addHours(12)" class="bg-orange-400 hover:bg-orange-500 text-white px-2 py-1 text-xs rounded">
                            +12h
                        </button>
                        <button wire:click="addHours(24)" class="bg-orange-400 hover:bg-orange-500 text-white px-2 py-1 text-xs rounded">
                            +24h
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <button wire:click="addDays(1)" class="bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 text-xs rounded">
                            +1 Day
                        </button>
                        <button wire:click="addDays(7)" class="bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 text-xs rounded">
                            +1 Week
                        </button>
                        <button wire:click="addDays(30)" class="bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 text-xs rounded">
                            +1 Month
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Success message -->
    @if(session()->has('message'))
        <div class="mt-2 text-xs text-green-700 bg-green-100 p-2 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>
