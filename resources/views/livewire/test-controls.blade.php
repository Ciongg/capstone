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
        <div class="text-xs font-semibold text-green-800 mb-2">Points Controls:</div>
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
    
    <!-- Level Controls -->
    <div class="mb-3">
        <div class="text-xs font-semibold text-blue-800 mb-2">Level Controls:</div>
        <div class="grid grid-cols-2 gap-2">
            <button wire:click="levelUp" class="px-3 py-1 bg-blue-500 text-white text-xs rounded-md hover:bg-blue-600">
                Level Up
            </button>
            
            <button wire:click="resetLevel" class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                Reset Level
            </button>
        </div>
    </div>
    
    <!-- XP Controls -->
    <div class="mb-3">
        <div class="text-xs font-semibold text-purple-800 mb-2">XP Controls:</div>
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
    
    <!-- Success message -->
    @if(session()->has('message'))
        <div class="mt-2 text-xs text-green-700 bg-green-100 p-2 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>
