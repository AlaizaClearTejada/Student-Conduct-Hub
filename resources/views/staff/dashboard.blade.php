<x-staff-app-layout>
    <x-slot name="header">
        Incident Reporting Hub
    </x-slot>

    <div x-data class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6 sm:space-y-8">
        
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- ── Unified incident reporting card ─────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="px-8 py-5 border-b border-gray-100 bg-[#f3f3f3] flex items-center gap-3">
                <svg class="w-5 h-5 text-[#590004]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h3 class="text-base font-bold text-[#250001]">File an Incident Report</h3>
                    <p class="text-xs text-gray-500">Choose the appropriate report type based on the severity of the offense.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">

                {{-- Quick Log ──────────────────────────────────────── --}}
                <button
                    @click="Livewire.dispatch('open-quick-log-modal')"
                    class="group w-full text-left p-8 hover:bg-gray-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#590004]"
                >
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 group-hover:bg-[#590004] text-gray-500 group-hover:text-white flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-base font-bold text-[#250001]">Log Minor Infraction</h4>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-500 uppercase tracking-wide">Minor</span>
                            </div>
                            <p class="text-sm text-gray-500 leading-relaxed">Quick entry for uniform violations, missing ID, or tardiness. No evidence upload required.</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-[#590004] flex-shrink-0 mt-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>

                {{-- Formal Charge ───────────────────────────────────── --}}
                <a
                    href="{{ route('staff.formal-charge') }}"
                    class="group w-full text-left p-8 hover:bg-red-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#a50104]"
                >
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 group-hover:bg-[#a50104] text-[#a50104] group-hover:text-white flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-base font-bold text-[#a50104]">File Formal Charge</h4>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-[#a50104] uppercase tracking-wide">Major</span>
                            </div>
                            <p class="text-sm text-gray-500 leading-relaxed">Submit a detailed report for major offenses (e.g., academic dishonesty, vandalism) with mandatory evidence upload.</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-[#a50104] flex-shrink-0 mt-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>

            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Reports</p>
                        <p class="text-3xl font-bold text-[#250001]">{{ $totalReports }}</p>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Submitted</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $submittedCount }}</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Under Review</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $underReviewCount }}</p>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Resolved</p>
                        <p class="text-3xl font-bold text-green-600">{{ $resolvedCount }}</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-5 border-b border-gray-100 bg-[#f3f3f3] flex flex-wrap justify-between items-center gap-3">
                <h3 class="text-lg font-bold text-[#250001]">Recently Submitted Reports</h3>
                @if($recentReports->count() > 0)
                    <a href="{{ route('staff.my-reports') }}" class="text-sm text-[#590004] hover:text-[#a50104] font-semibold transition-colors">
                        View All Reports →
                    </a>
                @endif
            </div>
            
            @if($recentReports->count() > 0)
                <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-white">
                            <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date Filed</th>
                            <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tracking No.</th>
                            <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student ID</th>
                            <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Offense</th>
                            <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($recentReports as $report)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-8 py-4 text-sm text-gray-900 font-medium">
                                    {{ $report->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-8 py-4 text-sm text-gray-600 font-mono">
                                    <a href="{{ route('staff.report.show', $report) }}" class="hover:text-[#590004] transition-colors">
                                        {{ $report->tracking_number }}
                                    </a>
                                </td>
                                <td class="px-8 py-4 text-sm text-gray-600 font-mono">
                                    {{ $report->student->student_id ?? 'N/A' }}
                                </td>
                                <td class="px-8 py-4 text-sm text-gray-600">
                                    {{ $report->offense->title ?? 'N/A' }}
                                </td>
                                <td class="px-8 py-4">
                                    @if($report->status === 'Submitted')
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Submitted</span>
                                    @elseif($report->status === 'Under Review by OSDW')
                                        <span class="px-3 py-1 bg-[#590004] text-[#f3f3f3] rounded-full text-xs font-semibold">Under OSDW Review</span>
                                    @elseif($report->status === 'Resolved')
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Resolved</span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">{{ $report->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="px-8 py-4 border-t border-gray-100 bg-white text-sm text-gray-500 italic">
                    Note: To maintain student privacy, full resolution details are only accessible to the OSDW and the Student Disciplinary Tribunal.
                </div>
            @else
                <div class="px-8 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">No incident reports submitted yet</p>
                    <p class="text-sm text-gray-400 mt-1">Use the buttons above to submit your first report</p>
                </div>
            @endif
        </div>
        
    </div>

    <!-- Quick Log Livewire Component -->
    @livewire('staff.quick-log')

    <script>
        // Listen for successful log submission
        document.addEventListener('livewire:init', () => {
            Livewire.on('incident-logged', (event) => {
                console.log('Incident logged:', event.trackingNumber);
            });

            Livewire.on('refresh-dashboard', () => {
                window.location.reload();
            });
        });
    </script>
</x-staff-app-layout>
