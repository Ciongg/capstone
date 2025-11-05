{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\modal\view-security-modal.blade.php --}}
<div class="p-4">
    @if(!empty($log))
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Timestamp</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'created_at') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Event</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'event_type', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Outcome</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'outcome', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">IP</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'ip', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Route</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'route', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Method</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'http_method', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">HTTP Status</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'http_status', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Message</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'message', '—') }}</span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 mb-1">Meta</span>
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-md p-3 overflow-x-auto whitespace-pre-wrap">{{ json_encode(data_get($log, 'meta'), JSON_PRETTY_PRINT) ?: '—' }}</pre>
            </div>
            <div>
                <span class="block text-sm text-gray-500 mb-1">Geo</span>
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-md p-3 overflow-x-auto whitespace-pre-wrap">{{ json_encode(data_get($log, 'geo'), JSON_PRETTY_PRINT) ?: '—' }}</pre>
            </div>
        </div>
    @else
        <div class="text-center text-gray-500 py-6">
            No security log selected.
        </div>
    @endif
</div>
