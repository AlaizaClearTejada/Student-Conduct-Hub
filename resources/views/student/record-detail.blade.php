<x-student-app-layout>
    <x-slot name="header">
        Violation Record Details
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6">

        <a href="{{ route('student.dashboard') }}"
           class="inline-flex items-center text-sm font-medium text-[#590004] hover:text-[#a50104] transition-colors mb-4">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Dashboard
        </a>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                @foreach($errors->all() as $error)
                    <p class="text-red-800 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Status Banner --}}
        @php
            $statusConfig = [
                'Pending Review' => [
                    'bg' => 'bg-yellow-50',
                    'border' => 'border-yellow-500',
                    'text' => 'text-yellow-800',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                ],
                'Notice Sent' => [
                    'bg' => 'bg-blue-50',
                    'border' => 'border-blue-500',
                    'text' => 'text-blue-800',
                    'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
                ],
                'Under Investigation' => [
                    'bg' => 'bg-indigo-50',
                    'border' => 'border-indigo-500',
                    'text' => 'text-indigo-800',
                    'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'
                ],
                'Sanction Active' => [
                    'bg' => 'bg-red-50',
                    'border' => 'border-[#a50104]',
                    'text' => 'text-[#a50104]',
                    'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'
                ],
                'Resolved' => [
                    'bg' => 'bg-green-50',
                    'border' => 'border-green-500',
                    'text' => 'text-green-800',
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                ],
                'Appealed' => [
                    'bg' => 'bg-purple-50',
                    'border' => 'border-purple-500',
                    'text' => 'text-purple-800',
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                ]
            ];
            $config = $statusConfig[$record->status] ?? $statusConfig['Pending Review'];
        @endphp

        <div class="rounded-2xl overflow-hidden {{ $config['bg'] }} border-2 {{ $config['border'] }} shadow-sm">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="bg-white p-4 rounded-xl shadow-sm">
                            <svg class="w-10 h-10 {{ $config['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="{{ $config['text'] }} font-bold text-2xl">{{ $record->status }}</h3>
                            <p class="text-gray-600 text-sm mt-1">Case No. {{ $record->case_tracking_number ?? '#'.$record->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Dates --}}
        @if($record->charge_filed_date || $record->answer_deadline)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                <div class="px-5 py-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Charge Filed</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $record->charge_filed_date?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Answer Deadline</p>
                    <p class="text-sm font-semibold mt-1 {{ $record->isAnswerOverdue() ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $record->answer_deadline?->format('M d, Y') ?? '—' }}
                        @if($record->isAnswerOverdue())
                            <span class="text-xs text-red-500 font-normal">(Overdue)</span>
                        @endif
                    </p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Notice Sent</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $record->notice_sent_at?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Date of Incident</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $record->date_of_incident?->format('M d, Y') ?? '—' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Offense Information --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-[#250001] font-bold text-lg">Offense Information</h3>
            </div>
            <div class="p-4 sm:p-8 space-y-5">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Offense Code</label>
                    <p class="text-[#250001] font-bold text-lg mt-1">{{ $record->offenseRule->code }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Offense Title</label>
                    <p class="text-[#250001] font-semibold text-xl mt-1">{{ $record->offenseRule->title }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Description</label>
                    <p class="text-gray-700 mt-1 leading-relaxed">{{ $record->offenseRule->description }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Category</label>
                        <p class="text-[#250001] font-medium mt-1">{{ $record->offenseRule->category }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Severity Level</label>
                        @php
                            $severityColors = [
                                'Minor' => 'bg-yellow-100 text-yellow-800',
                                'Moderate' => 'bg-orange-100 text-orange-800',
                                'Major' => 'bg-red-100 text-red-800',
                                'Severe' => 'bg-[#a50104] text-white',
                            ];
                        @endphp
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-bold rounded-full {{ $severityColors[$record->offenseRule->severity_level] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $record->offenseRule->severity_level }}
                        </span>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Standard Sanction</label>
                        <p class="text-[#250001] font-medium mt-1">{{ $record->offenseRule->standard_sanction }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Incident Details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-[#250001] font-bold text-lg">Incident Details</h3>
            </div>
            <div class="p-8 space-y-5">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Date of Incident</label>
                    <p class="text-[#250001] font-semibold text-lg mt-1">
                        {{ $record->date_of_incident->format('F d, Y') }}
                    </p>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Incident Description</label>
                    <div class="mt-2 p-4 bg-[#f3f3f3] rounded-xl border border-gray-200">
                        <p class="text-gray-800 leading-relaxed">{{ $record->incident_description }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Reported By</label>
                    <p class="text-gray-700 font-medium mt-1">{{ $record->reporter->name }}</p>
                </div>

                @if($record->resolution_date)
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Resolution Date</label>
                        <p class="text-gray-700 font-medium mt-1">
                            {{ $record->resolution_date->format('F d, Y') }}
                        </p>
                    </div>
                @endif

                @if($record->resolution_notes)
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Resolution Notes</label>
                        <div class="mt-2 p-4 bg-green-50 rounded-xl border border-green-200">
                            <p class="text-gray-800 leading-relaxed">{{ $record->resolution_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Student Answer Section --}}
        @if($record->canSubmitAnswer())
            {{-- Answer Submission Form --}}
            <div class="bg-white rounded-2xl shadow-sm border-2 border-blue-300 overflow-hidden">
                <div class="px-8 py-5 border-b border-blue-100 bg-blue-50">
                    <h3 class="text-[#250001] font-bold text-lg">Submit Your Written Response</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Deadline: <strong>{{ $record->answer_deadline->format('F d, Y') }}</strong>
                        @if(now()->lt($record->answer_deadline))
                            <span class="text-blue-600">({{ ceil(now()->diffInWeekdays($record->answer_deadline)) }} class days remaining)</span>
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('student.records.submit-answer', $record) }}" class="p-4 sm:p-8">
                    @csrf
                    <p class="text-sm text-gray-600 mb-4">
                        You may submit a written answer or defense in response to the charge filed against you. This is your opportunity to present your side of the case.
                    </p>
                    <textarea name="student_answer" rows="8" required minlength="20" maxlength="5000"
                        placeholder="Write your response/defense here. Be thorough and factual in your account of events..."
                        class="w-full border border-gray-300 rounded-xl p-4 focus:ring-2 focus:ring-[#590004] focus:border-[#590004] text-sm"
                    >{{ old('student_answer') }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">Minimum 20 characters. Maximum 5,000 characters.</p>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 mt-4">
                        <strong>Note:</strong> Once submitted, your response cannot be edited. Please review your answer carefully before submitting.
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="px-6 py-3 bg-[#590004] text-white font-semibold rounded-xl hover:bg-[#a50104] transition-colors"
                            onclick="return confirm('Are you sure you want to submit this response? This action cannot be undone.')">
                            Submit Response
                        </button>
                    </div>
                </form>
            </div>
        @elseif($record->student_answer)
            {{-- Show Submitted Answer (read-only) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-[#250001] font-bold text-lg">Your Written Response</h3>
                    </div>
                </div>
                <div class="p-8">
                    <p class="text-xs text-gray-500 mb-3">
                        Submitted on <strong>{{ $record->student_answer_submitted_date->format('F d, Y') }}</strong>
                    </p>
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <p class="text-gray-800 leading-relaxed whitespace-pre-line">{{ $record->student_answer }}</p>
                    </div>
                </div>
            </div>
        @elseif($record->status === 'Notice Sent' && $record->isAnswerOverdue())
            {{-- Deadline Passed --}}
            <div class="bg-red-50 border-l-4 border-red-400 rounded-r-lg shadow-sm p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Response Deadline Passed</p>
                        <p class="text-xs text-red-700 mt-1">
                            The deadline for submitting your written response was {{ $record->answer_deadline->format('F d, Y') }}.
                            The case may proceed under ex parte proceedings per CSU Student Conduct Code Section G.9.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Sanction Details --}}
        @if($record->action_taken || $record->sanction_imposed_at)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-red-50">
                <h3 class="text-[#250001] font-bold text-lg">Sanction Details</h3>
            </div>
                <div class="p-4 sm:p-8 space-y-4">
                @if($record->applied_sanction)
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Applied Sanction</label>
                    <p class="text-[#250001] font-semibold mt-1">{{ $record->applied_sanction }}</p>
                </div>
                @endif
                @if($record->action_taken)
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Action Taken</label>
                    <div class="mt-2 p-4 bg-red-50 rounded-xl border border-red-200">
                        <p class="text-gray-800 leading-relaxed">{{ $record->action_taken }}</p>
                    </div>
                </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($record->sanction_imposed_at)
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sanction Imposed</label>
                        <p class="text-gray-700 font-medium mt-1">{{ $record->sanction_imposed_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                    @endif
                    @if($record->sanction_effective_at)
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sanction Effective</label>
                        <p class="text-gray-700 font-medium mt-1">{{ $record->sanction_effective_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-[#250001] font-bold text-lg">Case Timeline</h3>
            </div>
            <div class="p-8">
                <div class="relative pl-6 border-l-2 border-gray-200 space-y-5">
                    {{-- Case Created --}}
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full bg-[#250001] border-2 border-white"></div>
                        <p class="text-sm font-semibold text-[#250001]">Case Created</p>
                        <p class="text-xs text-gray-500">{{ $record->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>

                    {{-- Notice Sent --}}
                    @if($record->notice_sent_at)
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full bg-blue-500 border-2 border-white"></div>
                        <p class="text-sm font-semibold text-blue-700">Formal Notice Sent</p>
                        <p class="text-xs text-gray-500">{{ $record->notice_sent_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    @endif

                    {{-- Student Answer --}}
                    @if($record->student_answer_submitted_date)
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full bg-teal-500 border-2 border-white"></div>
                        <p class="text-sm font-semibold text-teal-700">Written Response Submitted</p>
                        <p class="text-xs text-gray-500">{{ $record->student_answer_submitted_date->format('M d, Y') }}</p>
                    </div>
                    @endif

                    {{-- Conference --}}
                    @if($record->conference_held_at)
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full bg-purple-500 border-2 border-white"></div>
                        <p class="text-sm font-semibold text-purple-700">Conference Conducted</p>
                        <p class="text-xs text-gray-500">{{ $record->conference_held_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    @elseif($record->conference_date)
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full bg-purple-300 border-2 border-white"></div>
                        <p class="text-sm font-semibold text-purple-600">Conference Scheduled</p>
                        <p class="text-xs text-gray-500">{{ $record->conference_date->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    @endif

                    {{-- Sanction Applied --}}
                    @if($record->sanction_imposed_at)
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full bg-red-500 border-2 border-white"></div>
                        <p class="text-sm font-semibold text-red-700">Sanction Applied</p>
                        <p class="text-xs text-gray-500">{{ $record->sanction_imposed_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    @endif

                    {{-- Current Status --}}
                    <div class="relative">
                        <div class="absolute -left-[1.125rem] top-1 w-4 h-4 rounded-full {{ $record->isResolved() ? 'bg-green-500' : 'bg-gray-400' }} border-2 border-white"></div>
                        <p class="text-sm font-semibold {{ $record->isResolved() ? 'text-green-700' : 'text-gray-600' }}">
                            Current: {{ $record->status }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $record->updated_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Read-Only Notice (only when student cannot take action) --}}
        @if(!$record->canSubmitAnswer())
        <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg shadow-sm p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-yellow-800">Confidential Record</p>
                    <p class="text-xs text-yellow-700 mt-1">
                        This is a confidential view of your institutional conduct record. For questions or to submit an appeal, please contact the Office of Student Development and Welfare (OSDW).
                    </p>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-student-app-layout>
