<div x-data class="space-y-6">

    {{-- Flash success message --}}
    @if (session()->has('roster-success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">{{ session('roster-success') }}</span>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
            <div class="relative w-full sm:w-80">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by ID, name, college, or section…"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#a50104] focus:border-transparent outline-none bg-white shadow-sm"
                />
            </div>

            <div class="flex items-center bg-gray-100 p-1 rounded-xl w-full sm:w-auto">
                <button 
                    wire:click="$set('showArchived', false)" 
                    class="flex-1 sm:flex-none px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors {{ !$showArchived ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    Active
                </button>
                <button 
                    wire:click="$set('showArchived', true)" 
                    class="flex-1 sm:flex-none px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors {{ $showArchived ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    Archived
                </button>
            </div>
        </div>

        <button
            wire:click="openCreateForm"
            class="flex items-center gap-2 bg-[#590004] hover:bg-[#a50104] text-white font-semibold px-5 py-2.5 rounded-xl shadow-md transition-colors whitespace-nowrap"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Student
        </button>

        <button
            wire:click="openImportModal"
            class="flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md transition-colors whitespace-nowrap"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import Excel File
        </button>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($students->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-base font-medium text-gray-500">
                    @if ($search)
                        No students found matching &ldquo;{{ $search }}&rdquo;
                    @else
                        No students registered yet.
                    @endif
                </p>
                @if (! $search)
                    <button wire:click="openCreateForm" class="mt-4 text-sm font-semibold text-[#590004] hover:text-[#a50104] transition-colors">
                        + Add First Student
                    </button>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student ID</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">College</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Year</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Section</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($students as $student)
                            <tr wire:key="student-row-{{ $student->id }}" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm font-semibold text-[#250001] tracking-wider">{{ $student->student_id ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#590004] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($student->first_name ?? $student->name, 0, 1)) }}{{ strtoupper(substr($student->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $student->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-600 leading-relaxed">{{ $student->college ?? 'Not Assigned' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $student->year_level ?? 'Not Assigned' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ $student->section ?? 'Not Assigned' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($showArchived)
                                            <button
                                                wire:confirm="Are you sure you want to restore this student?"
                                                wire:click="restoreStudent({{ $student->id }})"
                                                title="Restore student"
                                                class="p-1.5 rounded-lg text-green-600 hover:text-green-800 hover:bg-green-50 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                            </button>
                                        @else
                                            <button
                                                wire:click="viewStudent({{ $student->id }})"
                                                title="View profile"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-[#590004] hover:bg-red-50 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <button
                                                wire:click="openEditForm({{ $student->id }})"
                                                title="Edit record"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-[#a50104] hover:bg-red-50 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button
                                                wire:confirm="Are you sure you want to archive this student?"
                                                wire:click="archiveStudent({{ $student->id }})"
                                                title="Archive student"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-700 hover:bg-red-50 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $students->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Import Excel File Modal --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4" wire:click.self="closeImportModal">
            <div class="max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto bg-white rounded-2xl shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[#250001]">Import Students from Excel</h3>
                    <button wire:click="closeImportModal" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold mb-1">Expected Excel/CSV columns:</p>
                                <p class="font-mono text-xs">Code, Name, Course, Year, Section, Email</p>
                                <p class="mt-2 text-xs text-blue-700 font-medium">All columns except <span class="font-mono">Email</span> are required. Email is auto-generated if blank. Duplicate student IDs (Code) are skipped.</p>
                            </div>
                            <a href="{{ route('staff.students.import-template') }}"
                               class="flex-shrink-0 flex items-center gap-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap"
                               title="Download a blank CSV template">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Template
                            </a>
                        </div>
                    </div>

                    @if($previewValidRows !== null)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-gray-100 border-b border-gray-200 flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-700">Preview (First 5 records)</span>
                                <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded-full">{{ count($previewValidRows) }} Valid Records</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Student ID</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Name</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Course</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Email</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @forelse(array_slice($previewValidRows, 0, 5) as $row)
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-xs">{{ $row['student_id'] }}</td>
                                                <td class="px-4 py-2 text-xs">{{ $row['name'] }}</td>
                                                <td class="px-4 py-2 text-xs">{{ $row['program'] }}</td>
                                                <td class="px-4 py-2 text-xs">{{ $row['email'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-4 py-4 text-center text-gray-500 text-xs">No valid records found in file.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="text-right">
                            <button wire:click="$set('previewValidRows', null)" class="text-xs text-[#590004] hover:underline">Upload a different file</button>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload File</label>
                            <input type="file" wire:model.live="importFile" accept=".xlsx,.xls,.csv" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#590004] file:text-white hover:file:bg-[#a50104]">
                            <div wire:loading wire:target="importFile" class="text-sm text-blue-600 mt-2 flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Reading file, please wait...
                            </div>
                            @error('importFile') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Import result --}}
                    @if($importResult)
                        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                            {{ $importResult }}
                        </div>
                    @endif

                    @if($importError)
                        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                            {{ $importError }}
                        </div>
                    @endif

                    @if(count($importErrors) > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
                            <p class="text-sm font-semibold text-yellow-800 mb-1">Skipped Rows ({{ $previewSkippedCount ?? count($importErrors) }} total):</p>
                            <ul class="text-xs text-yellow-700 space-y-0.5">
                                @foreach(array_slice($importErrors, 0, 10) as $err)
                                    <li>Row {{ $err['row'] }}: {{ $err['student_id'] }} — {{ $err['reason'] }}</li>
                                @endforeach
                                @if(count($importErrors) > 10)
                                    <li class="font-medium">...and {{ count($importErrors) - 10 }} more</li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button wire:click="closeImportModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    @if($previewValidRows !== null && count($previewValidRows) > 0)
                        <button
                            wire:click="confirmImport"
                            wire:loading.attr="disabled"
                            wire:target="confirmImport"
                            class="px-5 py-2 text-sm font-semibold text-white bg-green-700 hover:bg-green-800 rounded-lg shadow transition-colors disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="confirmImport">Confirm Import</span>
                            <span wire:loading wire:target="confirmImport">Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
