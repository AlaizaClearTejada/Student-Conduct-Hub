<x-student-app-layout>
    <x-slot name="header">
        Overview
    </x-slot>

    <div class="p-8 max-w-7xl mx-auto space-y-8">
        
        @if($activeSanctionsCount > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold tracking-wider text-gray-500 uppercase mb-1">Current Standing</h2>
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-[#a50104] animate-pulse"></div>
                        <span class="text-3xl font-bold text-[#250001]">Action Required</span>
                    </div>
                    <p class="text-gray-600 mt-2">
                        You have {{ $activeSanctionsCount }} {{ Str::plural('active sanction', $activeSanctionsCount) }} requiring your attention.
                    </p>
                </div>
                <a href="#violations-table" class="px-6 py-3 bg-[#a50104] text-white rounded-xl font-bold hover:bg-[#590004] transition-colors shadow-lg">
                    View Details
                </a>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold tracking-wider text-gray-500 uppercase mb-1">Current Standing</h2>
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-3xl font-bold text-[#250001]">Good Standing</span>
                    </div>
                    <p class="text-gray-600 mt-2">You have no active violations or pending sanctions.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Active Sanctions</p>
                        <p class="text-3xl font-bold {{ $activeSanctionsCount > 0 ? 'text-[#a50104]' : 'text-gray-400' }}">
                            {{ $activeSanctionsCount }}
                        </p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-[#a50104]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pending Review</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $pendingReviewCount }}</p>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Appealed</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $appealedCount }}</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div id="violations-table" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-[#250001]">Recent Disciplinary Records</h3>
                @if($violationRecords->count() > 5)
                    <a href="#" class="text-sm font-medium text-[#590004] hover:text-[#a50104] transition-colors">
                        View Full History &rarr;
                    </a>
                @endif
            </div>

            @if($violationRecords->isEmpty())
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="text-xl font-bold text-[#250001] mb-2">No Violations on Record</h4>
                    <p class="text-gray-600">You have maintained excellent conduct standing.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Offense Level</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($violationRecords->take(10) as $record)
                                <tr class="hover:bg-[#f3f3f3] transition-colors">
                                    <td class="px-8 py-4 text-sm text-gray-900 font-medium">
                                        {{ $record->date_of_incident->format('M d, Y') }}
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600">
                                        {{ $record->offenseRule->severity_level }}
                                    </td>
                                    <td class="px-8 py-4 text-sm">
                                        <div class="font-medium text-[#250001]">{{ $record->offenseRule->title }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $record->offenseRule->code }}</div>
                                    </td>
                                    <td class="px-8 py-4">
                                        @php
                                            $statusStyles = [
                                                'Notice Sent' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                'Pending Review' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                'Pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                'Sanction Active' => 'bg-red-100 text-red-800 border-red-300',
                                                'Appealed' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                'Resolved' => 'bg-green-100 text-green-800 border-green-300',
                                                'Dismissed' => 'bg-slate-100 text-slate-700 border-slate-300',
                                                'Warning' => 'bg-orange-100 text-orange-800 border-orange-300',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 {{ $statusStyles[$record->status] ?? 'bg-gray-100 text-gray-800 border-gray-300' }} rounded-full text-xs font-semibold">
                                            {{ $record->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <a href="{{ route('student.records.show', $record) }}" 
                                           class="text-sm font-medium text-[#590004] hover:text-[#a50104] transition-colors">
                                            View Details &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white border-l-4 border-[#250001] rounded-r-lg shadow-sm p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-[#250001] mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-[#250001]">Data Privacy Notice</p>
                    <p class="text-xs text-gray-600 mt-1">
                        This dashboard displays only your personal conduct records. All data is protected by institutional privacy protocols and cannot be modified by students.
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-student-app-layout>
