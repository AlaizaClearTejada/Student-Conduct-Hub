<x-student-app-layout>
    <x-slot name="header">
        My Conduct Records
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Records</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $violationRecords->count() }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-wrap gap-3">
                <span class="text-sm font-semibold text-gray-700 flex items-center">Filter by Status:</span>
                <button onclick="filterRecords('all')" 
                        class="status-filter-btn px-4 py-2 rounded-lg text-sm font-semibold transition-all active" 
                        data-status="all">
                    All Records
                </button>
                <button onclick="filterRecords('Sanction Active')" 
                        class="status-filter-btn px-4 py-2 rounded-lg text-sm font-semibold transition-all" 
                        data-status="Sanction Active">
                    Active Sanctions
                </button>
                <button onclick="filterRecords('Pending Review')" 
                        class="status-filter-btn px-4 py-2 rounded-lg text-sm font-semibold transition-all" 
                        data-status="Pending Review">
                    Pending Review
                </button>
                <button onclick="filterRecords('Appealed')" 
                        class="status-filter-btn px-4 py-2 rounded-lg text-sm font-semibold transition-all" 
                        data-status="Appealed">
                    Appealed
                </button>
                <button onclick="filterRecords('Resolved')" 
                        class="status-filter-btn px-4 py-2 rounded-lg text-sm font-semibold transition-all" 
                        data-status="Resolved">
                    Resolved
                </button>
            </div>
        </div>

        <!-- Records List -->
        @if($violationRecords->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h4 class="text-2xl font-bold text-[#250001] mb-2">Perfect Record!</h4>
                <p class="text-gray-600 mb-6">You have no violations on record. Keep up the excellent conduct!</p>
                <a href="{{ route('student.offense-rules') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-[#590004] text-white rounded-xl font-semibold hover:bg-[#a50104] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Browse University Policy
                </a>
            </div>
        @else
            <div class="space-y-4" id="records-list">
                @foreach($violationRecords as $record)
                    <div class="record-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all" 
                         data-status="{{ $record->status }}">
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-6">
                                <div class="flex-1">
                                    <!-- Header -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-lg text-xs font-bold">
                                            {{ $record->offenseRule->code }}
                                        </span>
                                        @php
                                            $statusStyles = [
                                                'Pending Review' => 'bg-yellow-100 text-yellow-800',
                                                'Sanction Active' => 'bg-[#590004] text-[#f3f3f3]',
                                                'Resolved' => 'bg-gray-100 text-gray-700',
                                                'Appealed' => 'bg-blue-100 text-blue-800',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 {{ $statusStyles[$record->status] }} rounded-lg text-xs font-bold">
                                            {{ $record->status }}
                                        </span>
                                        <span class="px-3 py-1 {{ 
                                            $record->offenseRule->severity_level === 'Minor' ? 'bg-green-100 text-green-800' : 
                                            ($record->offenseRule->severity_level === 'Moderate' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($record->offenseRule->severity_level === 'Major' ? 'bg-orange-100 text-orange-800' : 
                                            'bg-red-100 text-red-800')) 
                                        }} rounded-lg text-xs font-semibold">
                                            {{ $record->offenseRule->severity_level }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-xl font-bold text-[#250001] mb-2">
                                        {{ $record->offenseRule->title }}
                                    </h3>

                                    <!-- Meta Information -->
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <strong>Incident Date:</strong> {{ $record->date_of_incident->format('F d, Y') }}
                                        </span>
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <strong>Reported:</strong> {{ $record->created_at->format('M d, Y') }}
                                        </span>
                                        @if($record->sanction_start_date)
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <strong>Sanction Period:</strong> 
                                                {{ $record->sanction_start_date->format('M d') }} - {{ $record->sanction_end_date->format('M d, Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    @if($record->description)
                                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                            <p class="text-sm text-gray-700 leading-relaxed">
                                                <strong class="text-gray-900">Incident Details:</strong> {{ $record->description }}
                                            </p>
                                        </div>
                                    @endif

                                    <!-- Sanction Applied -->
                                    @if($record->sanction_applied)
                                        <div class="bg-[#f3f3f3] border-l-4 border-[#590004] rounded-lg p-4">
                                            <p class="text-sm text-gray-900">
                                                <strong class="text-[#590004]">Sanction Applied:</strong> {{ $record->sanction_applied }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Button -->
                                <div class="flex-shrink-0">
                                    <a href="{{ route('student.records.show', $record) }}" 
                                       class="inline-flex items-center gap-2 px-5 py-3 bg-[#590004] text-white rounded-xl font-semibold hover:bg-[#a50104] transition-colors">
                                        <span>View Details</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterRecords(status) {
            const cards = document.querySelectorAll('.record-card');
            
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Update button states
            document.querySelectorAll('.status-filter-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-[#590004]', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            });
            
            const activeBtn = document.querySelector(`[data-status="${status}"]`);
            activeBtn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            activeBtn.classList.add('active', 'bg-[#590004]', 'text-white');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            filterRecords('all');
        });
    </script>

    <style>
        .status-filter-btn.active {
            @apply bg-[#590004] text-white;
        }
        .status-filter-btn:not(.active) {
            @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
        }
    </style>
</x-student-app-layout>
