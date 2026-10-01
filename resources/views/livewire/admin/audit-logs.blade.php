<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-mahogany">Audit Trails & Logs</h1>
        <p class="text-gray-600">View all system activity and security events</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by email or IP address..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Event Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                <select wire:model.live="eventType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                    <option value="">All Events</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" wire:model.live="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
            </div>
        </div>

        <!-- User Filter & Clear -->
        <div class="flex flex-col md:flex-row md:items-end gap-4 mt-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by User</label>
                <select wire:model.live="userId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->created_at->format('g:i:s A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-mahogany text-white rounded-full flex items-center justify-center text-xs font-medium">
                                        {{ $log->user ? substr($log->user->name, 0, 1) : '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $log->user?->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ $log->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $eventColors = [
                                        'login' => 'bg-green-100 text-green-800',
                                        'logout' => 'bg-gray-100 text-gray-800',
                                        'failed_login' => 'bg-red-100 text-red-800',
                                        'user_created' => 'bg-blue-100 text-blue-800',
                                        'user_suspended' => 'bg-amber-100 text-amber-800',
                                        'user_activated' => 'bg-green-100 text-green-800',
                                        'user_deleted' => 'bg-red-100 text-red-800',
                                        'password_reset' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $colorClass = $eventColors[$log->event_type] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ ucwords(str_replace('_', ' ', $log->event_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                    <span class="text-sm text-gray-600 font-mono">{{ $log->ip_address }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($log->additional_data)
                                    @php
                                        $data = is_array($log->additional_data) ? $log->additional_data : json_decode($log->additional_data, true);
                                    @endphp
                                    @if($data)
                                        <div class="text-xs text-gray-600 max-w-xs truncate" title="{{ is_array($log->additional_data) ? json_encode($log->additional_data) : $log->additional_data }}">
                                            @foreach(array_slice($data, 0, 2) as $key => $value)
                                                <span class="inline-flex items-center gap-1 mr-2">
                                                    <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                                    <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500 font-medium">No audit logs found</p>
                                <p class="text-gray-400 text-sm">Try adjusting your search or filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Export Info -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-800">Audit Log Retention</p>
                <p class="text-sm text-blue-700 mt-1">Audit logs are retained for 90 days to comply with data retention policies. For extended records or compliance reports, please contact system administration.</p>
            </div>
        </div>
    </div>
</div>
