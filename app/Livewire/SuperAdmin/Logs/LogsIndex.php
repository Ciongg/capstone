<?php

namespace App\Livewire\SuperAdmin\Logs;

use Livewire\Component;
use App\Models\AuditLogs;
use App\Models\SecurityLogs;

class LogsIndex extends Component
{
    public $activeTab = 'audit';
    public $selectedAuditLogId = null;
    public $selectedSecurityLogId = null;

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    // DB-backed data sources
    public function getAuditLogsProperty()
    {
        return AuditLogs::orderByDesc('created_at')->limit(100)->get();
    }

    public function getSecurityLogsProperty()
    {
        return SecurityLogs::orderByDesc('created_at')->limit(100)->get();
    }

    // Computed selected models
    public function getSelectedAuditLogProperty()
    {
        return $this->selectedAuditLogId ? AuditLogs::find($this->selectedAuditLogId) : null;
    }

    public function getSelectedSecurityLogProperty()
    {
        return $this->selectedSecurityLogId ? SecurityLogs::find($this->selectedSecurityLogId) : null;
    }

    public function viewAuditLog($id)
    {
        $this->selectedAuditLogId = $id;
        // Use PHP array for payload (fix syntax)
        $this->dispatch('open-modal', ['name' => 'audit-view-modal']);
    }

    public function viewSecurityLog($id)
    {
        $this->selectedSecurityLogId = $id;
        // Use PHP array for payload (fix syntax)
        $this->dispatch('open-modal', ['name' => 'security-view-modal']);
    }

    public function render()
    {
        return view('livewire.super-admin.logs.logs-index', [
            // pass anything extra if needed; computed props are available in blade via $this->
        ]);
    }
}
