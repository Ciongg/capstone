{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\audit-logs-index.blade.php --}}
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">User Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resource</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($this->auditLogs as $log)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->email ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->performed_role)
                                <span class="inline-block px-2 py-1 rounded text-xs whitespace-nowrap
                                    {{ $log->performed_role === 'super_admin' ? 'bg-pink-200 text-pink-800' : 
                                    ($log->performed_role === 'institution_admin' ? 'bg-indigo-200 text-indigo-800' : 
                                    ($log->performed_role === 'researcher' ? 'bg-yellow-200 text-yellow-800' : 
                                    ($log->performed_role === 'respondent' ? 'bg-purple-200 text-purple-800' : 'bg-gray-200 text-gray-800'))) }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->performed_role)) }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->event_type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $log->performed_by ? ('ID: ' . $log->performed_by) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @if($log->resource_type || $log->resource_id)
                                {{ $log->resource_type ?? 'Resource' }} {{ $log->resource_id ? '#' . $log->resource_id : '' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 truncate max-w-xs">{{ $log->message ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <button
                                x-data
                                x-on:click="$wire.set('selectedAuditLogId', null).then(() => {
                                    $wire.set('selectedAuditLogId', {{ $log->id }});
                                    $nextTick(() => $dispatch('open-modal', { name: 'audit-view-modal' }));
                                })"
                                class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm flex items-center"
                                type="button"
                            >
                                View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            No audit logs available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $this->auditLogs->links() }}
    </div>
</div>
