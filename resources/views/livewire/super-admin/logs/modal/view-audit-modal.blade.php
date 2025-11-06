{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\modal\view-audit-modal.blade.php --}}
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
                <span class="text-sm text-gray-500">Performed By</span>
                <span class="text-sm font-medium text-gray-800">
                    {{ data_get($log, 'performed_by') ?: '—' }}
                    @if(data_get($log, 'performed_role'))
                        <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ data_get($log, 'performed_role') }}</span>
                    @endif
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">IP</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'ip', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Message</span>
                <span class="text-sm font-medium text-gray-800">{{ data_get($log, 'message', '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Resource</span>
                <span class="text-sm font-medium text-gray-800">
                    @php
                        $rtype = data_get($log, 'resource_type');
                        $rid = data_get($log, 'resource_id');
                    @endphp
                    {{ $rtype || $rid ? (($rtype ?? 'Resource') . ($rid ? ' #' . $rid : '')) : '—' }}
                </span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 mb-1">Changed Fields</span>
                @php $changed = data_get($log, 'changed_fields'); @endphp
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-md p-3 overflow-x-auto whitespace-pre-wrap">
{{ $changed ? (is_array($changed) ? implode(', ', $changed) : (is_string($changed) ? $changed : json_encode($changed, JSON_PRETTY_PRINT))) : '—' }}
                </pre>
            </div>
            <div>
                <span class="block text-sm text-gray-500 mb-1">Before</span>
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-md p-3 overflow-x-auto whitespace-pre-wrap">{{ json_encode(data_get($log, 'before'), JSON_PRETTY_PRINT) ?: '—' }}</pre>
            </div>
            <div>
                <span class="block text-sm text-gray-500 mb-1">After</span>
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-md p-3 overflow-x-auto whitespace-pre-wrap">{{ json_encode(data_get($log, 'after'), JSON_PRETTY_PRINT) ?: '—' }}</pre>
            </div>
        </div>
    @else
        <div class="text-center text-gray-500 py-6">
            No audit log selected.
        </div>
    @endif
</div>
