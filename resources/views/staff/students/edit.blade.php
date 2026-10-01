<x-staff-app-layout>
    <x-slot name="header">Edit Student Record</x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto space-y-6">
        {{-- Breadcrumb Navigation --}}
        <nav class="flex items-center text-sm font-medium text-gray-500">
            <a href="{{ route('staff.dashboard') }}" class="hover:text-[#590004] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('staff.students') }}" class="hover:text-[#590004] transition-colors">Student Roster</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-semibold">Edit Student</span>
        </nav>

        @livewire('staff.student-form', ['mode' => 'edit', 'studentId' => $studentId])
    </div>
</x-staff-app-layout>
