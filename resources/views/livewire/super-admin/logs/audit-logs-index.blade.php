{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\audit-logs-index.blade.php --}}
<div class="overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">Timestamp</th>
                <th class="py-3 px-6 text-left">Email</th>
                <th class="py-3 px-6 text-left">User Type</th>
                <th class="py-3 px-6 text-left">Event</th>
                <th class="py-3 px-6 text-left">Actor</th>
                <th class="py-3 px-6 text-left">Resource</th>
                <th class="py-3 px-6 text-left">Message</th>
                <th class="py-3 px-6 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            @forelse($this->auditLogs as $log)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6 font-mono">{{ $log->id }}</td>
                    <td class="py-3 px-6">
                        <div class="max-w-[8rem] break-words">
                            {{ $log->created_at->format('M d, Y H:i') }}
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="max-w-[10rem] break-words">
                            {{ $log->email ?? '—' }}
                        </div>
                    </td>
                    <td class="py-3 px-6">
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
                    <td class="py-3 px-6">{{ $log->event_type }}</td>
                    <td class="py-3 px-6 whitespace-nowrap">
                        {{ $log->performed_by ? ('ID: ' . $log->performed_by) : '—' }}
                    </td>
                    <td class="py-3 px-6 whitespace-nowrap">
                        @if($log->resource_type || $log->resource_id)
                            {{ $log->resource_type ?? 'Resource' }}{{ $log->resource_id ? ' #' . $log->resource_id : '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="max-w-md">
                            {{ $log->message ?? '—' }}
                        </div>
                    </td>
                    <td class="py-3 px-6 whitespace-nowrap">
                        <button
                            x-data
                            x-on:click="$wire.set('selectedAuditLogId', null).then(() => {
                                $wire.set('selectedAuditLogId', {{ $log->id }});
                                $nextTick(() => $dispatch('open-modal', { name: 'audit-view-modal' }));
                            })"
                            class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                            type="button"
                        >
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-3 px-6 text-center">No audit logs available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $this->auditLogs->links() }}
</div>
