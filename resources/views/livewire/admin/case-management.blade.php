<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-mahogany">Case Management</h1>
        <p class="text-gray-600">Manage violation cases, generate reports, and track sanctions</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tab Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto">
                <button wire:click="setTab('inbox')" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'inbox' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        Inbox
                        @if($inboxCount > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-inferno rounded-full">{{ $inboxCount > 9 ? '9+' : $inboxCount }}</span>
                        @endif
                    </div>
                </button>
                <button wire:click="setTab('cases')" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'cases' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Cases
                        @if($statistics['total_cases'] > 0)
                            <span class="inline-flex items-center justify-center px-2 h-5 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">{{ $statistics['total_cases'] }}</span>
                        @endif
                    </div>
                </button>
                <button wire:click="setTab('case-summary')" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'case-summary' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Case Summary
                    </div>
                </button>
                <button wire:click="setTab('monthly-report')" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'monthly-report' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Monthly Report
                    </div>
                </button>
                <button wire:click="setTab('by-type-report')" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'by-type-report' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        By Type
                    </div>
                </button>
            </nav>
        </div>

        {{-- ====================== TAB 0: INBOX ====================== --}}
        @if($activeTab === 'inbox')
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">Incoming Reports</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Review filed charges from staff. Click <strong>Open Case</strong> to create a formal violation record.</p>
                </div>
                @if($inboxCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-white bg-inferno rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        {{ $inboxCount }} Awaiting Review
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offense</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filed By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse($incomingReports as $report)
                            <tr class="hover:bg-gray-50 transition-colors {{ $report->status === 'Dismissed' ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3 text-sm font-mono text-mahogany font-medium">{{ $report->tracking_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-mahogany text-white flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($report->student?->first_name ?? $report->student?->name ?? '?', 0, 1) . substr($report->student?->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $report->student?->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-gray-500">{{ $report->student?->student_id ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-gray-800 font-medium">{{ $report->offense?->title ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $report->offense?->code ?? '' }} &bull; {{ $report->offense?->severity_level ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $report->report_type === 'Formal Charge' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $report->report_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $report->reporter?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $report->created_at->format('M d, Y') }}<br>
                                    <span class="text-gray-400">{{ $report->created_at->format('g:i A') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $report->status === 'Submitted' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $report->status === 'Under Review by OSDW' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $report->status === 'Dismissed' ? 'bg-gray-100 text-gray-500' : '' }}
                                        {{ $report->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}
                                    ">{{ $report->status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($report->status === 'Submitted')
                                        <div class="flex items-center gap-1.5">
                                            <button wire:click="openAcceptModal({{ $report->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Open Case
                                            </button>
                                            <button wire:click="dismissReport({{ $report->id }})" wire:confirm="Dismiss this report? This cannot be undone." class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Dismiss
                                            </button>
                                        </div>
                                    @elseif($report->status === 'Under Review by OSDW')
                                        <span class="text-xs text-blue-600 font-medium">Case Opened</span>
                                    @elseif($report->status === 'Dismissed')
                                        <span class="text-xs text-gray-400">Dismissed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-16 text-center">
                                    <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="text-gray-400 font-medium">No reports in the inbox.</p>
                                    <p class="text-sm text-gray-400 mt-1">Reports filed by staff will appear here for review.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $incomingReports->links() }}</div>
        </div>
        @endif

        {{-- ====================== TAB 1: CASES ====================== --}}
        @if($activeTab === 'cases')
        <div class="p-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Cases</p>
                            <p class="text-3xl font-bold text-mahogany mt-1">{{ $statistics['total_cases'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pending Review</p>
                            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $statistics['pending_review'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active Sanctions</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ $statistics['active_sanctions'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Resolved</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ $statistics['resolved'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="searchTerm" placeholder="Search case #, student, offense..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                </div>
                <select wire:model.live="filterStatus" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                    <option value="">All Statuses</option>
                    <option value="Pending Review">Pending Review</option>
                    <option value="Notice Sent">Notice Sent</option>
                    <option value="Under Investigation">Under Investigation</option>
                    <option value="Sanction Active">Sanction Active</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Appealed">Appealed</option>
                </select>
                <select wire:model.live="filterInvestigationType" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                    <option value="">All Types</option>
                    <option value="Tribunal">Tribunal</option>
                    <option value="Summary">Summary</option>
                    <option value="Dean Direct">Dean Direct</option>
                </select>
                <div class="flex items-center gap-3">
                    <select wire:model.live="filterOffenseCategory" class="flex-1 py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                        <option value="">All Categories</option>
                        @foreach($offenseCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    <button wire:click="clearFilters" class="px-3 py-2 text-sm text-gray-500 hover:text-inferno border border-gray-300 rounded-lg hover:border-inferno transition-colors">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Cases Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('case_tracking_number')">Case #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offense</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Settled By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sanction Imposed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions Taken</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse($cases as $case)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-mono text-mahogany font-medium">{{ $case->case_tracking_number ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-mahogany text-white flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($case->student?->first_name ?? '', 0, 1) . substr($case->student?->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $case->student?->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">{{ $case->student?->student_id ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 max-w-[200px] truncate" title="{{ $case->offenseRule?->title ?? '' }}">{{ $case->offenseRule?->title ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $case->status === 'Pending Review' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $case->status === 'Notice Sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $case->status === 'Under Investigation' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $case->status === 'Sanction Active' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $case->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $case->status === 'Appealed' ? 'bg-purple-100 text-purple-800' : '' }}
                                    ">{{ $case->status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($case->settled_by)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $case->settled_by === 'Dean' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $case->settled_by }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    @if($case->sanction_imposed_at)
                                        {{ $case->sanction_imposed_at->format('M d, Y') }}<br>
                                        <span class="text-gray-400">{{ $case->sanction_imposed_at->format('g:i A') }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 max-w-[200px] truncate" title="{{ $case->action_taken ?? '' }}">{{ $case->action_taken ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <button wire:click="viewCase({{ $case->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        @if($case->canSendNotice())
                                            @php
                                                $nextNotice = ($case->notice_count ?? 0) + 1;
                                                $ordinal = $nextNotice === 1 ? '1st' : ($nextNotice === 2 ? '2nd' : '3rd');
                                            @endphp
                                            <button wire:click="openSendNoticeModal({{ $case->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Send {{ $ordinal }} Notice to Student">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </button>
                                        @endif
                                        @if($case->status === 'Notice Sent')
                                            @if($case->student_answer_submitted_date || $case->isAnswerOverdue())
                                                <button wire:click="advanceToInvestigation({{ $case->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Move to Investigation">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                </button>
                                            @endif
                                        @elseif($case->status === 'Under Investigation')
                                            <button wire:click="openScheduleConferenceModal({{ $case->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="Schedule Conference">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </button>
                                            <button wire:click="openResolveCaseModal({{ $case->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 transition-colors" title="Apply Sanction">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            @if($case->investigation_type === 'Tribunal' && !$case->assigned_to_sdt)
                                                <button wire:click="openAssignSDTModal({{ $case->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="Assign SDT">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-gray-500">No cases found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $cases->links() }}
            </div>
        </div>

        {{-- ====================== CASE DETAIL PANEL ====================== --}}
        @if($selectedCase && !$showResolveCaseModal && !$showAssignSDTModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.classList.add('overflow-hidden')" x-on:close-case.window="$wire.closeCase()"
             x-on:keydown.escape.window="$wire.closeCase()">
            <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeCase"></div>

                <div class="relative my-2 sm:my-8 max-h-[calc(100dvh-1rem)] w-full max-w-5xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-2xl z-10">

                    {{-- Official Header --}}
                    <div class="bg-gradient-to-r from-mahogany to-black-cherry rounded-t-2xl px-4 sm:px-8 py-5 sm:py-6 text-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <svg class="w-6 h-6 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <h3 class="text-lg font-bold tracking-wide">CASE FILE</h3>
                                </div>
                                <p class="text-2xl font-mono font-bold tracking-wider">{{ $selectedCase->case_tracking_number }}</p>
                                <p class="text-sm text-white/70 mt-1">Filed {{ $selectedCase->charge_filed_date?->format('F d, Y') ?? $selectedCase->created_at->format('F d, Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold tracking-wide
                                    {{ $selectedCase->status === 'Pending Review' ? 'bg-amber-400/20 text-amber-200 ring-1 ring-amber-400/40' : '' }}
                                    {{ $selectedCase->status === 'Notice Sent' ? 'bg-blue-400/20 text-blue-200 ring-1 ring-blue-400/40' : '' }}
                                    {{ $selectedCase->status === 'Under Investigation' ? 'bg-indigo-400/20 text-indigo-200 ring-1 ring-indigo-400/40' : '' }}
                                    {{ $selectedCase->status === 'Sanction Active' ? 'bg-red-400/20 text-red-200 ring-1 ring-red-400/40' : '' }}
                                    {{ $selectedCase->status === 'Resolved' ? 'bg-green-400/20 text-green-200 ring-1 ring-green-400/40' : '' }}
                                    {{ $selectedCase->status === 'Appealed' ? 'bg-purple-400/20 text-purple-200 ring-1 ring-purple-400/40' : '' }}
                                ">{{ strtoupper($selectedCase->status) }}</span>
                                <button wire:click="closeCase" class="p-2 text-white/60 hover:text-white rounded-lg hover:bg-white/10 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="max-h-[70vh] overflow-y-auto">

                        {{-- Student & Case Info Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200 border-b border-gray-200">
                            {{-- Student Information --}}
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Respondent Information</h4>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">Full Name</p>
                                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $selectedCase->student?->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Student ID</p>
                                            <p class="text-sm font-medium text-gray-800 font-mono mt-1">{{ $selectedCase->student?->student_id ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Year / Section</p>
                                            <p class="text-sm font-medium text-gray-800 mt-1">{{ $selectedCase->student?->year_level ?? '—' }} - {{ $selectedCase->student?->section ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">College / Department</p>
                                        <p class="text-sm font-medium text-gray-800 mt-1">{{ $selectedCase->student?->college ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Case Information --}}
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Case Information</h4>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">Offense Charged</p>
                                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $selectedCase->offenseRule?->title ?? 'N/A' }}</p>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            @if($selectedCase->offenseRule?->code)
                                                <span class="text-xs font-mono text-gray-500">{{ $selectedCase->offenseRule->code }}</span>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ $selectedCase->offenseRule?->category ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Investigation Type</p>
                                            <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                {{ $selectedCase->investigation_type === 'Tribunal' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $selectedCase->investigation_type === 'Summary' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $selectedCase->investigation_type === 'Dean Direct' ? 'bg-amber-100 text-amber-700' : '' }}
                                            ">{{ $selectedCase->investigation_type ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Offense Count</p>
                                            <p class="text-sm font-medium text-gray-800 mt-1">{{ $selectedCase->offense_count ?? 1 }}{{ $selectedCase->offense_count === 1 ? 'st' : ($selectedCase->offense_count === 2 ? 'nd' : ($selectedCase->offense_count === 3 ? 'rd' : 'th')) }} Offense</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Reported By</p>
                                            <p class="text-sm font-medium text-gray-800 mt-1">{{ $selectedCase->reporter?->name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Date of Incident</p>
                                            <p class="text-sm font-medium text-gray-800 mt-1">{{ $selectedCase->date_of_incident?->format('F d, Y') ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Key Dates Row --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 divide-y md:divide-y-0 md:divide-x divide-gray-200 border-b border-gray-200 bg-gray-50/50">
                            <div class="px-5 py-3">
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Charge Filed</p>
                                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $selectedCase->charge_filed_date?->format('M d, Y') ?? '—' }}</p>
                            </div>
                            <div class="px-5 py-3">
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Notice Sent</p>
                                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $selectedCase->notice_sent_at?->format('M d, Y') ?? '—' }}</p>
                            </div>
                            <div class="px-5 py-3">
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Answer Deadline</p>
                                <p class="text-sm font-semibold mt-0.5 {{ $selectedCase->isAnswerOverdue() ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $selectedCase->answer_deadline?->format('M d, Y') ?? '—' }}
                                    @if($selectedCase->isAnswerOverdue())
                                        <span class="text-xs text-red-500 font-normal">(Overdue)</span>
                                    @endif
                                </p>
                            </div>
                            <div class="px-5 py-3">
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Hearing Date</p>
                                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $selectedCase->hearing_scheduled_date?->format('M d, Y') ?? '—' }}</p>
                            </div>
                            <div class="px-5 py-3">
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Decision Deadline</p>
                                <p class="text-sm font-semibold mt-0.5 {{ $selectedCase->isDecisionOverdue() ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $selectedCase->decision_deadline?->format('M d, Y') ?? '—' }}
                                    @if($selectedCase->isDecisionOverdue())
                                        <span class="text-xs text-red-500 font-normal">(Overdue)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Sanction Section --}}
                        @if($selectedCase->settled_by || $selectedCase->sanction_imposed_at || $selectedCase->action_taken)
                        <div class="border-b border-gray-200">
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Sanction Details</h4>
                                </div>
                                <div class="bg-red-50/50 border border-red-100 rounded-xl p-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Settled By</p>
                                            @if($selectedCase->settled_by)
                                                <span class="inline-flex items-center mt-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $selectedCase->settled_by === 'Dean' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ $selectedCase->settledByLabel() }}
                                                </span>
                                            @else
                                                <p class="text-sm text-gray-500 mt-0.5">—</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Applied Sanction</p>
                                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $selectedCase->applied_sanction ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Sanction Imposed</p>
                                            <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $selectedCase->sanction_imposed_at?->format('F d, Y \a\t g:i A') ?? '—' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide">Sanction Effective</p>
                                            <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $selectedCase->sanction_effective_at?->format('F d, Y \a\t g:i A') ?? '—' }}</p>
                                        </div>
                                    </div>
                                    @if($selectedCase->action_taken)
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Action Taken</p>
                                            <div class="bg-white rounded-lg border border-red-100 p-3">
                                                <p class="text-sm text-gray-800 leading-relaxed">{{ $selectedCase->action_taken }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Student's Written Answer --}}
                        @if($selectedCase->student_answer)
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Student's Written Response</h4>
                                <span class="ml-auto text-xs text-gray-400">Submitted {{ $selectedCase->student_answer_submitted_date?->format('M d, Y') }}</span>
                            </div>
                            <div class="bg-teal-50/50 border border-teal-100 rounded-xl p-5">
                                <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $selectedCase->student_answer }}</p>
                            </div>
                        </div>
                        @elseif($selectedCase->status === 'Notice Sent')
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Awaiting Student Response</h4>
                            </div>
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                <p class="text-sm text-blue-800">
                                    @if($selectedCase->isAnswerOverdue())
                                        <span class="font-semibold text-red-600">Answer deadline has passed ({{ $selectedCase->answer_deadline?->format('M d, Y') }}).</span>
                                        The student did not submit a response. The case may proceed ex parte per CSU Section G.9.
                                    @else
                                        Notice sent on {{ $selectedCase->notice_sent_at?->format('M d, Y') }}.
                                        Student has until <strong>{{ $selectedCase->answer_deadline?->format('M d, Y') }}</strong> to submit their written answer.
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Conference / Review --}}
                        @if($selectedCase->conference_date || $selectedCase->conference_notes || $selectedCase->conference_held_at)
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Conference / Review</h4>
                            </div>
                            <div class="bg-purple-50/50 border border-purple-100 rounded-xl p-5 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">Scheduled</p>
                                        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $selectedCase->conference_date?->format('F d, Y \a\t g:i A') ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">Conducted</p>
                                        @if($selectedCase->conference_held_at)
                                            <p class="text-sm font-medium text-green-700 mt-0.5">{{ $selectedCase->conference_held_at->format('F d, Y \a\t g:i A') }}</p>
                                        @else
                                            <p class="text-sm text-gray-500 mt-0.5">Not yet conducted</p>
                                        @endif
                                    </div>
                                </div>
                                @if($selectedCase->conference_notes)
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</p>
                                        <div class="bg-white rounded-lg border border-purple-100 p-3">
                                            <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $selectedCase->conference_notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Incident Narrative --}}
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h12"/></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Incident Narrative</h4>
                            </div>
                            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                                <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $selectedCase->incident_description ?? 'No description provided.' }}</p>
                            </div>
                        </div>

                        {{-- Evidence --}}
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Evidence & Attachments</h4>
                                @if($selectedCase->evidence && $selectedCase->evidence->count() > 0)
                                    <span class="ml-auto text-xs text-gray-400">{{ $selectedCase->evidence->count() }} file(s)</span>
                                @endif
                            </div>

                            @if($selectedCase->evidence && $selectedCase->evidence->count() > 0)
                                {{-- Case Evidence records --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($selectedCase->evidence as $evidence)
                                        <a href="{{ route('admin.evidence.download', $evidence) }}" target="_blank" class="flex items-center gap-3 bg-gray-50 rounded-lg border border-gray-200 p-3 hover:bg-indigo-50 hover:border-indigo-300 transition-colors group">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0
                                                {{ $evidence->file_type === 'image' ? 'bg-green-100' : '' }}
                                                {{ $evidence->file_type === 'document' ? 'bg-blue-100' : '' }}
                                                {{ $evidence->file_type === 'video' ? 'bg-purple-100' : '' }}
                                                {{ $evidence->file_type === 'audio' ? 'bg-amber-100' : '' }}
                                                {{ !in_array($evidence->file_type, ['image', 'document', 'video', 'audio']) ? 'bg-gray-100' : '' }}
                                            ">
                                                @if($evidence->file_type === 'image')
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                @elseif($evidence->file_type === 'video')
                                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-700">{{ $evidence->file_name }}</p>
                                                <p class="text-xs text-gray-400">{{ $evidence->evidence_type }} &bull; {{ number_format($evidence->file_size / 1024, 1) }} KB</p>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    @endforeach
                                </div>

                            @elseif($sourceReport && $sourceReport->evidence_path)
                                {{-- Fallback: evidence from source IncidentReport --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <a href="{{ route('admin.report-evidence.download', $sourceReport) }}" target="_blank" class="flex items-center gap-3 bg-gray-50 rounded-lg border border-gray-200 p-3 hover:bg-indigo-50 hover:border-indigo-300 transition-colors group">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-700">{{ basename($sourceReport->evidence_path) }}</p>
                                            <p class="text-xs text-gray-400">From incident report {{ $sourceReport->tracking_number }}</p>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                                <p class="text-xs text-gray-400 mt-2 italic">Evidence attached to the original incident report filed by staff.</p>

                            @else
                                <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-6 text-center">
                                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <p class="text-sm text-gray-400">No evidence files attached to this case.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Activity Timeline --}}
                        @if($selectedCase->workflowLogs && $selectedCase->workflowLogs->count() > 0)
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Case Activity Timeline</h4>
                            </div>
                            <div class="relative pl-6 border-l-2 border-gray-200 space-y-5">
                                @foreach($selectedCase->workflowLogs->sortByDesc('created_at') as $log)
                                    <div class="relative">
                                        <div class="absolute -left-[1.625rem] top-1 w-3 h-3 rounded-full border-2 border-white
                                            {{ $log->action_type === 'Case Created' ? 'bg-emerald-500' : '' }}
                                            {{ $log->action_type === 'Sanction Applied' ? 'bg-red-500' : '' }}
                                            {{ $log->action_type === 'Hearing Scheduled' ? 'bg-purple-500' : '' }}
                                            {{ $log->action_type === 'Record Accessed' ? 'bg-gray-400' : '' }}
                                            {{ !in_array($log->action_type, ['Case Created', 'Sanction Applied', 'Hearing Scheduled', 'Record Accessed']) ? 'bg-inferno' : '' }}
                                        "></div>
                                        <div class="bg-gray-50 rounded-lg border border-gray-100 p-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">{{ $log->action_type }}</p>
                                                    <p class="text-sm text-gray-600 mt-0.5">{{ $log->action_details }}</p>
                                                </div>
                                                <span class="text-xs text-gray-400 whitespace-nowrap shrink-0">{{ $log->created_at->format('g:i A') }}</span>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-1.5">
                                                <span class="font-medium text-gray-500">{{ $log->actor?->name ?? 'System' }}</span>
                                                &bull; {{ $log->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Action Footer --}}
                    <div class="flex items-center justify-between px-8 py-4 border-t border-gray-200 bg-gray-50/50 rounded-b-2xl">
                        <p class="text-xs text-gray-400">Confidential &mdash; CSU Office of Student Development and Welfare</p>
                        <div class="flex items-center gap-2">
                            @if($selectedCase->canSendNotice())
                                @php
                                    $nextNotice = ($selectedCase->notice_count ?? 0) + 1;
                                    $ordinal = $nextNotice === 1 ? '1st' : ($nextNotice === 2 ? '2nd' : '3rd');
                                @endphp
                                <button wire:click="closeCase" x-on:click.defer="$nextTick(() => $wire.openSendNoticeModal({{ $selectedCase->id }}))" class="px-4 py-2 text-sm font-semibold text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors">
                                    Send {{ $ordinal }} Notice to Student
                                </button>
                            @endif
                            @if($selectedCase->status === 'Notice Sent')
                                @if($selectedCase->student_answer_submitted_date || $selectedCase->isAnswerOverdue())
                                    <button wire:click="closeCase" x-on:click.defer="$nextTick(() => $wire.advanceToInvestigation({{ $selectedCase->id }}))" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                        Move to Investigation
                                    </button>
                                @else
                                    <span class="px-4 py-2 text-sm text-blue-600 bg-blue-50 border border-blue-200 rounded-lg">
                                        Awaiting Student Response
                                    </span>
                                @endif
                            @elseif($selectedCase->status === 'Under Investigation')
                                @if($selectedCase->conference_date && !$selectedCase->conference_held_at)
                                    <button wire:click="closeCase" x-on:click.defer="$nextTick(() => $wire.markConferenceHeld({{ $selectedCase->id }}))" class="px-4 py-2 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                                        Mark Conference Held
                                    </button>
                                @else
                                    <button wire:click="closeCase" x-on:click.defer="$nextTick(() => $wire.openScheduleConferenceModal({{ $selectedCase->id }}))" class="px-4 py-2 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                                        Schedule Conference
                                    </button>
                                @endif
                                @if($selectedCase->investigation_type === 'Tribunal' && !$selectedCase->assigned_to_sdt)
                                    <button wire:click="closeCase" x-on:click.defer="$nextTick(() => $wire.openAssignSDTModal({{ $selectedCase->id }}))" class="px-4 py-2 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                                        Assign Tribunal
                                    </button>
                                @endif
                                <button wire:click="closeCase" x-on:click.defer="$nextTick(() => $wire.openResolveCaseModal({{ $selectedCase->id }}))" class="px-4 py-2 text-sm font-semibold text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors">
                                    Apply Sanction
                                </button>
                            @endif
                            <button wire:click="closeCase" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        {{-- ====================== TAB 2: CASE SUMMARY ====================== --}}
        @if($activeTab === 'case-summary')
        <div class="p-6">
            @include('livewire.admin.partials.report-period-selector')

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mahogany">{{ $caseSummary['period'] ?? '' }}</h3>
                <button wire:click="exportCaseSummary" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export to Excel
                </button>
            </div>

            @if(!($caseSummary['hasRecords'] ?? false))
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-blue-800">No complaint recorded within the period ({{ $caseSummary['period'] ?? '' }})</span>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Case #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Offense</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settled By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sanction Imposed</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sanction Effective</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions Taken</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($caseSummary['records'] as $record)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-mono text-mahogany">{{ $record->case_tracking_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $record->student?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm max-w-[180px] truncate">{{ $record->offenseRule?->title ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $record->status === 'Pending Review' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $record->status === 'Sanction Active' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $record->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $record->status === 'Appealed' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">{{ $record->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($record->settled_by)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $record->settled_by === 'Dean' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">{{ $record->settled_by }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">{{ $record->sanction_imposed_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600">{{ $record->sanction_effective_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600 max-w-[200px] truncate" title="{{ $record->action_taken ?? '' }}">{{ $record->action_taken ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif

        {{-- ====================== TAB 3: MONTHLY REPORT ====================== --}}
        @if($activeTab === 'monthly-report')
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700">Year:</label>
                    <select wire:model.live="reportYear" class="py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button wire:click="exportMonthlyReport" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export to Excel
                </button>
            </div>

            @if(!($monthlyReport['hasRecords'] ?? false))
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-blue-800">No complaint recorded for the year {{ $monthlyReport['year'] ?? '' }}</span>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pending</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Resolved</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">By Dean</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">By OSDW</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($monthlyReport['months'] ?? [] as $month)
                            <tr class="hover:bg-gray-50 {{ $month['total'] === 0 ? 'bg-gray-50/50' : '' }}">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $month['month'] }}</td>
                                <td class="px-4 py-3 text-sm text-center font-semibold {{ $month['total'] > 0 ? 'text-mahogany' : 'text-gray-400' }}">{{ $month['total'] }}</td>
                                @if($month['total'] === 0)
                                    <td colspan="5" class="px-4 py-3 text-xs text-gray-400 text-center italic">No complaint recorded</td>
                                @else
                                    <td class="px-4 py-3 text-sm text-center text-amber-600">{{ $month['pending'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-red-600">{{ $month['active'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-green-600">{{ $month['resolved'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">{{ $month['by_dean'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">{{ $month['by_osdw'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ====================== TAB 4: BY TYPE REPORT ====================== --}}
        @if($activeTab === 'by-type-report')
        <div class="p-6">
            @include('livewire.admin.partials.report-period-selector')

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mahogany">{{ $byTypeReport['period'] ?? '' }}</h3>
                <button wire:click="exportByTypeReport" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export to Excel
                </button>
            </div>

            @if(!($byTypeReport['hasRecords'] ?? false))
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-blue-800">No complaint recorded within the period ({{ $byTypeReport['period'] ?? '' }})</span>
                </div>
            @else
                <!-- Category Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    @foreach($byTypeReport['categories'] ?? [] as $cat)
                        @if($cat['total'] > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h4 class="text-sm font-semibold text-mahogany mb-3">{{ $cat['category'] }}</h4>
                            <p class="text-2xl font-bold text-gray-900">{{ $cat['total'] }} <span class="text-sm font-normal text-gray-500">cases</span></p>
                            <div class="mt-3 space-y-1 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Pending</span><span class="text-amber-600 font-medium">{{ $cat['pending'] }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Active</span><span class="text-red-600 font-medium">{{ $cat['active'] }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Resolved</span><span class="text-green-600 font-medium">{{ $cat['resolved'] }}</span></div>
                                <div class="flex justify-between border-t pt-1 mt-1"><span class="text-gray-500">By Dean</span><span class="font-medium">{{ $cat['by_dean'] }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">By OSDW</span><span class="font-medium">{{ $cat['by_osdw'] }}</span></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                <!-- Summary Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pending</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Resolved</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">By Dean</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">By OSDW</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($byTypeReport['categories'] ?? [] as $cat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $cat['category'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center font-semibold {{ $cat['total'] > 0 ? 'text-mahogany' : 'text-gray-400' }}">{{ $cat['total'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-amber-600">{{ $cat['pending'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-red-600">{{ $cat['active'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-green-600">{{ $cat['resolved'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">{{ $cat['by_dean'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">{{ $cat['by_osdw'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ====================== ACCEPT CASE MODAL ====================== --}}
    @if($showAcceptModal && $acceptCaseId && isset($acceptReport) && $acceptReport)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showAcceptModal', false)"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10">
                <div class="p-6 border-b flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-mahogany">Open Formal Case</h3>
                        <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $acceptReport->tracking_number }}</p>
                    </div>
                    <button wire:click="$set('showAcceptModal', false)" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 pt-4 pb-2 bg-gray-50 border-b space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Student</span>
                        <span class="font-medium text-gray-800">{{ $acceptReport->student?->name ?? 'N/A' }} ({{ $acceptReport->student?->student_id ?? '' }})</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Offense</span>
                        <span class="font-medium text-gray-800 text-right max-w-[60%]">{{ $acceptReport->offense?->code }} — {{ $acceptReport->offense?->title }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Gravity</span>
                        <span class="font-medium text-gray-800 capitalize">{{ $acceptReport->offense?->gravity ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Severity</span>
                        <span class="font-medium text-gray-800">{{ $acceptReport->offense?->severity_level ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Report Type</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $acceptReport->report_type === 'Formal Charge' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">{{ $acceptReport->report_type }}</span>
                    </div>
                    <div class="text-sm pt-1">
                        <span class="text-gray-500">Description:</span>
                        <p class="text-gray-700 mt-1 text-xs line-clamp-3">{{ $acceptReport->description }}</p>
                    </div>
                </div>

                <form wire:submit="acceptReport" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Incident <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="acceptDateOfIncident" max="{{ now()->toDateString() }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                        @error('acceptDateOfIncident') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Investigation Type <span class="text-red-500">*</span></label>
                        <select wire:model="acceptInvestigationType" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                            <option value="Tribunal">Tribunal (Major / Formal Charge)</option>
                            <option value="Summary">Summary (Minor / Quick Log)</option>
                            <option value="Dean Direct">Dean Direct</option>
                        </select>
                        @error('acceptInvestigationType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">Pre-filled based on offense gravity. Change if needed.</p>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        <strong>Note:</strong> Opening this case will create a formal <em>Violation Record</em> with status <strong>Pending Review</strong> and set a 5-day answer deadline for the student.
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" wire:click="$set('showAcceptModal', false)" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors">
                            <span wire:loading.remove wire:target="acceptReport">Open Case</span>
                            <span wire:loading wire:target="acceptReport">Opening...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ====================== RESOLVE CASE / APPLY SANCTION MODAL ====================== --}}
    @if($showResolveCaseModal && $selectedCase)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.classList.add('overflow-hidden')" x-on:keydown.escape.window="$wire.set('showResolveCaseModal', false)">
        <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showResolveCaseModal', false)"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full z-10 my-8 border border-gray-200">

                {{-- Modal Header --}}
                <div class="bg-gradient-to-r from-mahogany to-black-cherry rounded-t-2xl px-6 py-5 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <h3 class="text-base font-bold tracking-wide uppercase">Apply Sanction</h3>
                            </div>
                            <p class="text-xl font-mono font-bold tracking-wider">{{ $sanctionCaseContext['case_number'] ?? $selectedCase->case_tracking_number }}</p>
                        </div>
                        <button wire:click="$set('showResolveCaseModal', false)" class="p-2 text-white/60 hover:text-white rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[75vh] overflow-y-auto">

                    {{-- Case Context Card --}}
                    <div class="px-6 pt-5 pb-4 border-b border-gray-200 bg-gray-50/50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                            <div>
                                <span class="text-xs text-gray-400 uppercase tracking-wide">Respondent</span>
                                <p class="font-semibold text-gray-900">{{ $sanctionCaseContext['student_name'] ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $sanctionCaseContext['student_id'] ?? '' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 uppercase tracking-wide">College</span>
                                <p class="font-medium text-gray-800 text-xs mt-0.5">{{ $sanctionCaseContext['college'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-span-2 pt-1">
                                <span class="text-xs text-gray-400 uppercase tracking-wide">Offense Charged</span>
                                <p class="font-semibold text-gray-900">{{ $sanctionCaseContext['offense_title'] ?? 'N/A' }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if(!empty($sanctionCaseContext['offense_code']))
                                        <span class="text-xs font-mono text-gray-500">{{ $sanctionCaseContext['offense_code'] }}</span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-600">{{ $sanctionCaseContext['offense_category'] ?? '' }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($sanctionCaseContext['gravity'] ?? '') === 'major' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">{{ ucfirst($sanctionCaseContext['gravity'] ?? '') }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 uppercase tracking-wide">Investigation</span>
                                <p class="font-medium text-gray-800 text-xs mt-0.5">{{ $sanctionCaseContext['investigation_type'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 uppercase tracking-wide">Offense Count</span>
                                <p class="font-medium text-gray-800 text-xs mt-0.5">
                                    @php $oc = $sanctionCaseContext['offense_count'] ?? 1; @endphp
                                    {{ $oc }}{{ $oc === 1 ? 'st' : ($oc === 2 ? 'nd' : ($oc === 3 ? 'rd' : 'th')) }} Offense
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Progressive Sanction Reference --}}
                    @if(!empty($sanctionCaseContext['first_sanction']) || !empty($sanctionCaseContext['second_sanction']) || !empty($sanctionCaseContext['third_sanction']))
                    <div class="px-6 py-3 border-b border-gray-200 bg-amber-50/60">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">Progressive Sanction Schedule</span>
                        </div>
                        @php $oc = $sanctionCaseContext['offense_count'] ?? 1; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            @if(!empty($sanctionCaseContext['first_sanction']))
                            <div class="rounded-lg border p-2 text-xs {{ $oc === 1 ? 'bg-inferno/10 border-inferno/30 ring-1 ring-inferno/20' : 'bg-white border-gray-200' }}">
                                <span class="font-semibold {{ $oc === 1 ? 'text-inferno' : 'text-gray-500' }}">1st Offense</span>
                                <p class="text-gray-700 mt-0.5 leading-tight">{{ $sanctionCaseContext['first_sanction'] }}</p>
                            </div>
                            @endif
                            @if(!empty($sanctionCaseContext['second_sanction']))
                            <div class="rounded-lg border p-2 text-xs {{ $oc === 2 ? 'bg-inferno/10 border-inferno/30 ring-1 ring-inferno/20' : 'bg-white border-gray-200' }}">
                                <span class="font-semibold {{ $oc === 2 ? 'text-inferno' : 'text-gray-500' }}">2nd Offense</span>
                                <p class="text-gray-700 mt-0.5 leading-tight">{{ $sanctionCaseContext['second_sanction'] }}</p>
                            </div>
                            @endif
                            @if(!empty($sanctionCaseContext['third_sanction']))
                            <div class="rounded-lg border p-2 text-xs {{ $oc >= 3 ? 'bg-inferno/10 border-inferno/30 ring-1 ring-inferno/20' : 'bg-white border-gray-200' }}">
                                <span class="font-semibold {{ $oc >= 3 ? 'text-inferno' : 'text-gray-500' }}">3rd Offense</span>
                                <p class="text-gray-700 mt-0.5 leading-tight">{{ $sanctionCaseContext['third_sanction'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Sanction Form --}}
                    <form wire:submit="resolveCase" class="px-6 py-5 space-y-4">

                        {{-- Applied Sanction --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Sanction to Apply <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="appliedSanction" placeholder="e.g. Written Warning, 3-day Suspension, Expulsion..."
                                class="w-full py-2.5 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm font-medium">
                            @error('appliedSanction') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-400 mt-1">Pre-filled from the progressive sanction schedule. Modify if needed.</p>
                        </div>

                        {{-- Settled By --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Settled By <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="relative flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors {{ $settledBy === 'Dean' ? 'border-inferno bg-inferno/5 ring-1 ring-inferno/20' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="settledBy" value="Dean" class="text-inferno focus:ring-inferno">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Dean</p>
                                        <p class="text-xs text-gray-500">Dean of the College</p>
                                    </div>
                                </label>
                                <label class="relative flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors {{ $settledBy === 'OSDW' ? 'border-inferno bg-inferno/5 ring-1 ring-inferno/20' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="settledBy" value="OSDW" class="text-inferno focus:ring-inferno">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">OSDW</p>
                                        <p class="text-xs text-gray-500">Office of Student Dev & Welfare</p>
                                    </div>
                                </label>
                            </div>
                            @error('settledBy') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Sanction Imposed Date & Time --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Date Imposed <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="sanctionImposedDate" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                                @error('sanctionImposedDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Time Imposed <span class="text-red-500">*</span></label>
                                <input type="time" wire:model="sanctionImposedTime" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                                @error('sanctionImposedTime') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Sanction Effective Date & Time --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Effective Date <span class="text-xs font-normal text-gray-400">(Optional)</span></label>
                                <input type="date" wire:model="sanctionEffectiveDate" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Effective Time <span class="text-xs font-normal text-gray-400">(Optional)</span></label>
                                <input type="time" wire:model="sanctionEffectiveTime" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                            </div>
                        </div>

                        {{-- Action Taken --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Action Taken / Remarks <span class="text-red-500">*</span></label>
                            <textarea wire:model="actionTaken" rows="3" placeholder="Describe the detailed actions taken, including any conditions, community service requirements, or counseling mandates..." class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm"></textarea>
                            @error('actionTaken') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Warning Note --}}
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <p class="text-xs text-red-800">This action will change the case status to <strong>Sanction Active</strong> and record you as the deciding authority. This action is logged in the case audit trail.</p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <p class="text-xs text-gray-400">All fields are recorded permanently.</p>
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="$set('showResolveCaseModal', false)" class="px-4 py-2.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors shadow-sm">
                                    <span wire:loading.remove wire:target="resolveCase">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span wire:loading.remove wire:target="resolveCase">Apply Sanction</span>
                                    <span wire:loading wire:target="resolveCase">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    </span>
                                    <span wire:loading wire:target="resolveCase">Applying...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ====================== ASSIGN SDT MODAL ====================== --}}
    @if($showAssignSDTModal && $selectedCase)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showAssignSDTModal', false)"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-mahogany">Assign Student Disciplinary Tribunal</h3>
                    <p class="text-sm text-gray-500">Select exactly 5 members (CSU Section G.1)</p>
                </div>
                <form wire:submit="assignSDT" class="p-6">
                    @error('selectedSDTMembers') <p class="text-sm text-red-500 mb-3">{{ $message }}</p> @enderror
                    <div class="max-h-60 overflow-y-auto space-y-2 mb-4">
                        @foreach($sdtMembers as $member)
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" wire:model="selectedSDTMembers" value="{{ $member['id'] }}" class="rounded border-gray-300 text-inferno focus:ring-inferno">
                                <span class="text-sm text-gray-700">{{ $member['name'] }}</span>
                                <span class="text-xs text-gray-400">({{ $member['role'] }})</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Selected: {{ count($selectedSDTMembers) }}/5</p>
                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="$set('showAssignSDTModal', false)" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors" {{ count($selectedSDTMembers) !== 5 ? 'disabled' : '' }}>Assign Tribunal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ====================== SEND NOTICE MODAL ====================== --}}
    @if($showSendNoticeModal && $selectedCase)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showSendNoticeModal', false)"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10">
                <div class="p-6 border-b flex items-start justify-between">
                    <div>
                        @php
                            $nextNotice = ($selectedCase->notice_count ?? 0) + 1;
                            $ordinal = $nextNotice === 1 ? '1st' : ($nextNotice === 2 ? '2nd' : '3rd');
                        @endphp
                        <h3 class="text-lg font-semibold text-mahogany">Send {{ $ordinal }} Formal Notice to Student</h3>
                        <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $selectedCase->case_tracking_number }}</p>
                    </div>
                    <button wire:click="$set('showSendNoticeModal', false)" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 pt-4 pb-2 bg-gray-50 border-b space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Student</span>
                        <span class="font-medium text-gray-800">{{ $selectedCase->student?->name ?? 'N/A' }} ({{ $selectedCase->student?->student_id ?? '' }})</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Email</span>
                        <span class="font-medium text-gray-800">{{ $selectedCase->student?->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Offense</span>
                        <span class="font-medium text-gray-800 text-right max-w-[60%]">{{ $selectedCase->offenseRule?->code }} — {{ $selectedCase->offenseRule?->title }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Date of Incident</span>
                        <span class="font-medium text-gray-800">{{ $selectedCase->date_of_incident?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
                        <strong>What happens next:</strong>
                        <ul class="mt-1 space-y-0.5 list-disc list-inside">
                            <li>A formal charge notice email will be sent to the student</li>
                            <li>The student will have <strong>5 business days</strong> to submit a written response</li>
                            <li>Case status will change to <strong>Notice Sent</strong></li>
                        </ul>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        <strong>Ex Parte Rule:</strong> If the student fails to respond within the deadline, the case will proceed <em>ex parte</em> (without the student's defense) per CSU Student Manual guidelines.
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" wire:click="$set('showSendNoticeModal', false)" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button wire:click="sendNoticeToStudent" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
                            <span wire:loading.remove wire:target="sendNoticeToStudent">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <span wire:loading wire:target="sendNoticeToStudent">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="sendNoticeToStudent">Send {{ $ordinal }} Notice</span>
                            <span wire:loading wire:target="sendNoticeToStudent">Sending...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ====================== SCHEDULE CONFERENCE MODAL ====================== --}}
    @if($showScheduleConferenceModal && $selectedCase)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showScheduleConferenceModal', false)"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10">
                <div class="p-6 border-b flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-mahogany">Schedule Conference / Hearing</h3>
                        <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $selectedCase->case_tracking_number }}</p>
                    </div>
                    <button wire:click="$set('showScheduleConferenceModal', false)" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 pt-4 pb-2 bg-gray-50 border-b space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Student</span>
                        <span class="font-medium text-gray-800">{{ $selectedCase->student?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Offense</span>
                        <span class="font-medium text-gray-800 text-right max-w-[60%]">{{ $selectedCase->offenseRule?->code }} — {{ $selectedCase->offenseRule?->title }}</span>
                    </div>
                </div>

                <form wire:submit="scheduleConference" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conference Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="conferenceDate" min="{{ now()->addDay()->toDateString() }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                            @error('conferenceDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Time <span class="text-red-500">*</span></label>
                            <input type="time" wire:model="conferenceTime" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm">
                            @error('conferenceTime') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Agenda</label>
                        <textarea wire:model="conferenceNotes" rows="3" placeholder="Optional notes about the conference agenda, attendees, or location..." class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno text-sm"></textarea>
                        @error('conferenceNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-600">
                        <strong>Note:</strong> The student and relevant parties will be informed of the scheduled conference. You can mark the conference as held after it takes place.
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" wire:click="$set('showScheduleConferenceModal', false)" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-inferno rounded-lg hover:bg-black-cherry transition-colors inline-flex items-center gap-2">
                            <span wire:loading.remove wire:target="scheduleConference">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="scheduleConference">Schedule Conference</span>
                            <span wire:loading wire:target="scheduleConference">Scheduling...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
