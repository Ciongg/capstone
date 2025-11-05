{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\logs-index.blade.php --}}
<div class="mt-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white p-6">
        <div class="flex flex-col md:flex-row items-center md:items-start justify-between md:space-x-8">
            <div class="md:w-2/3 flex flex-col items-center md:items-start">
                <h1 class="text-4xl font-bold text-gray-600 text-center md:text-left mb-2">System Logs</h1>
                {{-- Removed subheader/overview to mirror reward-index's clean header --}}
            </div>
            {{-- Removed right-side overview card --}}
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 py-2 border-gray-300"></div>

    <!-- Tabs Navigation (same style as reward-index) -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <div class="flex -mb-px w-full">
                <button 
                    wire:click="setActiveTab('audit')"  
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition
                          {{ $activeTab === 'audit' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300' }}"
                >
                    Audit Logs
                </button>
                
                <button 
                    wire:click="setActiveTab('security')" 
                    class="py-4 flex-1 text-center border-b-2 font-medium text-sm focus:outline-none transition
                          {{ $activeTab === 'security' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-blue-500 hover:border-gray-300' }}"
                >
                    Security Logs
                </button>
            </div>
        </div>

        <!-- Audit Tab -->
        <div class="{{ $activeTab === 'audit' ? '' : 'hidden' }} mt-6">
            @include('livewire.super-admin.logs.audit-logs-index')
        </div>

        <!-- Security Tab -->
        <div class="{{ $activeTab === 'security' ? '' : 'hidden' }} mt-6">
            @include('livewire.super-admin.logs.security-logs-index')
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
