{{-- filepath: d:\Projects\capstone\resources\views\livewire\super-admin\logs\security-logs-index.blade.php --}}
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outcome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">HTTP</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($this->securityLogs as $log)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->created_at }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->email ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->event_type }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php $outcome = strtolower($log->outcome ?? ''); @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $outcome === 'success' ? 'bg-green-100 text-green-800' : ($outcome === 'failure' || $outcome === 'denied' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $log->outcome ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->ip ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->http_status ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <button
                                x-data
                                x-on:click="$wire.set('selectedSecurityLogId', null).then(() => {
                                    $wire.set('selectedSecurityLogId', {{ $log->id }});
                                    $nextTick(() => $dispatch('open-modal', { name: 'security-view-modal' }));
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
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No security logs available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
