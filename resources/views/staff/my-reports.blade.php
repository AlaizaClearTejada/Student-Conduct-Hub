<x-staff-app-layout>
    <x-slot name="header">
        My Submitted Reports
    </x-slot>

    <div class="p-8 max-w-7xl mx-auto space-y-8">

        {{-- Incident Reports Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-[#f3f3f3] flex justify-between items-center">
                <h3 class="text-lg font-bold text-[#250001]">All Incident Reports</h3>

            </div>

            @if($reports->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-white">
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date Filed</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tracking No.</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Offense</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($reports as $report)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-8 py-4 text-sm text-gray-900 font-medium">
                                        {{ $report->created_at->format('M d, Y') }}
                                        <span class="block text-xs text-gray-500">{{ $report->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600 font-mono">
                                        {{ $report->tracking_number }}
                                    </td>
                                    <td class="px-8 py-4 text-sm">
                                        <p class="font-medium text-gray-900">{{ $report->student->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ $report->student->student_id ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600">
                                        {{ $report->offense->title ?? 'N/A' }}
                                    </td>
                                    <td class="px-8 py-4 text-sm">
                                        @if($report->report_type === 'Quick Log')
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">Quick Log</span>
                                        @else
                                            <span class="px-2 py-1 bg-[#a50104] text-white rounded text-xs font-medium">Formal</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4">
                                        @if($report->status === 'Submitted')
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Submitted</span>
                                        @elseif($report->status === 'Under Review by OSDW')
                                            <span class="px-3 py-1 bg-[#590004] text-[#f3f3f3] rounded-full text-xs font-semibold">Under Review</span>
                                        @elseif($report->status === 'Resolved')
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Resolved</span>
                                        @elseif($report->status === 'Dismissed')
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Dismissed</span>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">{{ $report->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4">
                                        <a href="{{ route('staff.report.show', $report) }}" class="text-[#590004] hover:text-[#a50104] font-medium text-sm transition-colors">
                                            View Details →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-4 border-t border-gray-100 bg-white">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="px-8 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">No incident reports submitted yet</p>
                    <p class="text-sm text-gray-400 mt-1 mb-4">Submit your first report to get started</p>

                </div>
            @endif
        </div>

        {{-- Case Dispositions Section - Shows sanction details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-[#f3f3f3]">
                <h3 class="text-lg font-bold text-[#250001]">Case Dispositions & Sanctions</h3>
                <p class="text-sm text-gray-500 mt-1">Cases filed from your reports with sanction details</p>
            </div>

            @if($cases->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-white">
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Case No.</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Offense</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Settled By</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sanction Imposed</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Effective Date</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action Taken</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($cases as $case)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono font-semibold">
                                        {{ $case->case_tracking_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <p class="font-medium text-gray-900">{{ $case->student->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ $case->student->student_id ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $case->offenseRule->title ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($case->status === 'Sanction Active')
                                            <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">Sanction Active</span>
                                        @elseif($case->status === 'Resolved')
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Resolved</span>
                                        @elseif($case->status === 'Pending Review')
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Pending Review</span>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">{{ $case->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($case->settled_by)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $case->settled_by === 'Dean' ? 'bg-purple-100 text-purple-800' : 'bg-[#590004]/10 text-[#590004]' }}">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                {{ $case->settled_by === 'Dean' ? 'Dean' : 'OSDW' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($case->sanction_imposed_at)
                                            <span class="text-gray-900 font-medium">{{ $case->sanction_imposed_at->format('M d, Y') }}</span>
                                            <span class="block text-xs text-gray-500">{{ $case->sanction_imposed_at->format('h:i A') }}</span>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($case->sanction_effective_at)
                                            <span class="text-gray-900 font-medium">{{ $case->sanction_effective_at->format('M d, Y') }}</span>
                                            <span class="block text-xs text-gray-500">{{ $case->sanction_effective_at->format('h:i A') }}</span>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                        @if($case->action_taken)
                                            <p class="truncate" title="{{ $case->action_taken }}">{{ $case->action_taken }}</p>
                                        @else
                                            <span class="text-gray-400 text-xs">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-4 border-t border-gray-100 bg-white">
                    {{ $cases->links() }}
                </div>
            @else
                <div class="px-8 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No case dispositions yet</p>
                    <p class="text-sm text-gray-400 mt-1">Cases filed from your reports will appear here with sanction details</p>
                </div>
            @endif
        </div>

    </div>
</x-staff-app-layout>
