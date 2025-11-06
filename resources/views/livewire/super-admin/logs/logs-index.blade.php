{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\logs-index.blade.php --}}
<div class="mt-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white p-6">
        <div class="flex flex-col md:flex-row items-center md:items-start justify-between md:space-x-8">
            <div class="md:w-2/3 flex flex-col items-center md:items-start">
                <h1 class="text-4xl font-bold text-gray-600 text-center md:text-left mb-2">System Logs</h1>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 py-2 border-gray-300"></div>

    <!-- Search Bar - Conditional based on active tab -->
    @if($activeTab === 'security')
        <!-- Security Logs Search -->
        <div class="mb-4 flex flex-col md:flex-row gap-2">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchEmail" 
                placeholder="Search by email or ID..." 
                class="flex-1 px-4 py-2 border rounded-lg"
            >
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchIp" 
                placeholder="Search by IP address..." 
                class="flex-1 px-4 py-2 border rounded-lg"
            >
            <button 
                wire:click="toggleIpMasking"
                class="w-40 px-4 py-2 {{ $maskIp ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded text-sm whitespace-nowrap flex items-center justify-center gap-2"
            >
                @if($maskIp)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Show Full IP</span>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                    </svg>
                    <span>Mask IP</span>
                @endif
            </button>
        </div>
    @else
        <!-- Audit Logs Search -->
        <div class="mb-4 flex flex-col md:flex-row gap-2">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchAuditEmail" 
                placeholder="Search by email or ID..." 
                class="flex-1 px-4 py-2 border rounded-lg"
            >
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchAuditResource" 
                placeholder="Search by resource..." 
                class="flex-1 px-4 py-2 border rounded-lg"
            >
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchAuditEvent" 
                placeholder="Search by event..." 
                class="flex-1 px-4 py-2 border rounded-lg"
            >
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <div class="flex -mb-px w-full">
                <button 
                    wire:click="setActiveTab('security')" 
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition
                          {{ $activeTab === 'security' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300' }}"
                >
                    Security Logs
                </button>
                
                <button 
                    wire:click="setActiveTab('audit')"  
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition
                          {{ $activeTab === 'audit' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300' }}"
                >
                    Audit Logs
                </button>
            </div>
        </div>

        <!-- Security Tab -->
        <div class="{{ $activeTab === 'security' ? '' : 'hidden' }} mt-6">
            @include('livewire.super-admin.logs.security-logs-index')
        </div>

        <!-- Audit Tab -->
        <div class="{{ $activeTab === 'audit' ? '' : 'hidden' }} mt-6">
            @include('livewire.super-admin.logs.audit-logs-index')
        </div>
    </div>

    <!-- View Modals -->
    <x-modal name="audit-view-modal" title="View Audit Log">
        @include('livewire.super-admin.logs.modal.view-audit-modal', ['log' => $this->selectedAuditLog])
    </x-modal>

    <x-modal name="security-view-modal" title="View Security Log">
        @include('livewire.super-admin.logs.modal.view-security-modal', ['log' => $this->selectedSecurityLog])
    </x-modal>
</div>
