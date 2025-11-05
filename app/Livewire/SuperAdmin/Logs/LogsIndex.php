<?php

namespace App\Livewire\SuperAdmin\Logs;

use Livewire\Component;
use App\Models\AuditLogs;
use App\Models\SecurityLogs;
use Livewire\WithPagination;

class LogsIndex extends Component
{
    use WithPagination;

    public $activeTab = 'audit';
    public $selectedAuditLogId = null;
    public $selectedSecurityLogId = null;
    
    // Search filters
    public $searchEmail = '';
    public $searchIp = '';
    
    // IP masking toggle
    public $maskIp = true;

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function toggleIpMasking()
    {
        $this->maskIp = !$this->maskIp;
    }

    public function updatedSearchEmail()
    {
        $this->resetPage();
    }

    public function updatedSearchIp()
    {
        $this->resetPage();
    }

    // DB-backed data sources with search
    public function getAuditLogsProperty()
    {
        $query = AuditLogs::orderByDesc('created_at');
        
        if (!empty($this->searchEmail)) {
            $query->where('email', 'like', '%' . $this->searchEmail . '%');
        }
        
        if (!empty($this->searchIp)) {
            $query->where('ip', 'like', '%' . $this->searchIp . '%');
        }
        
        return $query->paginate(20);
    }

    public function getSecurityLogsProperty()
    {
        $query = SecurityLogs::orderByDesc('created_at');
        
        if (!empty($this->searchEmail)) {
            $query->where('email', 'like', '%' . $this->searchEmail . '%');
        }
        
        if (!empty($this->searchIp)) {
            $query->where('ip', 'like', '%' . $this->searchIp . '%');
        }
        
        return $query->paginate(20);
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

    /**
     * Get friendly location label from log
     */
    public function getFriendlyLocation($log): string
    {
        $geo = $log->geo ?? [];
        $userAgent = $log->user_agent ?? '';
        
        // Parse location
        $location = 'Unknown Location';
        if (!empty($geo)) {
            $parts = [];
            if (!empty($geo['city'])) $parts[] = $geo['city'];
            if (!empty($geo['country'])) $parts[] = $geo['country'];
            $location = !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
        }
        
        // Parse browser and OS from user agent
        $browser = $this->getBrowserFromUserAgent($userAgent);
        $os = $this->getOsFromUserAgent($userAgent);
        
        return "Logged in from {$location} ({$browser} on {$os})";
    }

    /**
     * Mask IP address
     */
    public function maskIpAddress($ip): string
    {
        if (!$this->maskIp) {
            return $ip;
        }
        
        // For IPv4
        if (strpos($ip, '.') !== false) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                return $parts[0] . '**.***.' . $parts[3];
            }
        }
        
        // For IPv6 or other formats, mask middle portion
        if (strlen($ip) > 8) {
            return substr($ip, 0, 4) . '****' . substr($ip, -4);
        }
        
        return '***.**.***.***';
    }

    private function getBrowserFromUserAgent($userAgent): string
    {
        if (empty($userAgent)) return 'Unknown Browser';
        
        if (preg_match('/Edge\/\d+/', $userAgent)) return 'Edge';
        if (preg_match('/Edg\/\d+/', $userAgent)) return 'Edge';
        if (preg_match('/Chrome\/\d+/', $userAgent)) return 'Chrome';
        if (preg_match('/Safari\/\d+/', $userAgent) && !preg_match('/Chrome/', $userAgent)) return 'Safari';
        if (preg_match('/Firefox\/\d+/', $userAgent)) return 'Firefox';
        if (preg_match('/MSIE|Trident/', $userAgent)) return 'Internet Explorer';
        if (preg_match('/Opera|OPR\//', $userAgent)) return 'Opera';
        
        return 'Unknown Browser';
    }

    private function getOsFromUserAgent($userAgent): string
    {
        if (empty($userAgent)) return 'Unknown OS';
        
        if (preg_match('/Windows NT 10\.0/', $userAgent)) return 'Windows 10/11';
        if (preg_match('/Windows NT 6\.3/', $userAgent)) return 'Windows 8.1';
        if (preg_match('/Windows NT 6\.2/', $userAgent)) return 'Windows 8';
        if (preg_match('/Windows NT 6\.1/', $userAgent)) return 'Windows 7';
        if (preg_match('/Windows/', $userAgent)) return 'Windows';
        if (preg_match('/Mac OS X/', $userAgent)) return 'macOS';
        if (preg_match('/Linux/', $userAgent)) return 'Linux';
        if (preg_match('/Android/', $userAgent)) return 'Android';
        if (preg_match('/iPhone|iPad|iPod/', $userAgent)) return 'iOS';
        
        return 'Unknown OS';
    }

    public function render()
    {
        return view('livewire.super-admin.logs.logs-index');
    }
}
