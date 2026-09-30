<x-staff-app-layout>
    <x-slot name="header">
        Incident Report Details
    </x-slot>

    <div class="p-8 max-w-5xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between">
            <a href="{{ route('staff.my-reports') }}" class="flex items-center text-[#590004] hover:text-[#a50104] font-medium transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to My Reports
            </a>
            
            @if($report->status === 'Submitted')
                <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm font-semibold">Submitted</span>
            @elseif($report->status === 'Under Review by OSDW')
                <span class="px-4 py-2 bg-[#590004] text-[#f3f3f3] rounded-lg text-sm font-semibold">Under OSDW Review</span>
            @elseif($report->status === 'Resolved')
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">Resolved</span>
            @elseif($report->status === 'Dismissed')
                <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold">Dismissed</span>
            @else
                <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold">{{ $report->status }}</span>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 bg-[#f3f3f3]">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#250001] mb-1">{{ $report->tracking_number }}</h2>
                        <p class="text-sm text-gray-600">Filed on {{ $report->created_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                    @if($report->report_type === 'Quick Log')
                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">Quick Log</span>
                    @else
                        <span class="px-4 py-2 bg-[#a50104] text-white rounded-lg font-medium">Formal Charge</span>
                    @endif
                </div>
            </div>

            <div class="px-8 py-6 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Student Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-bold text-gray-900 text-lg">{{ $report->student->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 font-mono mt-1">Student ID: {{ $report->student->student_id ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $report->student->email ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Offense Details</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-bold text-gray-900">{{ $report->offense->title ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                Code: {{ $report->offense->code ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-[#a50104] font-medium mt-2">
                                Gravity: {{ ucfirst($report->offense->gravity ?? 'N/A') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Incident Description</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900 whitespace-pre-line">{{ $report->description }}</p>
                    </div>
                </div>

                @if($report->evidence_path)
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Evidence Attached</h3>
                        <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">Evidence File</p>
                                    <p class="text-sm text-gray-500 font-mono">{{ basename($report->evidence_path) }}</p>
                                </div>
                            </div>
                            <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors" disabled>
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View (Admin Only)
                            </button>
                        </div>
                    </div>
                @endif

                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Reported By</h3>
                    <div class="bg-gray-50 rounded-lg p-4 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-[#a50104] text-white flex items-center justify-center font-bold mr-4 shadow-md">
                            {{ strtoupper(substr($report->reporter->name ?? 'N/A', 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $report->reporter->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ $report->reporter->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                @if($report->recommended_violation)
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-[#a50104]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Decision Support
                        </h3>
                        <div class="bg-[#fdf2f2] rounded-lg p-6 border border-[#f3dada]">
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">Detected Violation:</p>
                                <p class="text-lg font-bold text-[#250001]">{{ $report->recommended_violation }}</p>
                                <p class="text-sm font-semibold text-[#a50104] mt-1">Severity: {{ strtoupper($report->severity) }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">Matched Terms:</p>
                                <div class="flex flex-wrap gap-2">
                                    @if(is_array($report->matched_keywords) || is_object($report->matched_keywords))
                                        @foreach($report->matched_keywords as $keyword)
                                            <span class="px-2 py-1 bg-white border border-[#f3dada] text-[#a50104] rounded text-xs font-semibold">
                                                {{ $keyword }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-sm text-gray-500 italic">None logged</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">AI/System Recommendation:</p>
                                <p class="text-md font-bold text-[#590004] bg-white px-3 py-2 border border-[#f3dada] inline-block rounded">
                                    {{ $report->recommended_sanction }}
                                </p>
                            </div>

                            <div class="flex items-start gap-2 mt-4 pt-4 border-t border-[#f3dada]">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-xs text-yellow-800 font-semibold mt-0.5">
                                    Recommendation only. Final decision must be made by the Tribunal.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="px-8 py-4 border-t border-gray-100 bg-yellow-50 flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-bold text-yellow-900 mb-1">Privacy Notice</p>
                    <p class="text-sm text-yellow-800">
                        You can track the status of this report. However, full resolution details, hearing transcripts, 
                        and the student's complete disciplinary history remain confidential per CSU Student Manual Section G.20. 
                        Only authorized OSDW personnel and the Student Disciplinary Tribunal have access to complete case files.
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-staff-app-layout>
